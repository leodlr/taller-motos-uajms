<?php
declare(strict_types=1);

/**
 * Órdenes de trabajo. Las reglas de negocio críticas (correlativo, stock, cierre)
 * se aplican aquí, dentro de transacciones, y lanzan DomainException si se violan.
 */
final class Orden
{
    public const ESTADOS = ['Recibida', 'En diagnóstico', 'En reparación', 'Lista para entrega', 'Entregada'];

    private const BASE = 'SELECT o.*, m.placa, m.marca, m.modelo, c.nombre_completo AS cliente, c.ci,
                                 u.nombre AS mecanico
                            FROM ordenes_trabajo o
                            JOIN motos m ON m.id_moto = o.id_moto
                            JOIN clientes c ON c.id_cliente = m.id_cliente
                            JOIN usuarios u ON u.id_usuario = o.id_mecanico';

    public static function all(string $estado = '', ?int $idMecanico = null, string $q = ''): array
    {
        $where = [];
        $p = [];
        if ($estado !== '') {
            $where[] = 'o.estado = :estado';
            $p[':estado'] = $estado;
        }
        if ($idMecanico !== null) {
            $where[] = 'o.id_mecanico = :mec';
            $p[':mec'] = $idMecanico;
        }
        if ($q !== '') {
            $where[] = '(o.codigo_correlativo LIKE :q1 OR m.placa LIKE :q2 OR c.nombre_completo LIKE :q3)';
            $p[':q1'] = $p[':q2'] = $p[':q3'] = '%' . $q . '%';
        }
        $sql = self::BASE . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY o.fecha_ingreso DESC, o.id_orden DESC';
        $st = getConnection()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getConnection()->prepare(self::BASE . ' WHERE o.id_orden = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function detalle(int $id): array
    {
        $st = getConnection()->prepare(
            'SELECT d.*, r.codigo, r.nombre FROM detalle_repuestos d
               JOIN repuestos r ON r.id_repuesto = d.id_repuesto
              WHERE d.id_orden = :id ORDER BY d.id_detalle'
        );
        $st->execute([':id' => $id]);
        return $st->fetchAll();
    }

    /** Crea la orden con correlativo OT-YYYY-XXXX; reintenta ante colisión/deadlock. */
    public static function create(array $d): string
    {
        $pdo = getConnection();
        for ($i = 1; ; $i++) {
            try {
                $pdo->beginTransaction();
                $codigo = self::nextCodigo($pdo);
                $pdo->prepare(
                    "INSERT INTO ordenes_trabajo (codigo_correlativo, id_moto, id_mecanico, falla_reportada,
                            costo_mano_obra, total, estado)
                     VALUES (:cod, :moto, :mec, :falla, :costo, :total, 'Recibida')"
                )->execute([
                    ':cod' => $codigo, ':moto' => $d['id_moto'], ':mec' => $d['id_mecanico'],
                    ':falla' => $d['falla_reportada'], ':costo' => $d['costo_mano_obra'], ':total' => $d['costo_mano_obra'],
                ]);
                $pdo->commit();
                return $codigo;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if (!in_array((string) $e->getCode(), ['23000', '40001'], true) || $i >= 3) {
                    throw $e;
                }
            }
        }
    }

    private static function nextCodigo(PDO $pdo): string
    {
        $year = date('Y');
        $st = $pdo->prepare(
            'SELECT codigo_correlativo FROM ordenes_trabajo WHERE codigo_correlativo LIKE :p
              ORDER BY codigo_correlativo DESC LIMIT 1 FOR UPDATE'
        );
        $st->execute([':p' => "OT-$year-%"]);
        $last = $st->fetchColumn();
        $n = $last ? (int) substr((string) $last, -4) + 1 : 1;
        return sprintf('OT-%s-%04d', $year, $n);
    }

    /** Actualiza datos de cabecera y recalcula el total. */
    public static function updateHeader(int $id, array $d): void
    {
        self::transaction(function (PDO $pdo) use ($id, $d): void {
            $pdo->prepare(
                'UPDATE ordenes_trabajo SET id_moto = :moto, id_mecanico = :mec, falla_reportada = :falla,
                        costo_mano_obra = :costo WHERE id_orden = :id'
            )->execute([':moto' => $d['id_moto'], ':mec' => $d['id_mecanico'], ':falla' => $d['falla_reportada'],
                        ':costo' => $d['costo_mano_obra'], ':id' => $id]);
            self::recalcularTotal($pdo, $id);
        });
    }

    /** Cambia estado/diagnóstico. Regla: no se puede pasar a Entregada sin diagnóstico. */
    public static function updateEstado(int $id, string $estado, string $diagnostico): void
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new DomainException('Estado inválido.');
        }
        $diagnostico = trim($diagnostico);
        if ($estado === 'Entregada' && $diagnostico === '') {
            throw new DomainException('No se puede marcar como Entregada sin un diagnóstico técnico registrado.');
        }
        self::transaction(function (PDO $pdo) use ($id, $estado, $diagnostico): void {
            self::lockOrden($pdo, $id);
            if ($estado === 'Entregada') {
                // RN-05: además del diagnóstico, debe haber mano de obra > 0 o al menos un repuesto.
                $st = $pdo->prepare(
                    'SELECT o.costo_mano_obra, (SELECT COUNT(*) FROM detalle_repuestos d WHERE d.id_orden = o.id_orden) AS n
                       FROM ordenes_trabajo o WHERE o.id_orden = :id'
                );
                $st->execute([':id' => $id]);
                $r = $st->fetch();
                if ((float) $r['costo_mano_obra'] <= 0 && (int) $r['n'] === 0) {
                    throw new DomainException('No se puede entregar: registre un monto de mano de obra o al menos un repuesto.');
                }
            }
            $pdo->prepare(
                'UPDATE ordenes_trabajo SET estado = :e, diagnostico = :d,
                        fecha_entrega = CASE WHEN :e2 = \'Entregada\' THEN COALESCE(fecha_entrega, NOW()) ELSE NULL END
                  WHERE id_orden = :id'
            )->execute([':e' => $estado, ':d' => $diagnostico === '' ? null : $diagnostico, ':e2' => $estado, ':id' => $id]);
        });
    }

    /**
     * Agrega un repuesto a la orden: valida stock, descuenta stock_actual,
     * guarda precio_aplicado histórico y recalcula el total. Todo en una transacción.
     */
    public static function addRepuesto(int $idOrden, int $idRepuesto, int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new DomainException('La cantidad debe ser mayor a 0.');
        }
        self::transaction(function (PDO $pdo) use ($idOrden, $idRepuesto, $cantidad): void {
            $orden = self::lockOrden($pdo, $idOrden);
            if ($orden['estado'] === 'Entregada') {
                throw new DomainException('No se pueden agregar repuestos a una orden ya entregada.');
            }
            $st = $pdo->prepare(
                'SELECT nombre, precio_unitario, stock_actual FROM repuestos WHERE id_repuesto = :id FOR UPDATE'
            );
            $st->execute([':id' => $idRepuesto]);
            $rep = $st->fetch();
            if (!$rep) {
                throw new DomainException('El repuesto no existe.');
            }
            if ($cantidad > (int) $rep['stock_actual']) {
                throw new DomainException(sprintf(
                    'Stock insuficiente de "%s": disponible %d, solicitado %d.',
                    $rep['nombre'], $rep['stock_actual'], $cantidad
                ));
            }
            $pdo->prepare('UPDATE repuestos SET stock_actual = stock_actual - :c WHERE id_repuesto = :id')
                ->execute([':c' => $cantidad, ':id' => $idRepuesto]);
            $pdo->prepare(
                'INSERT INTO detalle_repuestos (id_orden, id_repuesto, cantidad, precio_aplicado)
                 VALUES (:o, :r, :c, :p)'
            )->execute([':o' => $idOrden, ':r' => $idRepuesto, ':c' => $cantidad, ':p' => $rep['precio_unitario']]);
            self::recalcularTotal($pdo, $idOrden);
        });
    }

    /** Solo se eliminan órdenes recién recibidas y sin repuestos (evita perder stock descontado). */
    public static function delete(int $id): void
    {
        self::transaction(function (PDO $pdo) use ($id): void {
            $orden = self::lockOrden($pdo, $id);
            $st = $pdo->prepare('SELECT COUNT(*) FROM detalle_repuestos WHERE id_orden = :id');
            $st->execute([':id' => $id]);
            if ($orden['estado'] !== 'Recibida' || (int) $st->fetchColumn() > 0) {
                throw new DomainException('Solo se pueden eliminar órdenes en estado Recibida y sin repuestos.');
            }
            $pdo->prepare('DELETE FROM ordenes_trabajo WHERE id_orden = :id')->execute([':id' => $id]);
        });
    }

    private static function lockOrden(PDO $pdo, int $id): array
    {
        $st = $pdo->prepare('SELECT id_orden, estado FROM ordenes_trabajo WHERE id_orden = :id FOR UPDATE');
        $st->execute([':id' => $id]);
        $o = $st->fetch();
        if (!$o) {
            throw new DomainException('La orden no existe.');
        }
        return $o;
    }

    private static function recalcularTotal(PDO $pdo, int $id): void
    {
        $pdo->prepare(
            'UPDATE ordenes_trabajo SET total = costo_mano_obra + COALESCE(
                 (SELECT SUM(cantidad * precio_aplicado) FROM detalle_repuestos WHERE id_orden = :a), 0)
              WHERE id_orden = :b'
        )->execute([':a' => $id, ':b' => $id]);
    }

    /** beginTransaction / commit / rollBack alrededor de $fn. */
    private static function transaction(callable $fn): void
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $fn($pdo);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

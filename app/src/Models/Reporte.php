<?php
declare(strict_types=1);

final class Reporte
{
    /** Historial de órdenes con filtros opcionales (fechas, placa, cliente). */
    public static function historial(?string $desde, ?string $hasta, string $placa, string $cliente): array
    {
        $where = [];
        $p = [];
        if ($desde !== null) {
            $where[] = 'o.fecha_ingreso >= :desde';
            $p[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== null) {
            $where[] = 'o.fecha_ingreso < :hasta';
            $p[':hasta'] = date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00';
        }
        if ($placa !== '') {
            $where[] = 'm.placa LIKE :placa';
            $p[':placa'] = '%' . $placa . '%';
        }
        if ($cliente !== '') {
            $where[] = '(c.nombre_completo LIKE :c1 OR c.ci LIKE :c2)';
            $p[':c1'] = $p[':c2'] = '%' . $cliente . '%';
        }
        $sql = 'SELECT o.codigo_correlativo, o.fecha_ingreso, o.fecha_entrega, o.estado, o.costo_mano_obra, o.total,
                       m.placa, m.marca, m.modelo, c.nombre_completo AS cliente, c.ci, u.nombre AS mecanico
                  FROM ordenes_trabajo o
                  JOIN motos m ON m.id_moto = o.id_moto
                  JOIN clientes c ON c.id_cliente = m.id_cliente
                  JOIN usuarios u ON u.id_usuario = o.id_mecanico'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY o.fecha_ingreso DESC, o.id_orden DESC';
        $st = getConnection()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    /** Repuestos con stock_actual <= stock_minimo. */
    public static function stockCritico(): array
    {
        return getConnection()->query(
            'SELECT codigo, nombre, stock_actual, stock_minimo, (stock_minimo - stock_actual) AS faltante
               FROM repuestos WHERE stock_actual <= stock_minimo
              ORDER BY (stock_actual - stock_minimo) ASC, nombre'
        )->fetchAll();
    }
}

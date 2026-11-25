<?php
declare(strict_types=1);

final class Repuesto
{
    public static function all(string $q = ''): array
    {
        $st = getConnection()->prepare(
            'SELECT * FROM repuestos WHERE codigo LIKE :a OR nombre LIKE :b ORDER BY nombre'
        );
        $like = '%' . $q . '%';
        $st->execute([':a' => $like, ':b' => $like]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getConnection()->prepare('SELECT * FROM repuestos WHERE id_repuesto = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function codigoExists(string $codigo, int $excludeId = 0): bool
    {
        $st = getConnection()->prepare('SELECT 1 FROM repuestos WHERE codigo = :c AND id_repuesto <> :id');
        $st->execute([':c' => $codigo, ':id' => $excludeId]);
        return (bool) $st->fetchColumn();
    }

    private static function params(array $d): array
    {
        return [
            ':c' => $d['codigo'], ':n' => $d['nombre'], ':p' => $d['precio_unitario'],
            ':sa' => $d['stock_actual'], ':sm' => $d['stock_minimo'],
        ];
    }

    public static function create(array $d): void
    {
        getConnection()->prepare(
            'INSERT INTO repuestos (codigo, nombre, precio_unitario, stock_actual, stock_minimo)
             VALUES (:c, :n, :p, :sa, :sm)'
        )->execute(self::params($d));
    }

    public static function update(int $id, array $d): void
    {
        getConnection()->prepare(
            'UPDATE repuestos SET codigo = :c, nombre = :n, precio_unitario = :p,
                    stock_actual = :sa, stock_minimo = :sm WHERE id_repuesto = :id'
        )->execute(self::params($d) + [':id' => $id]);
    }

    public static function delete(int $id): void
    {
        getConnection()->prepare('DELETE FROM repuestos WHERE id_repuesto = :id')->execute([':id' => $id]);
    }
}

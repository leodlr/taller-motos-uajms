<?php
declare(strict_types=1);

final class Cliente
{
    public static function all(string $q = ''): array
    {
        $st = getConnection()->prepare(
            'SELECT * FROM clientes WHERE ci LIKE :a OR nombre_completo LIKE :b ORDER BY nombre_completo'
        );
        $like = '%' . $q . '%';
        $st->execute([':a' => $like, ':b' => $like]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getConnection()->prepare('SELECT * FROM clientes WHERE id_cliente = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function ciExists(string $ci, int $excludeId = 0): bool
    {
        $st = getConnection()->prepare('SELECT 1 FROM clientes WHERE ci = :ci AND id_cliente <> :id');
        $st->execute([':ci' => $ci, ':id' => $excludeId]);
        return (bool) $st->fetchColumn();
    }

    public static function create(array $d): void
    {
        getConnection()->prepare(
            'INSERT INTO clientes (ci, nombre_completo, telefono) VALUES (:ci, :n, :t)'
        )->execute([':ci' => $d['ci'], ':n' => $d['nombre_completo'], ':t' => $d['telefono']]);
    }

    public static function update(int $id, array $d): void
    {
        getConnection()->prepare(
            'UPDATE clientes SET ci = :ci, nombre_completo = :n, telefono = :t WHERE id_cliente = :id'
        )->execute([':ci' => $d['ci'], ':n' => $d['nombre_completo'], ':t' => $d['telefono'], ':id' => $id]);
    }

    public static function delete(int $id): void
    {
        getConnection()->prepare('DELETE FROM clientes WHERE id_cliente = :id')->execute([':id' => $id]);
    }
}

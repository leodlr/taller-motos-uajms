<?php
declare(strict_types=1);

final class Moto
{
    public static function all(string $q = '', int $idCliente = 0): array
    {
        $st = getConnection()->prepare(
            'SELECT m.*, c.nombre_completo, c.ci
               FROM motos m JOIN clientes c ON c.id_cliente = m.id_cliente
              WHERE (m.placa LIKE :a OR m.marca LIKE :b OR m.modelo LIKE :c OR c.nombre_completo LIKE :d)
                AND (:cl = 0 OR m.id_cliente = :cl2)
              ORDER BY m.placa'
        );
        $like = '%' . $q . '%';
        $st->execute([':a' => $like, ':b' => $like, ':c' => $like, ':d' => $like, ':cl' => $idCliente, ':cl2' => $idCliente]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getConnection()->prepare('SELECT * FROM motos WHERE id_moto = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function placaExists(string $placa, int $excludeId = 0): bool
    {
        $st = getConnection()->prepare('SELECT 1 FROM motos WHERE placa = :p AND id_moto <> :id');
        $st->execute([':p' => $placa, ':id' => $excludeId]);
        return (bool) $st->fetchColumn();
    }

    private static function params(array $d): array
    {
        return [
            ':c' => $d['id_cliente'], ':p' => $d['placa'], ':ma' => $d['marca'],
            ':mo' => $d['modelo'], ':a' => $d['anio'], ':co' => $d['color'],
        ];
    }

    public static function create(array $d): void
    {
        getConnection()->prepare(
            'INSERT INTO motos (id_cliente, placa, marca, modelo, anio, color)
             VALUES (:c, :p, :ma, :mo, :a, :co)'
        )->execute(self::params($d));
    }

    public static function update(int $id, array $d): void
    {
        getConnection()->prepare(
            'UPDATE motos SET id_cliente = :c, placa = :p, marca = :ma, modelo = :mo, anio = :a, color = :co
              WHERE id_moto = :id'
        )->execute(self::params($d) + [':id' => $id]);
    }

    public static function delete(int $id): void
    {
        getConnection()->prepare('DELETE FROM motos WHERE id_moto = :id')->execute([':id' => $id]);
    }
}

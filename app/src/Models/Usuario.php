<?php
declare(strict_types=1);

final class Usuario
{
    public static function all(string $q = ''): array
    {
        $st = getConnection()->prepare(
            'SELECT id_usuario, nombre, username, rol, activo FROM usuarios
              WHERE nombre LIKE :a OR username LIKE :b ORDER BY activo DESC, nombre'
        );
        $st->execute([':a' => "%$q%", ':b' => "%$q%"]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getConnection()->prepare(
            'SELECT id_usuario, nombre, username, rol, activo FROM usuarios WHERE id_usuario = :id'
        );
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function usernameExists(string $username, int $excludeId = 0): bool
    {
        $st = getConnection()->prepare('SELECT 1 FROM usuarios WHERE username = :u AND id_usuario <> :id');
        $st->execute([':u' => $username, ':id' => $excludeId]);
        return (bool) $st->fetchColumn();
    }

    public static function create(array $d): void
    {
        getConnection()->prepare(
            'INSERT INTO usuarios (nombre, username, password_hash, rol, activo) VALUES (:n, :u, :p, :r, :a)'
        )->execute([':n' => $d['nombre'], ':u' => $d['username'], ':p' => password_hash($d['password'], PASSWORD_BCRYPT),
                    ':r' => $d['rol'], ':a' => $d['activo']]);
    }

    /** Si $d['password'] está vacío se conserva la contraseña actual. */
    public static function update(int $id, array $d): void
    {
        $sql = 'UPDATE usuarios SET nombre = :n, username = :u, rol = :r, activo = :a'
             . ($d['password'] !== '' ? ', password_hash = :p' : '') . ' WHERE id_usuario = :id';
        $p = [':n' => $d['nombre'], ':u' => $d['username'], ':r' => $d['rol'], ':a' => $d['activo'], ':id' => $id];
        if ($d['password'] !== '') {
            $p[':p'] = password_hash($d['password'], PASSWORD_BCRYPT);
        }
        getConnection()->prepare($sql)->execute($p);
    }

    public static function setActivo(int $id, int $activo): void
    {
        getConnection()->prepare('UPDATE usuarios SET activo = :a WHERE id_usuario = :id')
            ->execute([':a' => $activo, ':id' => $id]);
    }

    public static function mecanicos(): array
    {
        return getConnection()->query(
            "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'MECANICO' AND activo = 1 ORDER BY nombre"
        )->fetchAll();
    }

    public static function mecanicoExists(int $id): bool
    {
        $st = getConnection()->prepare(
            "SELECT 1 FROM usuarios WHERE id_usuario = :id AND rol = 'MECANICO' AND activo = 1"
        );
        $st->execute([':id' => $id]);
        return (bool) $st->fetchColumn();
    }
}

<?php
declare(strict_types=1);

/** Sesiones seguras, login/logout, CSRF y control de roles. */
final class Auth
{
    private const TIMEOUT = 1800; // 30 min de inactividad

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_name('TALLERSESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();

        if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > self::TIMEOUT) {
            self::logout();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }

    /** Valida credenciales con password_verify(). */
    public static function attempt(string $username, string $password): bool
    {
        $stmt = getConnection()->prepare(
            'SELECT id_usuario, nombre, username, password_hash, rol
               FROM usuarios WHERE username = :u AND activo = 1 LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        // Verificación con hash ficticio si no existe el usuario (mitiga timing attacks)
        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaleeIHxpYpNkq5vGzR2m5s7nqE2Hkq3W';
        if (!password_verify($password, $hash) || !$user) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int) $user['id_usuario'],
            'nombre'   => $user['nombre'],
            'username' => $user['username'],
            'rol'      => $user['rol'],
        ];
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $c = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** Middleware: exige sesión y, opcionalmente, uno de los roles dados. */
    public static function requireRole(string ...$roles): void
    {
        $user = self::user();
        if ($user === null) {
            header('Location: /index.php?r=login');
            exit;
        }
        // Una cuenta desactivada pierde el acceso aunque tenga la sesión abierta.
        $st = getConnection()->prepare('SELECT 1 FROM usuarios WHERE id_usuario = :id AND activo = 1');
        $st->execute([':id' => $user['id']]);
        if (!$st->fetchColumn()) {
            self::logout();
            header('Location: /index.php?r=login');
            exit;
        }
        if ($roles && !in_array($user['rol'], $roles, true)) {
            http_response_code(403);
            exit('403 - Acceso denegado: no tiene permisos para esta sección.');
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}

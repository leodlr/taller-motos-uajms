<?php
declare(strict_types=1);

abstract class Controller
{
    /** Renderiza una vista dentro del layout base. */
    protected function render(string $view, array $data = []): void
    {
        $user  = Auth::user();
        $csrf  = Auth::csrfToken();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $route = (string) ($_GET['r'] ?? '');
        extract($data, EXTR_SKIP);
        $base = __DIR__ . '/../Views/';
        require $base . 'layouts/header.php';
        require $base . $view . '.php';
        require $base . 'layouts/footer.php';
    }

    protected function redirect(string $route): never
    {
        header('Location: /index.php?r=' . $route);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
    }

    /** Exige POST con token CSRF válido. */
    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            exit('Solicitud inválida.');
        }
    }

    protected function notFound(): never
    {
        http_response_code(404);
        exit('404 - Registro no encontrado.');
    }

    protected function post(string $key): string
    {
        return trim((string) ($_POST[$key] ?? ''));
    }
}

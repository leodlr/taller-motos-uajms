<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            header('Location: /index.php?r=dashboard');
            exit;
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
                $error = 'Solicitud inválida. Recargue la página.';
            } elseif ($username === '' || $password === '') {
                $error = 'Ingrese usuario y contraseña.';
            } elseif (Auth::attempt($username, $password)) {
                header('Location: /index.php?r=dashboard');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        }
        $csrf = Auth::csrfToken();
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(405);
            exit('Método no permitido.');
        }
        Auth::logout();
        header('Location: /index.php?r=login');
        exit;
    }

    public function dashboard(): void
    {
        Auth::requireRole('ADMIN', 'MECANICO');
        $this->render('dashboard', ['title' => 'Panel']);
    }
}

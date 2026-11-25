<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Core/helpers.php';

spl_autoload_register(function (string $class): void {
    foreach (['Core', 'Controllers', 'Models'] as $dir) {
        $file = __DIR__ . "/../src/$dir/$class.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

Auth::startSession();

/** Tabla de rutas: 'ruta' => [Controlador, método]. El control de roles lo aplica cada método. */
$routes = [
    'login'            => [AuthController::class, 'login'],
    'logout'           => [AuthController::class, 'logout'],
    'dashboard'        => [AuthController::class, 'dashboard'],
    'clientes'         => [ClienteController::class, 'index'],
    'clientes.form'    => [ClienteController::class, 'form'],
    'clientes.save'    => [ClienteController::class, 'save'],
    'clientes.delete'  => [ClienteController::class, 'delete'],
    'motos'            => [MotoController::class, 'index'],
    'motos.form'       => [MotoController::class, 'form'],
    'motos.save'       => [MotoController::class, 'save'],
    'motos.delete'     => [MotoController::class, 'delete'],
    'repuestos'        => [RepuestoController::class, 'index'],
    'repuestos.form'   => [RepuestoController::class, 'form'],
    'repuestos.save'   => [RepuestoController::class, 'save'],
    'repuestos.delete' => [RepuestoController::class, 'delete'],
    'ordenes'          => [OrdenController::class, 'index'],
    'ordenes.form'     => [OrdenController::class, 'form'],
    'ordenes.save'     => [OrdenController::class, 'save'],
    'ordenes.ver'      => [OrdenController::class, 'ver'],
    'ordenes.update'   => [OrdenController::class, 'update'],
    'ordenes.addrep'   => [OrdenController::class, 'addrep'],
    'ordenes.delete'   => [OrdenController::class, 'delete'],
    'usuarios'         => [UsuarioController::class, 'index'],
    'usuarios.form'    => [UsuarioController::class, 'form'],
    'usuarios.save'    => [UsuarioController::class, 'save'],
    'usuarios.toggle'  => [UsuarioController::class, 'toggle'],
    'reportes.historial' => [ReporteController::class, 'historial'],
    'reportes.stock'   => [ReporteController::class, 'stock'],
];

$route = (string) ($_GET['r'] ?? (Auth::check() ? 'dashboard' : 'login'));

if (!isset($routes[$route])) {
    http_response_code(404);
    exit('404 - Página no encontrada.');
}
[$class, $method] = $routes[$route];
(new $class())->$method();

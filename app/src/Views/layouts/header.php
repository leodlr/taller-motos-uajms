<?php
/** Navbar dinámico según rol. */
$menu = [];
if ($user && $user['rol'] === 'ADMIN') {
    $menu = ['ordenes' => 'Órdenes', 'clientes' => 'Clientes', 'motos' => 'Motos', 'repuestos' => 'Repuestos', 'usuarios' => 'Usuarios'];
} elseif ($user && $user['rol'] === 'MECANICO') {
    $menu = ['ordenes' => 'Mis órdenes', 'clientes' => 'Clientes', 'motos' => 'Motos', 'repuestos' => 'Consultar stock'];
}
$reportes = ($user && $user['rol'] === 'ADMIN')
    ? ['reportes.historial' => 'Historial e ingresos', 'reportes.stock' => 'Stock crítico'] : [];
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Panel') ?> - Taller de Motos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php?r=dashboard">Taller de Motos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
            aria-controls="nav" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($menu as $key => $label): ?>
          <li class="nav-item">
            <a class="nav-link <?= str_starts_with($route, $key) ? 'active' : '' ?>"
               href="/index.php?r=<?= e($key) ?>"><?= e($label) ?></a>
          </li>
        <?php endforeach; ?>
        <?php if ($reportes): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= str_starts_with($route, 'reportes') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
            <ul class="dropdown-menu">
              <?php foreach ($reportes as $key => $label): ?>
                <li><a class="dropdown-item" href="/index.php?r=<?= e($key) ?>"><?= e($label) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
      <form method="post" action="/index.php?r=logout" class="d-flex align-items-center gap-3 m-0">
        <span class="text-light small"><?= e($user['nombre']) ?> <span class="badge bg-secondary"><?= e($user['rol']) ?></span></span>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
      </form>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php endif; ?>

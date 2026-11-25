<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Usuarios</h1>
  <a href="/index.php?r=usuarios.form" class="btn btn-primary">Nuevo usuario</a>
</div>
<form class="row g-2 mb-3" method="get" action="/index.php">
  <input type="hidden" name="r" value="usuarios">
  <div class="col-sm-6 col-md-4"><input type="search" name="q" class="form-control" placeholder="Buscar por nombre o username" value="<?= e($q) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover bg-white align-middle">
  <thead class="table-dark"><tr><th>Nombre</th><th>Username</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): $activo = (int) $it['activo'] === 1; $propio = (int) $it['id_usuario'] === $user['id']; ?>
    <tr>
      <td><?= e($it['nombre']) ?><?= $propio ? ' <small class="text-muted">(usted)</small>' : '' ?></td>
      <td><?= e($it['username']) ?></td>
      <td><span class="badge bg-<?= $it['rol'] === 'ADMIN' ? 'dark' : 'info text-dark' ?>"><?= e($it['rol']) ?></span></td>
      <td><span class="badge bg-<?= $activo ? 'success' : 'secondary' ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="/index.php?r=usuarios.form&amp;id=<?= (int) $it['id_usuario'] ?>">Editar</a>
        <?php if (!$propio): ?>
        <form method="post" action="/index.php?r=usuarios.toggle" class="d-inline" data-confirm="¿<?= $activo ? 'Desactivar' : 'Activar' ?> este usuario?">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $it['id_usuario'] ?>">
          <button class="btn btn-sm btn-outline-<?= $activo ? 'danger' : 'success' ?>"><?= $activo ? 'Desactivar' : 'Activar' ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="5" class="text-center text-muted">Sin resultados.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

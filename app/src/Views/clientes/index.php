<?php $isAdmin = $user['rol'] === 'ADMIN'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Clientes</h1>
  <?php if ($isAdmin): ?><a href="/index.php?r=clientes.form" class="btn btn-primary">Nuevo cliente</a><?php endif; ?>
</div>
<form class="row g-2 mb-3" method="get" action="/index.php">
  <input type="hidden" name="r" value="clientes">
  <div class="col-sm-6 col-md-4"><input type="search" name="q" class="form-control" placeholder="Buscar por CI o nombre" value="<?= e($q) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover bg-white align-middle">
  <thead class="table-dark"><tr><th>CI</th><th>Nombre completo</th><th>Teléfono</th><th>Registro</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><?= e($it['ci']) ?></td>
      <td><?= e($it['nombre_completo']) ?></td>
      <td><?= e($it['telefono']) ?></td>
      <td><?= e($it['fecha_registro']) ?></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-secondary" href="/index.php?r=motos&amp;id_cliente=<?= (int) $it['id_cliente'] ?>">Motos</a>
        <?php if ($isAdmin): ?>
        <a class="btn btn-sm btn-outline-primary" href="/index.php?r=clientes.form&amp;id=<?= (int) $it['id_cliente'] ?>">Editar</a>
        <form method="post" action="/index.php?r=clientes.delete" class="d-inline" data-confirm="¿Eliminar este cliente?">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $it['id_cliente'] ?>">
          <button class="btn btn-sm btn-outline-danger">Eliminar</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="5" class="text-center text-muted">Sin resultados.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

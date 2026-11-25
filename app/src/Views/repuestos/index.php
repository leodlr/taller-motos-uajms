<?php $isAdmin = $user['rol'] === 'ADMIN'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Repuestos</h1>
  <?php if ($isAdmin): ?><a href="/index.php?r=repuestos.form" class="btn btn-primary">Nuevo repuesto</a><?php endif; ?>
</div>
<form class="row g-2 mb-3" method="get" action="/index.php">
  <input type="hidden" name="r" value="repuestos">
  <div class="col-sm-6 col-md-4"><input type="search" name="q" class="form-control" placeholder="Buscar por código o nombre" value="<?= e($q) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover bg-white align-middle">
  <thead class="table-dark"><tr><th>Código</th><th>Nombre</th><?php if ($isAdmin): ?><th class="text-end">Precio (Bs)</th><?php endif; ?><th class="text-end">Stock</th><th class="text-end">Mínimo</th><th>Estado</th><?php if ($isAdmin): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($items as $it): $crit = (int) $it['stock_actual'] <= (int) $it['stock_minimo']; ?>
    <tr>
      <td><?= e($it['codigo']) ?></td>
      <td><?= e($it['nombre']) ?></td>
      <?php if ($isAdmin): ?><td class="text-end"><?= e(number_format((float) $it['precio_unitario'], 2)) ?></td><?php endif; ?>
      <td class="text-end"><?= (int) $it['stock_actual'] ?></td>
      <td class="text-end"><?= (int) $it['stock_minimo'] ?></td>
      <td><span class="badge bg-<?= $crit ? 'danger' : 'success' ?>"><?= $crit ? 'Stock crítico' : 'OK' ?></span></td>
      <?php if ($isAdmin): ?>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="/index.php?r=repuestos.form&amp;id=<?= (int) $it['id_repuesto'] ?>">Editar</a>
        <form method="post" action="/index.php?r=repuestos.delete" class="d-inline" data-confirm="¿Eliminar este repuesto?">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $it['id_repuesto'] ?>">
          <button class="btn btn-sm btn-outline-danger">Eliminar</button>
        </form>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="7" class="text-center text-muted">Sin resultados.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

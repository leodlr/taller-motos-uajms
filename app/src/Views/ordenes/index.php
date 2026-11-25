<?php $badge = ['Recibida' => 'secondary', 'En diagnóstico' => 'info', 'En reparación' => 'warning', 'Lista para entrega' => 'primary', 'Entregada' => 'success']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0"><?= e($title) ?></h1>
  <?php if ($user['rol'] === 'ADMIN'): ?><a href="/index.php?r=ordenes.form" class="btn btn-primary">Nueva orden</a><?php endif; ?>
</div>
<form class="row g-2 mb-3" method="get" action="/index.php">
  <input type="hidden" name="r" value="ordenes">
  <div class="col-sm-5 col-md-4"><input type="search" name="q" class="form-control" placeholder="Código, placa o cliente" value="<?= e($q) ?>"></div>
  <div class="col-sm-4 col-md-3">
    <select name="estado" class="form-select" aria-label="Estado">
      <option value="">Todos los estados</option>
      <?php foreach (Orden::ESTADOS as $s): ?><option value="<?= e($s) ?>" <?= $estado === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Filtrar</button></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover bg-white align-middle">
  <thead class="table-dark"><tr><th>Código</th><th>Ingreso</th><th>Placa</th><th>Cliente</th><th>Mecánico</th><th>Estado</th><th class="text-end">Total (Bs)</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><a href="/index.php?r=ordenes.ver&amp;id=<?= (int) $it['id_orden'] ?>"><?= e($it['codigo_correlativo']) ?></a></td>
      <td><?= e(substr($it['fecha_ingreso'], 0, 16)) ?></td>
      <td><?= e($it['placa']) ?></td>
      <td><?= e($it['cliente']) ?></td>
      <td><?= e($it['mecanico']) ?></td>
      <td><span class="badge bg-<?= e($badge[$it['estado']] ?? 'secondary') ?>"><?= e($it['estado']) ?></span></td>
      <td class="text-end"><?= e(number_format((float) $it['total'], 2)) ?></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="/index.php?r=ordenes.ver&amp;id=<?= (int) $it['id_orden'] ?>">Ver</a>
        <?php if ($user['rol'] === 'ADMIN'): ?>
          <a class="btn btn-sm btn-outline-secondary" href="/index.php?r=ordenes.form&amp;id=<?= (int) $it['id_orden'] ?>">Editar</a>
          <form method="post" action="/index.php?r=ordenes.delete" class="d-inline" data-confirm="¿Eliminar esta orden?">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) $it['id_orden'] ?>">
            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="8" class="text-center text-muted">Sin órdenes.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

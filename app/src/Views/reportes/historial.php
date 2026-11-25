<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Reporte: historial de reparaciones e ingresos</h1>
  <button class="btn btn-outline-secondary d-print-none" onclick="window.print()">Imprimir</button>
</div>
<form class="row g-2 mb-3 d-print-none" method="get" action="/index.php" novalidate>
  <input type="hidden" name="r" value="reportes.historial">
  <div class="col-sm-6 col-md-2">
    <label class="form-label small mb-0" for="desde">Desde</label>
    <input type="date" class="form-control <?= isset($errors['desde']) ? 'is-invalid' : '' ?>" id="desde" name="desde" value="<?= e($desde) ?>">
    <div class="invalid-feedback"><?= e($errors['desde'] ?? '') ?></div>
  </div>
  <div class="col-sm-6 col-md-2">
    <label class="form-label small mb-0" for="hasta">Hasta</label>
    <input type="date" class="form-control <?= isset($errors['hasta']) ? 'is-invalid' : '' ?>" id="hasta" name="hasta" value="<?= e($hasta) ?>">
    <div class="invalid-feedback"><?= e($errors['hasta'] ?? '') ?></div>
  </div>
  <div class="col-sm-6 col-md-2">
    <label class="form-label small mb-0" for="placa">Placa</label>
    <input class="form-control" id="placa" name="placa" maxlength="15" data-uppercase value="<?= e($placa) ?>">
  </div>
  <div class="col-sm-6 col-md-3">
    <label class="form-label small mb-0" for="cliente">Cliente (nombre o CI)</label>
    <input class="form-control" id="cliente" name="cliente" maxlength="120" value="<?= e($cliente) ?>">
  </div>
  <div class="col-auto d-flex align-items-end gap-2">
    <button class="btn btn-primary">Filtrar</button>
    <a class="btn btn-outline-secondary" href="/index.php?r=reportes.historial">Limpiar</a>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card"><div class="card-body py-2"><div class="text-muted small">Órdenes</div><div class="fs-5"><?= count($items) ?></div></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body py-2"><div class="text-muted small">Ingresos (órdenes entregadas)</div><div class="fs-5">Bs <?= e(number_format($ingresos, 2)) ?></div></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body py-2"><div class="text-muted small">Total facturado (todas las órdenes)</div><div class="fs-5">Bs <?= e(number_format($facturado, 2)) ?></div></div></div></div>
</div>

<div class="table-responsive">
<table class="table table-striped table-sm bg-white align-middle">
  <thead class="table-dark"><tr><th>Código</th><th>Ingreso</th><th>Entrega</th><th>Placa</th><th>Cliente</th><th>Mecánico</th><th>Estado</th><th class="text-end">Mano obra</th><th class="text-end">Total (Bs)</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><?= e($it['codigo_correlativo']) ?></td>
      <td><?= e(substr($it['fecha_ingreso'], 0, 16)) ?></td>
      <td><?= e($it['fecha_entrega'] ? substr($it['fecha_entrega'], 0, 16) : '-') ?></td>
      <td><?= e($it['placa']) ?></td>
      <td><?= e($it['cliente']) ?></td>
      <td><?= e($it['mecanico']) ?></td>
      <td><?= e($it['estado']) ?></td>
      <td class="text-end"><?= e(number_format((float) $it['costo_mano_obra'], 2)) ?></td>
      <td class="text-end"><?= e(number_format((float) $it['total'], 2)) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$items): ?><tr><td colspan="9" class="text-center text-muted">Sin resultados para los filtros indicados.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

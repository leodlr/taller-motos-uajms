<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Reporte: repuestos con stock crítico</h1>
  <button class="btn btn-outline-secondary d-print-none" onclick="window.print()">Imprimir</button>
</div>
<?php if (!$items): ?>
  <div class="alert alert-success">No hay repuestos con stock crítico.</div>
<?php else: ?>
  <div class="alert alert-danger"><?= count($items) ?> repuesto(s) con stock igual o inferior al mínimo.</div>
  <div class="table-responsive">
  <table class="table table-striped bg-white align-middle">
    <thead class="table-dark"><tr><th>Código</th><th>Repuesto</th><th class="text-end">Stock actual</th><th class="text-end">Stock mínimo</th><th class="text-end">Faltante para el mínimo</th><th>Estado</th></tr></thead>
    <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= e($it['codigo']) ?></td>
        <td><?= e($it['nombre']) ?></td>
        <td class="text-end"><?= (int) $it['stock_actual'] ?></td>
        <td class="text-end"><?= (int) $it['stock_minimo'] ?></td>
        <td class="text-end"><?= max(0, (int) $it['faltante']) ?></td>
        <td><span class="badge bg-<?= (int) $it['stock_actual'] === 0 ? 'danger' : 'warning text-dark' ?>"><?= (int) $it['stock_actual'] === 0 ? 'Agotado' : 'Crítico' ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>

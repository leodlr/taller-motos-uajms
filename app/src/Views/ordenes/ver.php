<?php
$badge = ['Recibida' => 'secondary', 'En diagnóstico' => 'info', 'En reparación' => 'warning', 'Lista para entrega' => 'primary', 'Entregada' => 'success'];
$cerrada = $orden['estado'] === 'Entregada';
$subRep = 0.0;
foreach ($detalle as $d) {
    $subRep += (int) $d['cantidad'] * (float) $d['precio_aplicado'];
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0"><?= e($orden['codigo_correlativo']) ?>
    <span class="badge bg-<?= e($badge[$orden['estado']] ?? 'secondary') ?> align-middle"><?= e($orden['estado']) ?></span></h1>
  <div class="d-flex gap-2">
    <?php if ($user['rol'] === 'ADMIN'): ?><a class="btn btn-outline-secondary" href="/index.php?r=ordenes.form&amp;id=<?= (int) $orden['id_orden'] ?>">Editar datos</a><?php endif; ?>
    <a class="btn btn-secondary" href="/index.php?r=ordenes">Volver</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h2 class="h6 text-uppercase text-muted">Datos de la orden</h2>
      <dl class="row mb-0">
        <dt class="col-sm-4">Moto</dt><dd class="col-sm-8"><?= e($orden['placa']) ?> - <?= e($orden['marca']) ?> <?= e($orden['modelo']) ?></dd>
        <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8"><?= e($orden['cliente']) ?> (<?= e($orden['ci']) ?>)</dd>
        <dt class="col-sm-4">Mecánico</dt><dd class="col-sm-8"><?= e($orden['mecanico']) ?></dd>
        <dt class="col-sm-4">Ingreso</dt><dd class="col-sm-8"><?= e($orden['fecha_ingreso']) ?></dd>
        <dt class="col-sm-4">Entrega</dt><dd class="col-sm-8"><?= e($orden['fecha_entrega'] ?? '-') ?></dd>
        <dt class="col-sm-4">Falla</dt><dd class="col-sm-8"><?= nl2br(e($orden['falla_reportada'])) ?></dd>
      </dl>
    </div></div>
  </div>

  <div class="col-lg-6">
    <form method="post" action="/index.php?r=ordenes.update" id="form-estado" class="card h-100" novalidate>
      <div class="card-body">
        <h2 class="h6 text-uppercase text-muted">Estado y diagnóstico</h2>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $orden['id_orden'] ?>">
        <div class="mb-3">
          <label class="form-label" for="estado">Estado</label>
          <select class="form-select" id="estado" name="estado" required>
            <?php foreach (Orden::ESTADOS as $s): ?>
              <option value="<?= e($s) ?>" <?= $orden['estado'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="diagnostico">Diagnóstico técnico</label>
          <textarea class="form-control" id="diagnostico" name="diagnostico" rows="4" maxlength="2000"><?= e($orden['diagnostico'] ?? '') ?></textarea>
          <div class="invalid-feedback">Registre el diagnóstico técnico antes de marcar como Entregada.</div>
        </div>
        <button class="btn btn-primary" type="submit">Guardar estado</button>
      </div>
    </form>
  </div>
</div>

<div class="card mt-3"><div class="card-body">
  <h2 class="h6 text-uppercase text-muted">Repuestos utilizados</h2>
  <div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead><tr><th>Código</th><th>Repuesto</th><th class="text-end">Cant.</th><th class="text-end">Precio aplicado</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($detalle as $d): ?>
      <tr>
        <td><?= e($d['codigo']) ?></td><td><?= e($d['nombre']) ?></td>
        <td class="text-end"><?= (int) $d['cantidad'] ?></td>
        <td class="text-end"><?= e(number_format((float) $d['precio_aplicado'], 2)) ?></td>
        <td class="text-end"><?= e(number_format((int) $d['cantidad'] * (float) $d['precio_aplicado'], 2)) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$detalle): ?><tr><td colspan="5" class="text-center text-muted">Sin repuestos registrados.</td></tr><?php endif; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="4" class="text-end">Repuestos</td><td class="text-end"><?= e(number_format($subRep, 2)) ?></td></tr>
      <tr><td colspan="4" class="text-end">Mano de obra</td><td class="text-end"><?= e(number_format((float) $orden['costo_mano_obra'], 2)) ?></td></tr>
      <tr class="fw-bold"><td colspan="4" class="text-end">TOTAL (Bs)</td><td class="text-end"><?= e(number_format((float) $orden['total'], 2)) ?></td></tr>
    </tfoot>
  </table>
  </div>

  <?php if ($cerrada): ?>
    <div class="alert alert-secondary mb-0">La orden está entregada: no admite más repuestos.</div>
  <?php else: ?>
  <form method="post" action="/index.php?r=ordenes.addrep" class="needs-validation row g-2 align-items-end" novalidate>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) $orden['id_orden'] ?>">
    <div class="col-md-6">
      <label class="form-label" for="id_repuesto">Agregar repuesto</label>
      <select class="form-select" id="id_repuesto" name="id_repuesto" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($repuestos as $r): ?>
          <option value="<?= (int) $r['id_repuesto'] ?>" data-stock="<?= (int) $r['stock_actual'] ?>">
            <?= e($r['codigo']) ?> - <?= e($r['nombre']) ?> (stock: <?= (int) $r['stock_actual'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <div class="invalid-feedback">Seleccione un repuesto.</div>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="cantidad">Cantidad</label>
      <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" step="1" value="1" required>
      <div class="invalid-feedback">Cantidad entre 1 y el stock disponible.</div>
    </div>
    <div class="col-md-3"><button class="btn btn-success w-100" type="submit">Agregar</button></div>
  </form>
  <?php endif; ?>
</div></div>

<script>
  // Bloqueo en cliente: no permitir Entregada sin diagnóstico
  (function () {
    var form = document.getElementById('form-estado');
    var estado = document.getElementById('estado');
    var diag = document.getElementById('diagnostico');
    function check() {
      var bad = estado.value === 'Entregada' && diag.value.trim() === '';
      diag.setCustomValidity(bad ? 'Diagnóstico requerido' : '');
      diag.classList.toggle('is-invalid', bad && form.classList.contains('was-validated'));
    }
    estado.addEventListener('change', check);
    diag.addEventListener('input', check);
    form.addEventListener('submit', function (e) {
      check();
      form.classList.add('was-validated');
      if (!form.checkValidity()) { e.preventDefault(); diag.classList.toggle('is-invalid', diag.validationMessage !== ''); }
    });
  })();
  // Cantidad máxima = stock del repuesto elegido
  (function () {
    var sel = document.getElementById('id_repuesto');
    var qty = document.getElementById('cantidad');
    if (!sel) { return; }
    sel.addEventListener('change', function () {
      var opt = sel.options[sel.selectedIndex];
      if (opt && opt.dataset.stock) { qty.max = opt.dataset.stock; } else { qty.removeAttribute('max'); }
    });
  })();
</script>

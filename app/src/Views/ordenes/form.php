<h1 class="h4 mb-3"><?= e($title) ?></h1>
<form method="post" action="/index.php?r=ordenes.save" class="needs-validation card card-body" novalidate style="max-width:640px">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <?php if ($id): ?><p class="text-muted small mb-3">El código correlativo se asigna automáticamente y no se puede modificar.</p><?php endif; ?>
  <div class="mb-3">
    <label class="form-label" for="id_moto">Moto</label>
    <select class="form-select <?= isset($errors['id_moto']) ? 'is-invalid' : '' ?>" id="id_moto" name="id_moto" required>
      <option value="">-- Seleccione --</option>
      <?php foreach ($motos as $m): ?>
        <option value="<?= (int) $m['id_moto'] ?>" <?= (int) $item['id_moto'] === (int) $m['id_moto'] ? 'selected' : '' ?>>
          <?= e($m['placa']) ?> - <?= e($m['marca']) ?> <?= e($m['modelo']) ?> (<?= e($m['nombre_completo']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <div class="invalid-feedback"><?= e($errors['id_moto'] ?? 'Seleccione una moto.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="id_mecanico">Mecánico asignado</label>
    <select class="form-select <?= isset($errors['id_mecanico']) ? 'is-invalid' : '' ?>" id="id_mecanico" name="id_mecanico" required>
      <option value="">-- Seleccione --</option>
      <?php foreach ($mecanicos as $u): ?>
        <option value="<?= (int) $u['id_usuario'] ?>" <?= (int) $item['id_mecanico'] === (int) $u['id_usuario'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="invalid-feedback"><?= e($errors['id_mecanico'] ?? 'Seleccione un mecánico.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="falla_reportada">Falla reportada</label>
    <textarea class="form-control <?= isset($errors['falla_reportada']) ? 'is-invalid' : '' ?>" id="falla_reportada" name="falla_reportada" rows="3" required minlength="5" maxlength="1000"><?= e($item['falla_reportada']) ?></textarea>
    <div class="invalid-feedback"><?= e($errors['falla_reportada'] ?? 'Describa la falla (5 a 1000 caracteres).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="costo_mano_obra">Costo de mano de obra (Bs)</label>
    <input type="number" step="0.01" min="0" max="99999999.99" class="form-control <?= isset($errors['costo_mano_obra']) ? 'is-invalid' : '' ?>" id="costo_mano_obra" name="costo_mano_obra" required value="<?= e($item['costo_mano_obra']) ?>">
    <div class="invalid-feedback"><?= e($errors['costo_mano_obra'] ?? 'Costo inválido (>= 0).') ?></div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="/index.php?r=<?= $id ? 'ordenes.ver&amp;id=' . (int) $id : 'ordenes' ?>">Cancelar</a>
  </div>
</form>

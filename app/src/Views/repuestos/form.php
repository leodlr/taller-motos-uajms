<h1 class="h4 mb-3"><?= e($title) ?></h1>
<form method="post" action="/index.php?r=repuestos.save" class="needs-validation card card-body" novalidate style="max-width:560px">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="mb-3">
    <label class="form-label" for="codigo">Código</label>
    <input class="form-control <?= isset($errors['codigo']) ? 'is-invalid' : '' ?>" id="codigo" name="codigo" required data-uppercase
           pattern="[A-Za-z0-9_\-]{2,30}" maxlength="30" value="<?= e($item['codigo']) ?>">
    <div class="invalid-feedback"><?= e($errors['codigo'] ?? 'Código inválido (2 a 30 caracteres).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="nombre">Nombre</label>
    <input class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" required minlength="2" maxlength="120" value="<?= e($item['nombre']) ?>">
    <div class="invalid-feedback"><?= e($errors['nombre'] ?? 'Ingrese el nombre.') ?></div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3">
      <label class="form-label" for="precio_unitario">Precio unitario (Bs)</label>
      <input type="number" step="0.01" min="0" max="99999999.99" class="form-control <?= isset($errors['precio_unitario']) ? 'is-invalid' : '' ?>" id="precio_unitario" name="precio_unitario" required value="<?= e($item['precio_unitario']) ?>">
      <div class="invalid-feedback"><?= e($errors['precio_unitario'] ?? 'Precio inválido (>= 0).') ?></div>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label" for="stock_actual">Stock actual</label>
      <input type="number" step="1" min="0" class="form-control <?= isset($errors['stock_actual']) ? 'is-invalid' : '' ?>" id="stock_actual" name="stock_actual" required value="<?= e($item['stock_actual']) ?>">
      <div class="invalid-feedback"><?= e($errors['stock_actual'] ?? 'Entero >= 0.') ?></div>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label" for="stock_minimo">Stock mínimo</label>
      <input type="number" step="1" min="0" class="form-control <?= isset($errors['stock_minimo']) ? 'is-invalid' : '' ?>" id="stock_minimo" name="stock_minimo" required value="<?= e($item['stock_minimo']) ?>">
      <div class="invalid-feedback"><?= e($errors['stock_minimo'] ?? 'Entero >= 0.') ?></div>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="/index.php?r=repuestos">Cancelar</a>
  </div>
</form>

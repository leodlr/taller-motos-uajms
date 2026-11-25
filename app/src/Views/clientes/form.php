<h1 class="h4 mb-3"><?= e($title) ?></h1>
<form method="post" action="/index.php?r=clientes.save" class="needs-validation card card-body" novalidate style="max-width:560px">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="mb-3">
    <label class="form-label" for="ci">CI</label>
    <input class="form-control <?= isset($errors['ci']) ? 'is-invalid' : '' ?>" id="ci" name="ci" required
           pattern="[A-Za-z0-9\-]{5,20}" maxlength="20" value="<?= e($item['ci']) ?>">
    <div class="invalid-feedback"><?= e($errors['ci'] ?? 'CI inválido (5 a 20 caracteres alfanuméricos).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="nombre_completo">Nombre completo</label>
    <input class="form-control <?= isset($errors['nombre_completo']) ? 'is-invalid' : '' ?>" id="nombre_completo" name="nombre_completo"
           required minlength="3" maxlength="120" value="<?= e($item['nombre_completo']) ?>">
    <div class="invalid-feedback"><?= e($errors['nombre_completo'] ?? 'Ingrese el nombre (3 a 120 caracteres).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="telefono">Teléfono</label>
    <input class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono" required
           pattern="[0-9+\-\s]{7,25}" maxlength="25" value="<?= e($item['telefono']) ?>">
    <div class="invalid-feedback"><?= e($errors['telefono'] ?? 'Teléfono inválido (7 a 25 dígitos).') ?></div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="/index.php?r=clientes">Cancelar</a>
  </div>
</form>

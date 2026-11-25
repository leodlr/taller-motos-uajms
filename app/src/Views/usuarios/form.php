<h1 class="h4 mb-3"><?= e($title) ?></h1>
<form method="post" action="/index.php?r=usuarios.save" class="needs-validation card card-body" novalidate style="max-width:560px" autocomplete="off">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="mb-3">
    <label class="form-label" for="nombre">Nombre completo</label>
    <input class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" required minlength="3" maxlength="100" value="<?= e($item['nombre']) ?>">
    <div class="invalid-feedback"><?= e($errors['nombre'] ?? 'Ingrese el nombre (3 a 100 caracteres).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="username">Username</label>
    <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" required pattern="[A-Za-z0-9._\-]{3,50}" maxlength="50" value="<?= e($item['username']) ?>">
    <div class="invalid-feedback"><?= e($errors['username'] ?? 'Username inválido (3 a 50: letras, números, . _ -).') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Contraseña <?= $id ? '<small class="text-muted">(vacío = conservar la actual)</small>' : '' ?></label>
    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" <?= $id ? '' : 'required' ?>>
    <div class="invalid-feedback"><?= e($errors['password'] ?? 'La contraseña debe tener entre 8 y 72 caracteres.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="rol">Rol</label>
    <select class="form-select <?= isset($errors['rol']) ? 'is-invalid' : '' ?>" id="rol" name="rol" required <?= $esPropio ? 'disabled' : '' ?>>
      <?php foreach (['ADMIN' => 'Administrador', 'MECANICO' => 'Mecánico'] as $v => $l): ?>
        <option value="<?= e($v) ?>" <?= $item['rol'] === $v ? 'selected' : '' ?>><?= e($l) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="invalid-feedback"><?= e($errors['rol'] ?? 'Seleccione un rol.') ?></div>
  </div>
  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?= (int) $item['activo'] === 1 ? 'checked' : '' ?> <?= $esPropio ? 'disabled' : '' ?>>
    <label class="form-check-label" for="activo">Cuenta activa</label>
  </div>
  <?php if ($esPropio): ?><p class="text-muted small">No puede cambiar su propio rol ni desactivar su propia cuenta.</p><?php endif; ?>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="/index.php?r=usuarios">Cancelar</a>
  </div>
</form>

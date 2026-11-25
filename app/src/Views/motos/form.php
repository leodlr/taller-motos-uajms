<?php $maxYear = (int) date('Y') + 1; ?>
<h1 class="h4 mb-3"><?= e($title) ?></h1>
<form method="post" action="/index.php?r=motos.save" class="needs-validation card card-body" novalidate style="max-width:560px">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="mb-3">
    <label class="form-label" for="id_cliente">Cliente</label>
    <select class="form-select <?= isset($errors['id_cliente']) ? 'is-invalid' : '' ?>" id="id_cliente" name="id_cliente" required>
      <option value="">-- Seleccione --</option>
      <?php foreach ($clientes as $c): ?>
        <option value="<?= (int) $c['id_cliente'] ?>" <?= (int) $item['id_cliente'] === (int) $c['id_cliente'] ? 'selected' : '' ?>>
          <?= e($c['nombre_completo']) ?> (<?= e($c['ci']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <div class="invalid-feedback"><?= e($errors['id_cliente'] ?? 'Seleccione un cliente.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="placa">Placa</label>
    <input class="form-control <?= isset($errors['placa']) ? 'is-invalid' : '' ?>" id="placa" name="placa" required data-uppercase
           pattern="[A-Za-z0-9\-]{5,15}" maxlength="15" value="<?= e($item['placa']) ?>">
    <div class="invalid-feedback"><?= e($errors['placa'] ?? 'Placa inválida (5 a 15 caracteres).') ?></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label" for="marca">Marca</label>
      <input class="form-control <?= isset($errors['marca']) ? 'is-invalid' : '' ?>" id="marca" name="marca" required minlength="2" maxlength="50" value="<?= e($item['marca']) ?>">
      <div class="invalid-feedback"><?= e($errors['marca'] ?? 'Ingrese la marca.') ?></div>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label" for="modelo">Modelo</label>
      <input class="form-control <?= isset($errors['modelo']) ? 'is-invalid' : '' ?>" id="modelo" name="modelo" required minlength="2" maxlength="60" value="<?= e($item['modelo']) ?>">
      <div class="invalid-feedback"><?= e($errors['modelo'] ?? 'Ingrese el modelo.') ?></div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label" for="anio">Año</label>
      <input type="number" class="form-control <?= isset($errors['anio']) ? 'is-invalid' : '' ?>" id="anio" name="anio" required min="1950" max="<?= $maxYear ?>" value="<?= e($item['anio']) ?>">
      <div class="invalid-feedback"><?= e($errors['anio'] ?? "Año entre 1950 y $maxYear.") ?></div>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label" for="color">Color</label>
      <input class="form-control <?= isset($errors['color']) ? 'is-invalid' : '' ?>" id="color" name="color" required minlength="2" maxlength="30" value="<?= e($item['color']) ?>">
      <div class="invalid-feedback"><?= e($errors['color'] ?? 'Ingrese el color.') ?></div>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="/index.php?r=motos">Cancelar</a>
  </div>
</form>

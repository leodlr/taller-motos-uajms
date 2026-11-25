<?php $isAdmin = $user['rol'] === 'ADMIN'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Motos<?php if (!empty($clienteFiltro)): ?> de <?= e($clienteFiltro['nombre_completo']) ?><?php endif; ?></h1>
  <?php if ($isAdmin): ?><a href="/index.php?r=motos.form<?= !empty($clienteFiltro) ? '&amp;id_cliente=' . (int) $clienteFiltro['id_cliente'] : '' ?>" class="btn btn-primary">Nueva moto</a><?php endif; ?>
</div>
<?php if (!empty($clienteFiltro)): ?>
  <p><a href="/index.php?r=motos">Ver todas las motos</a></p>
<?php endif; ?>
<form class="row g-2 mb-3" method="get" action="/index.php">
  <input type="hidden" name="r" value="motos">
  <?php if (!empty($clienteFiltro)): ?><input type="hidden" name="id_cliente" value="<?= (int) $clienteFiltro['id_cliente'] ?>"><?php endif; ?>
  <div class="col-sm-6 col-md-4"><input type="search" name="q" class="form-control" placeholder="Buscar por placa, marca, modelo o cliente" value="<?= e($q) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover bg-white align-middle">
  <thead class="table-dark"><tr><th>Placa</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Color</th><th>Cliente</th><?php if ($isAdmin): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><?= e($it['placa']) ?></td>
      <td><?= e($it['marca']) ?></td>
      <td><?= e($it['modelo']) ?></td>
      <td><?= (int) $it['anio'] ?></td>
      <td><?= e($it['color']) ?></td>
      <td><?= e($it['nombre_completo']) ?> <small class="text-muted">(<?= e($it['ci']) ?>)</small></td>
      <?php if ($isAdmin): ?>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="/index.php?r=motos.form&amp;id=<?= (int) $it['id_moto'] ?>">Editar</a>
        <form method="post" action="/index.php?r=motos.delete" class="d-inline" data-confirm="¿Eliminar esta moto?">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $it['id_moto'] ?>">
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

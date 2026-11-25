<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingresar - Taller de Motos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
  <div class="container" style="max-width:400px">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 text-center mb-3">Taller de Motos</h1>
        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="/index.php?r=login" novalidate class="needs-validation">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="username" name="username" maxlength="50" required autofocus
                   value="<?= htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="invalid-feedback">Ingrese su usuario.</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" maxlength="72" required>
            <div class="invalid-feedback">Ingrese su contraseña.</div>
          </div>
          <button class="btn btn-primary w-100" type="submit">Ingresar</button>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.querySelector('form').addEventListener('submit', function (e) {
      if (!this.checkValidity()) { e.preventDefault(); }
      this.classList.add('was-validated');
    });
  </script>
</body>
</html>

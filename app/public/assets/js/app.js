// Validación HTML5 con estilos Bootstrap
document.querySelectorAll('form.needs-validation').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});

// Confirmación antes de eliminar
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!window.confirm(form.dataset.confirm)) {
      e.preventDefault();
    }
  });
});

// Mayúsculas automáticas (placa, código)
document.querySelectorAll('input[data-uppercase]').forEach(function (input) {
  input.addEventListener('input', function () {
    input.value = input.value.toUpperCase();
  });
});

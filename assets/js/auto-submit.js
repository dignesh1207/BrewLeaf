// auto-submits the form when a .auto-submit field changes
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.auto-submit').forEach(function (field) {
    field.addEventListener('change', function () {
      field.form.submit();
    });
  });
});

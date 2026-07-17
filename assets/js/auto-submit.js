/**
 * assets/js/auto-submit.js
 * ---------------------------------------------------------------------------
 * Any radio button, checkbox, or dropdown marked class="auto-submit"
 * submits its form the instant its value changes -- no separate "Save"
 * button needed. Used by the admin theme picker (pick a theme card, it
 * applies immediately) and the order status dropdown in admin/orders.php.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.auto-submit').forEach(function (field) {
    field.addEventListener('change', function () {
      field.form.submit();
    });
  });
});

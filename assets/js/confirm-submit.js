/**
 * assets/js/confirm-submit.js
 * ---------------------------------------------------------------------------
 * Any form marked data-confirm="some question" pops up a Yes/Cancel box
 * before it submits, and stops the submit if the user clicks Cancel. Used
 * for "Delete this product?" / "Remove this option?" buttons in admin.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });
});

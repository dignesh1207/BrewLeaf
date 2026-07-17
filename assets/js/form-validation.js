/**
 * assets/js/form-validation.js
 * ---------------------------------------------------------------------------
 * Simple, beginner-level client-side checks: required fields, a valid-looking
 * email, and "password" matching "password_confirm". Only runs on forms
 * marked with data-validate (login/register/contact/checkout forms).
 *
 * This is just a nicer experience before the page reloads -- the real,
 * trustworthy checks still happen in PHP on the server.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var valid = true;
      form.querySelectorAll('[required]').forEach(function (field) {
        clearFieldError(field);
        if (!field.value.trim()) {
          showFieldError(field, 'This field is required.');
          valid = false;
        } else if (field.type === 'email' && !/^\S+@\S+\.\S+$/.test(field.value)) {
          showFieldError(field, 'Enter a valid email address.');
          valid = false;
        }
      });

      var pass = form.querySelector('[name="password"]');
      var confirm = form.querySelector('[name="password_confirm"]');
      if (pass && confirm && pass.value !== confirm.value) {
        showFieldError(confirm, 'Passwords do not match.');
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  });
});

/** Adds a red error message under a field and marks the field itself red. */
function showFieldError(field, message) {
  var err = document.createElement('div');
  err.className = 'form-error field-error';
  err.textContent = message;
  field.insertAdjacentElement('afterend', err);
  field.classList.add('field-invalid');
}

/** Removes the red error message/border added by showFieldError above. */
function clearFieldError(field) {
  field.classList.remove('field-invalid');
  var next = field.nextElementSibling;
  if (next && next.classList.contains('field-error')) next.remove();
}

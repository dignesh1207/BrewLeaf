// basic client side checks - required fields, email format, matching passwords
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

      // just for ux, php re-checks all of this anyway
      if (!valid) e.preventDefault();
    });
  });
});

// shows the red error text under a field
function showFieldError(field, message) {
  var err = document.createElement('div');
  err.className = 'form-error field-error';
  err.textContent = message;
  field.insertAdjacentElement('afterend', err);
  field.classList.add('field-invalid');
}

// undoes showFieldError
function clearFieldError(field) {
  field.classList.remove('field-invalid');
  var next = field.nextElementSibling;
  if (next && next.classList.contains('field-error')) next.remove();
}

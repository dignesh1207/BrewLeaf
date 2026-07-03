/**
 * assets/js/main.js
 * ---------------------------------------------------------------------------
 * Shared front-end behaviour for every page: mobile nav toggle, product
 * option-pill selection, add-to-cart (AJAX), quantity steppers on the cart
 * page, and lightweight client-side form validation.
 * ---------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {
  initMobileNav();
  initOptionPills();
  initAddToCartForms();
  initQuantityInputs();
  initFormValidation();
});

/** Hamburger menu toggle for small screens. */
function initMobileNav() {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var isOpen = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

/**
 * Product detail page: clicking an option pill selects it (and deselects
 * its siblings within the same option group), then updates a hidden input
 * and recalculates the displayed price.
 */
function initOptionPills() {
  document.querySelectorAll('.option-group').forEach(function (group) {
    var pills = group.querySelectorAll('.option-pill');
    pills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('selected'); });
        pill.classList.add('selected');

        var hiddenInput = group.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = pill.dataset.optionId;

        recalcPrice();
      });
    });
  });
}

/** Sum base price + selected option modifiers, update the on-page total. */
function recalcPrice() {
  var priceEl = document.getElementById('livePrice');
  if (!priceEl) return;

  var base = parseFloat(priceEl.dataset.basePrice || '0');
  var total = base;

  document.querySelectorAll('.option-pill.selected').forEach(function (pill) {
    total += parseFloat(pill.dataset.priceModifier || '0');
  });

  priceEl.textContent = '$' + total.toFixed(2);
}

/** Progressive enhancement: submit add-to-cart forms via fetch, show a toast. */
function initAddToCartForms() {
  document.querySelectorAll('form.add-to-cart-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      // If fetch isn't available for some reason, fall back to normal submit.
      if (!window.fetch) return;
      e.preventDefault();

      var formData = new FormData(form);
      fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          showToast(data.message || 'Added to cart');
          var badge = document.querySelector('.cart-badge');
          if (data.cartCount !== undefined) {
            if (badge) {
              badge.textContent = data.cartCount;
            } else if (data.cartCount > 0) {
              var cartLink = document.querySelector('.cart-link');
              if (cartLink) {
                var span = document.createElement('span');
                span.className = 'cart-badge';
                span.textContent = data.cartCount;
                cartLink.appendChild(span);
              }
            }
          }
        })
        .catch(function () {
          // Network/parse error: fall back to a normal page submit.
          form.submit();
        });
    });
  });
}

/** Simple toast notification, auto-dismisses after 2.5s. */
function showToast(message) {
  var toast = document.createElement('div');
  toast.textContent = message;
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;background:#4a3225;color:#fff;' +
    'padding:.8rem 1.2rem;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.2);z-index:9999;' +
    'font-family:inherit;opacity:0;transition:opacity .25s ease;';
  document.body.appendChild(toast);
  requestAnimationFrame(function () { toast.style.opacity = '1'; });
  setTimeout(function () {
    toast.style.opacity = '0';
    setTimeout(function () { toast.remove(); }, 300);
  }, 2500);
}

/** Cart page: +/- steppers next to quantity inputs. */
function initQuantityInputs() {
  document.querySelectorAll('.qty-stepper').forEach(function (stepper) {
    var input = stepper.querySelector('input[type="number"]');
    var minus = stepper.querySelector('.qty-minus');
    var plus = stepper.querySelector('.qty-plus');
    if (!input) return;

    if (minus) minus.addEventListener('click', function () {
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
      input.dispatchEvent(new Event('change'));
    });
    if (plus) plus.addEventListener('click', function () {
      input.value = (parseInt(input.value, 10) || 1) + 1;
      input.dispatchEvent(new Event('change'));
    });
  });
}

/** Lightweight required-field / email / password-match validation. */
function initFormValidation() {
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
}

function showFieldError(field, message) {
  var err = document.createElement('div');
  err.className = 'form-error field-error';
  err.textContent = message;
  field.insertAdjacentElement('afterend', err);
  field.style.borderColor = 'var(--color-danger)';
}

function clearFieldError(field) {
  field.style.borderColor = '';
  var next = field.nextElementSibling;
  if (next && next.classList.contains('field-error')) next.remove();
}

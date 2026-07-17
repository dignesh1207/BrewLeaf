/**
 * assets/js/cart.js
 * ---------------------------------------------------------------------------
 * Everything about adding/updating items in the cart:
 *   - "Add to cart" forms submit in the background (no page reload) and
 *     show a small toast message.
 *   - The +/- stepper buttons next to a quantity number on the cart page.
 *   - Typing a new quantity directly into the number box auto-submits the
 *     "update cart" form for that row.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  initAddToCartForms();
  initQuantityInputs();
});

/** Submits any form.add-to-cart-form via fetch() instead of a full page
 *  reload, then shows a toast and updates the cart count badge. */
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

/** Small message box that pops up in the corner and fades out on its own. */
function showToast(message) {
  var toast = document.createElement('div');
  toast.textContent = message;
  toast.className = 'toast';
  document.body.appendChild(toast);
  requestAnimationFrame(function () { toast.classList.add('toast-visible'); });
  setTimeout(function () {
    toast.classList.remove('toast-visible');
    setTimeout(function () { toast.remove(); }, 300);
  }, 2500);
}

/** Cart page: the +/- buttons next to a quantity box, and auto-submitting
 *  the row's form when the number is typed/changed directly. */
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

    input.addEventListener('change', function () {
      stepper.closest('form').submit();
    });
  });
}

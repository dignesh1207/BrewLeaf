// cart stuff - add-to-cart forms and the cart page qty/remove buttons all save via fetch
document.addEventListener('DOMContentLoaded', function () {
  initAddToCartForms();
  initCartPage();
});

// submits add-to-cart forms via fetch, then shows a toast and updates the cart badge
function initAddToCartForms() {
  document.querySelectorAll('form.add-to-cart-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      // if fetch isn't supported just let the form submit like normal
      if (!window.fetch) return;
      e.preventDefault();

      var formData = new FormData(form);
      // posting in the background, X-Requested-With header tells the php
      // endpoint to send back json instead of the whole html page
      fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          showToast(data.message || 'Added to cart');
          if (data.cartCount !== undefined) updateCartBadge(data.cartCount);
        })
        .catch(function () {
          // fetch failed for some reason, just do a normal submit instead
          form.submit();
        });
    });
  });
}

// little toast message that pops up and fades itself out
function showToast(message) {
  var toast = document.createElement('div');
  toast.textContent = message;
  toast.className = 'toast';
  document.body.appendChild(toast);
  // adding the visible class a frame later so the css transition actually animates
  requestAnimationFrame(function () { toast.classList.add('toast-visible'); });
  setTimeout(function () {
    toast.classList.remove('toast-visible');
    setTimeout(function () { toast.remove(); }, 300);
  }, 2500);
}

// updates the number bubble on the cart icon in the header
function updateCartBadge(count) {
  var badge = document.querySelector('.cart-badge');
  var cartLink = document.querySelector('.cart-link');
  if (count > 0) {
    if (badge) {
      badge.textContent = count;
    } else if (cartLink) {
      var span = document.createElement('span');
      span.className = 'cart-badge';
      span.textContent = count;
      cartLink.appendChild(span);
    }
  } else if (badge) {
    badge.remove();
  }
}

// cart.php's +/- buttons and typed qty save via cart_update.php, remove asks to confirm first
function initCartPage() {
  var itemsBody = document.getElementById('cartItemsBody');
  if (!itemsBody || !window.fetch) return;

  itemsBody.querySelectorAll('.qty-stepper').forEach(function (stepper) {
    var input = stepper.querySelector('.qty-input');
    var minus = stepper.querySelector('.qty-minus');
    var plus = stepper.querySelector('.qty-plus');
    if (!input) return;

    function save(previousValue) {
      // don't let qty go below 1
      var qty = Math.max(1, parseInt(input.value, 10) || 1);
      input.value = qty;
      saveCartChange(stepper, { item_id: stepper.dataset.itemId, action: 'update', quantity: qty }, function onFailure() {
        // put it back to the old value if the save failed
        input.value = previousValue;
      });
    }

    if (minus) minus.addEventListener('click', function () {
      var previousValue = input.value;
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
      save(previousValue);
    });
    if (plus) plus.addEventListener('click', function () {
      var previousValue = input.value;
      input.value = (parseInt(input.value, 10) || 1) + 1;
      save(previousValue);
    });
    input.addEventListener('focus', function () {
      // save the starting value in case we need to undo it on a failed save
      input.dataset.previousValue = input.value;
    });
    input.addEventListener('change', function () {
      save(input.dataset.previousValue || input.value);
    });
  });

  itemsBody.querySelectorAll('.remove-item-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      // confirming here instead of confirm-submit.js so the fetch below waits for the answer
      e.stopImmediatePropagation();
      if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;

      var itemId = form.querySelector('[name="item_id"]').value;
      saveCartChange(form, { item_id: itemId, action: 'remove' });
    });
  });
}

// sends a qty update or remove to cart_update.php, updates totals/badge or shows an error
function saveCartChange(context, payload, onFailure) {
  var row = context.closest('tr');
  var controls = row.querySelectorAll('button, input');
  var spinner = row.querySelector('.spinner');

  function setBusy(busy) {
    controls.forEach(function (el) { el.disabled = busy; });
    if (spinner) spinner.hidden = !busy;
  }

  function fail(message) {
    showCartError(message);
    setBusy(false);
    if (onFailure) onFailure();
  }

  setBusy(true);
  hideCartError();

  var formData = new FormData();
  Object.keys(payload).forEach(function (key) { formData.append(key, payload[key]); });

  fetch('cart_update.php', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (!data.success) {
        fail(data.message || 'Something went wrong updating your cart. Please try again.');
        return;
      }

      if (data.action === 'remove') {
        row.remove();
      } else {
        var totalCell = row.querySelector('.cart-line-total');
        if (totalCell) totalCell.textContent = data.lineTotalFormatted;
        setBusy(false);
      }

      var subtotalEl = document.getElementById('cartSubtotal');
      var shippingEl = document.getElementById('cartShipping');
      var totalEl = document.getElementById('cartTotal');
      if (subtotalEl) subtotalEl.textContent = data.subtotalFormatted;
      if (shippingEl) shippingEl.textContent = data.shippingFormatted;
      if (totalEl) totalEl.textContent = data.totalFormatted;
      updateCartBadge(data.cartCount);

      // last item was just removed, so show the empty cart state instead
      if (data.cartEmpty) {
        var content = document.getElementById('cartContent');
        var empty = document.getElementById('cartEmptyState');
        if (content) content.hidden = true;
        if (empty) empty.hidden = false;
      }
    })
    .catch(function () {
      fail('Something went wrong updating your cart. Please check your connection and try again.');
    });
}

function showCartError(message) {
  var el = document.getElementById('cartError');
  if (!el) return;
  el.textContent = message;
  el.hidden = false;
}

function hideCartError() {
  var el = document.getElementById('cartError');
  if (el) el.hidden = true;
}

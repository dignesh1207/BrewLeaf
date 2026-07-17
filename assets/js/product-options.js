/**
 * assets/js/product-options.js
 * ---------------------------------------------------------------------------
 * Runs only on the product detail page (product.php). Clicking a size/grind
 * "pill" selects it (and un-selects the other pills in its group), then
 * recalculates the live price shown on the page.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
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
});

/** Adds up the base price + every selected option's price change, and
 *  writes the total into the #livePrice element. */
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

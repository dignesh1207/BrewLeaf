// product page, clicking a size/grind pill selects it and updates the price
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.option-group').forEach(function (group) {
    var pills = group.querySelectorAll('.option-pill');
    pills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('selected'); });
        pill.classList.add('selected');

        // hidden input is what actually gets sent when add-to-cart form submits
        var hiddenInput = group.querySelector('input[type="hidden"]');
        if (hiddenInput) hiddenInput.value = pill.dataset.optionId;

        recalcPrice();
      });
    });
  });
});

// adds base price + all the selected options together, updates the live price on the page
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

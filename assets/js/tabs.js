/**
 * assets/js/tabs.js
 * ---------------------------------------------------------------------------
 * Runs only on the home page. Lets a visitor switch between the "Coffee"
 * and "Tea" tab buttons in the "Shop by Category" section without a page
 * reload -- clicking a button shows its matching panel and hides the rest.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.tab-btn').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.tab-panel').forEach(function (p) { p.hidden = true; });

      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      document.getElementById(btn.dataset.target).hidden = false;
    });
  });
});

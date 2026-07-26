// coffee/tea tab switcher for the shop by category section, home page only
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
      // data-target has the id of the panel to show
      document.getElementById(btn.dataset.target).hidden = false;
    });
  });
});

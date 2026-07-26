// hamburger button toggles the mobile nav open/closed
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var isOpen = nav.classList.toggle('open');
    // update aria-expanded too so screen readers know it opened
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
});

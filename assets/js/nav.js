/**
 * assets/js/nav.js
 * ---------------------------------------------------------------------------
 * Makes the hamburger button in the header open/close the mobile menu.
 * Only runs if the button exists on the page (it's in every page's header,
 * but this check keeps the file safe to include everywhere).
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var isOpen = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
});

/**
 * assets/js/hero-video.js
 * ---------------------------------------------------------------------------
 * Runs only on the home page. The "Watch How We Roast" video is set to
 * autoplay in the HTML (index.php) -- this just makes it play back 1.5x
 * faster than normal speed.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  var video = document.querySelector('.section-video');
  if (!video) return;
  video.playbackRate = 1.5;
});

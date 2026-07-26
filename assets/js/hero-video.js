// home page hero video, autoplay is already set in the html
// just speeds it up a little
document.addEventListener('DOMContentLoaded', function () {
  var video = document.querySelector('.section-video');
  if (!video) return;
  video.playbackRate = 1.5;
});

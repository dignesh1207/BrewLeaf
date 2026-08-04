// home page hero video, just speeds it up a little
document.addEventListener('DOMContentLoaded', function () {
  var video = document.querySelector('.section-video');
  if (!video) return;
  video.playbackRate = 1.5;
});

/**
 * assets/js/catalogue-chart.js
 * ---------------------------------------------------------------------------
 * Draws the "Catalogue at a Glance" bar chart on the home page, using
 * Chart.js (loaded from a CDN in includes/footer.php).
 *
 * The numbers come from the database, so index.php can't just hard-code
 * them here -- instead index.php writes them onto the <canvas> element as
 * data-labels / data-counts / data-ratings attributes (see index.php), and
 * this file reads them back out with JSON.parse().
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('catalogueChart');
  if (!canvas || typeof Chart === 'undefined') return;

  var labels = JSON.parse(canvas.dataset.labels || '[]');
  var counts = JSON.parse(canvas.dataset.counts || '[]');
  var ratings = JSON.parse(canvas.dataset.ratings || '[]');

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { label: 'Products', data: counts, backgroundColor: '#6f4e37' },
        { label: 'Avg. Rating (x10)', data: ratings.map(function (r) { return Math.round(r * 2 * 10) / 10; }), backgroundColor: '#c98a3b' }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
});

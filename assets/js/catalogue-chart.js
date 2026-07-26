// bar chart for the "Catalogue at a Glance" section on home page, uses Chart.js from a cdn
// the numbers come from the db so index.php can't hardcode them here -- it writes them
// onto the canvas as data- attributes instead and we just read them back out below
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('catalogueChart');
  if (!canvas || typeof Chart === 'undefined') return;

  // grab the data index.php stuck onto the canvas
  var labels = JSON.parse(canvas.dataset.labels || '[]');
  var counts = JSON.parse(canvas.dataset.counts || '[]');
  var ratings = JSON.parse(canvas.dataset.ratings || '[]');

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { label: 'Products', data: counts, backgroundColor: '#6f4e37' },
        // x2 so the rating bars are roughly the same scale as the count bars
        { label: 'Avg. Rating (x10)', data: ratings.map(function (r) { return Math.round(r * 2 * 10) / 10; }), backgroundColor: '#c98a3b' }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
});

/**
 * assets/js/revenue-chart.js
 * ---------------------------------------------------------------------------
 * Draws the revenue line chart on the admin dashboard, using Chart.js.
 * Same idea as assets/js/catalogue-chart.js: admin/dashboard.php writes the
 * database numbers onto the <canvas> as data-labels / data-revenue
 * attributes, and this file reads them back out.
 * ---------------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('revenueChart');
  if (!canvas || typeof Chart === 'undefined') return;

  var labels = JSON.parse(canvas.dataset.labels || '[]');
  var revenue = JSON.parse(canvas.dataset.revenue || '[]');

  new Chart(canvas, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue ($)',
        data: revenue,
        borderColor: '#6f4e37',
        backgroundColor: 'rgba(111,78,55,.15)',
        fill: true,
        tension: .3
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });
});

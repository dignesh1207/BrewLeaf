// revenue line chart for the admin dashboard, same deal as catalogue-chart.js --
// dashboard.php puts the numbers on the canvas as data- attributes and we read them here

document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('revenueChart');
  if (!canvas || typeof Chart === 'undefined') return;

  // pull the data dashboard.php put on the canvas
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

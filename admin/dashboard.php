<?php
// admin/dashboard.php - the main admin page, shows the stat cards, revenue chart and status list
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// kick out anyone who isn't an admin
require_admin();

$productCount = $conn->query('SELECT COUNT(*) AS n FROM products WHERE is_active = 1')->fetch_assoc()['n'];
$orderCount   = $conn->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'];
$userCount    = $conn->query("SELECT COUNT(*) AS n FROM users WHERE role = 'customer'")->fetch_assoc()['n'];
// don't count cancelled orders in the revenue total
$revenue      = $conn->query('SELECT COALESCE(SUM(total),0) AS n FROM orders WHERE status != "cancelled"')->fetch_assoc()['n'];

// grab order counts/totals grouped by day for the last 2 weeks
$salesData = $conn->query(
    "SELECT DATE(created_at) AS d, COUNT(*) AS n, COALESCE(SUM(total),0) AS revenue
     FROM orders WHERE created_at >= (CURDATE() - INTERVAL 14 DAY)
     GROUP BY DATE(created_at) ORDER BY d"
);
// fill in every day as 0 first, so days with no orders still show up on the chart
$revenueByDay = [];
for ($i = 13; $i >= 0; $i--) {
    $revenueByDay[date('Y-m-d', strtotime("-$i days"))] = 0.0;
}
while ($row = $salesData->fetch_assoc()) {
    $revenueByDay[$row['d']] = (float) $row['revenue'];
}
// split into two separate arrays since that's what the chart wants
$salesLabels = array_keys($revenueByDay);
$salesRevenue = array_values($revenueByDay);

// just the latest status per service here, monitor.php has the full history
$services = $conn->query('SELECT service_name, status FROM service_status ORDER BY id');

$pageTitle = 'Admin Dashboard | BrewLeaf';

require_once __DIR__ . '/../includes/header.php';
$adminActive = 'dashboard';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<!-- HTML for the admin dashboard -->

<section class="section container">
  <h1>Admin Dashboard</h1>

  <div class="dash-grid">
    <div class="dash-card"><div class="value"><?= (int) $productCount ?></div><div class="label">Active Products</div></div>
    <div class="dash-card"><div class="value"><?= (int) $orderCount ?></div><div class="label">Total Orders</div></div>
    <div class="dash-card"><div class="value"><?= (int) $userCount ?></div><div class="label">Registered Customers</div></div>
    <div class="dash-card"><div class="value"><?= money((float) $revenue) ?></div><div class="label">Total Revenue</div></div>
  </div>

  <div class="chart-card chart-card-spaced">
    <h2>Revenue (Last 14 Days)</h2>
    <?php if (array_sum($salesRevenue) == 0): ?>
      <p class="form-hint">No orders yet in this window. Place a demo order to see this chart populate.</p>
    <?php else: ?>
      <?php $maxRevenue = max($salesRevenue) ?: 1; ?>
      <div class="bar-chart" role="img" aria-label="Revenue for each of the last 14 days">
        <?php foreach ($salesLabels as $i => $label): ?>
          <div class="bar-chart-col" title="<?= h(date('M j', strtotime($label))) ?>: <?= h(money($salesRevenue[$i])) ?>">
            <div class="bar-chart-bar" style="height: <?= round($salesRevenue[$i] / $maxRevenue * 100) ?>%"></div>
            <div class="bar-chart-col-label"><?= h(date('j', strtotime($label))) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="chart-card">
    <h2>System Status Snapshot</h2>
    <p>Full detail on the public <a href="<?= h(SITE_BASE_URL) ?>/monitor.php">status page</a>.</p>
    <ul class="chip-list">
      <?php while ($s = $services->fetch_assoc()): ?>
        <li class="status-pill status-<?= h($s['status']) ?>">
          <span class="status-dot"></span> <?= h($s['service_name']) ?>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

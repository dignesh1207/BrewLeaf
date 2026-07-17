<?php
/**
 * admin/dashboard.php -- Admin landing page: key metrics + a live-data
 * revenue chart + a snapshot of the backend service monitor.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$productCount = $conn->query('SELECT COUNT(*) AS n FROM products WHERE is_active = 1')->fetch_assoc()['n'];
$orderCount   = $conn->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'];
$userCount    = $conn->query("SELECT COUNT(*) AS n FROM users WHERE role = 'customer'")->fetch_assoc()['n'];
$revenue      = $conn->query('SELECT COALESCE(SUM(total),0) AS n FROM orders WHERE status != "cancelled"')->fetch_assoc()['n'];

// Orders per day for the last 14 days (data visualization on the admin side).
$salesData = $conn->query(
    "SELECT DATE(created_at) AS d, COUNT(*) AS n, COALESCE(SUM(total),0) AS revenue
     FROM orders WHERE created_at >= (CURDATE() - INTERVAL 14 DAY)
     GROUP BY DATE(created_at) ORDER BY d"
);
$salesLabels = [];
$salesRevenue = [];
while ($row = $salesData->fetch_assoc()) {
    $salesLabels[] = $row['d'];
    $salesRevenue[] = (float) $row['revenue'];
}

$services = $conn->query('SELECT service_name, status FROM service_status ORDER BY id');

$pageTitle = 'Admin Dashboard | BrewLeaf';
require_once __DIR__ . '/../includes/header.php';
$adminActive = 'dashboard';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<section class="section container">
  <h1>Admin Dashboard</h1>

  <div class="dash-grid">
    <div class="dash-card"><div class="value"><?= (int) $productCount ?></div><div class="label">Active Products</div></div>
    <div class="dash-card"><div class="value"><?= (int) $orderCount ?></div><div class="label">Total Orders</div></div>
    <div class="dash-card"><div class="value"><?= (int) $userCount ?></div><div class="label">Registered Customers</div></div>
    <div class="dash-card"><div class="value"><?= money((float) $revenue) ?></div><div class="label">Total Revenue</div></div>
  </div>

  <div class="chart-card chart-card-spaced">
    <h2>Revenue -- Last 14 Days</h2>
    <canvas
      id="revenueChart"
      height="90"
      data-labels="<?= h(json_encode($salesLabels)) ?>"
      data-revenue="<?= h(json_encode($salesRevenue)) ?>"
    ></canvas>
    <?php if (empty($salesLabels)): ?><p class="form-hint">No orders yet in this window -- place a demo order to see this chart populate.</p><?php endif; ?>
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

<!-- The chart itself is set up in assets/js/revenue-chart.js, loaded from
     includes/footer.php -- it reads the data-labels/data-revenue attributes
     above. -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

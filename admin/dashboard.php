<?php
// admin/dashboard.php
// Main dashboard for administrators.
// Displays a quick overview of products, orders, users, revenue, and system status.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Only admins are allowed to view this page
require_admin();

// Count active products
$productCount = $conn->query(
    'SELECT COUNT(*) AS n FROM products WHERE is_active = 1'
)->fetch_assoc()['n'];

// Count all orders
$orderCount = $conn->query(
    'SELECT COUNT(*) AS n FROM orders'
)->fetch_assoc()['n'];

// Count registered customers (ignore admin accounts)
$userCount = $conn->query(
    "SELECT COUNT(*) AS n FROM users WHERE role = 'customer'"
)->fetch_assoc()['n'];

// Calculate total revenue.
// Cancelled orders are not included in the total.
$revenue = $conn->query(
    'SELECT COALESCE(SUM(total),0) AS n FROM orders WHERE status != "cancelled"'
)->fetch_assoc()['n'];

// Get sales data from the last 14 days.
// This will be used to draw the revenue chart.
$salesData = $conn->query(
    "SELECT DATE(created_at) AS d,
            COUNT(*) AS n,
            COALESCE(SUM(total),0) AS revenue
     FROM orders
     WHERE created_at >= (CURDATE() - INTERVAL 14 DAY)
     GROUP BY DATE(created_at)
     ORDER BY d"
);

// Start by filling every day with 0 revenue.
// This keeps the graph looking complete even if
// there were no orders on some days.
$revenueByDay = [];

for ($i = 13; $i >= 0; $i--) {
    $revenueByDay[date('Y-m-d', strtotime("-$i days"))] = 0.0;
}

// Replace the zero values with the actual revenue
// for any days that have orders.
while ($row = $salesData->fetch_assoc()) {
    $revenueByDay[$row['d']] = (float) $row['revenue'];
}

// Separate the dates and revenue values.
// The chart uses one array for labels
// and another for the numbers.
$salesLabels = array_keys($revenueByDay);
$salesRevenue = array_values($revenueByDay);

// Get the latest status for each service.
// The monitor page contains the complete history.
$services = $conn->query(
    'SELECT service_name, status FROM service_status ORDER BY id'
);

$pageTitle = 'Admin Dashboard | BrewLeaf';

require_once __DIR__ . '/../includes/header.php';

$adminActive = 'dashboard';

require_once __DIR__ . '/../includes/admin-nav.php';
?>

<!-- Admin Dashboard -->

<section class="section container">

  <h1>Admin Dashboard</h1>

  <!-- Quick statistics shown at the top -->
  <div class="dash-grid">

    <div class="dash-card">
      <div class="value"><?= (int) $productCount ?></div>
      <div class="label">Active Products</div>
    </div>

    <div class="dash-card">
      <div class="value"><?= (int) $orderCount ?></div>
      <div class="label">Total Orders</div>
    </div>

    <div class="dash-card">
      <div class="value"><?= (int) $userCount ?></div>
      <div class="label">Registered Customers</div>
    </div>

    <div class="dash-card">
      <div class="value"><?= money((float) $revenue) ?></div>
      <div class="label">Total Revenue</div>
    </div>

  </div>

  <!-- Revenue chart -->
  <div class="chart-card chart-card-spaced">

    <h2>Revenue (Last 14 Days)</h2>

    <?php if (array_sum($salesRevenue) == 0): ?>

      <!-- Show this message if no sales have been made recently -->
      <p class="form-hint">
        No orders yet in this window. Place a demo order to see this chart populate.
      </p>

    <?php else: ?>

      <?php
      // Find the highest revenue value so each bar
      // can be scaled correctly.
      $maxRevenue = max($salesRevenue) ?: 1;
      ?>

      <div
        class="bar-chart"
        role="img"
        aria-label="Revenue for each of the last 14 days">

        <?php foreach ($salesLabels as $i => $label): ?>

          <div
            class="bar-chart-col"
            title="<?= h(date('M j', strtotime($label))) ?>: <?= h(money($salesRevenue[$i])) ?>">

            <!-- Height of each bar is based on revenue -->
            <div
              class="bar-chart-bar"
              style="height: <?= round($salesRevenue[$i] / $maxRevenue * 100) ?>%">
            </div>

            <!-- Day number shown below each bar -->
            <div class="bar-chart-col-label">
              <?= h(date('j', strtotime($label))) ?>
            </div>

          </div>

        <?php endforeach; ?>

      </div>

    <?php endif; ?>

  </div>

  <!-- Small snapshot of current system status -->
  <div class="chart-card">

    <h2>System Status Snapshot</h2>

    <p>
      Full detail on the public
      <a href="<?= h(SITE_BASE_URL) ?>/monitor.php">status page</a>.
    </p>

    <ul class="chip-list">

      <?php while ($s = $services->fetch_assoc()): ?>

        <li class="status-pill status-<?= h($s['status']) ?>">

          <span class="status-dot"></span>

          <?= h($s['service_name']) ?>

        </li>

      <?php endwhile; ?>

    </ul>

  </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
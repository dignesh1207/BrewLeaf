<?php
/**
 * monitor.php -- Public backend status page. Runs a handful of live health
 * checks (not just static DB rows) so the "online/offline" state actually
 * reflects the current condition of the site, then persists the result to
 * service_status so admin/dashboard.php can show a snapshot too.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Each check below sets true (online) or false (offline) into $checks.
// Database checks are wrapped in try/catch so a failure in one check can't
// take down the whole page.
$checks = [];

// Database Connection: can we run a simple query right now?
try {
    $conn->query('SELECT 1');
    $checks['Database Connection'] = true;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Database Connection'] = false;
}

// Product Catalogue: does the products table have at least one row?
try {
    $result = $conn->query('SELECT id FROM products LIMIT 1');
    $checks['Product Catalogue'] = $result->num_rows > 0;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Product Catalogue'] = false;
}

// Shopping Cart: the cart relies on PHP sessions, so just confirm the
// session is active.
$checks['Shopping Cart'] = session_status() === PHP_SESSION_ACTIVE;

// Checkout Service: does the orders table exist?
try {
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    $checks['Checkout Service'] = $result->num_rows === 1;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Checkout Service'] = false;
}

// User Authentication: does the users table exist?
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $checks['User Authentication'] = $result->num_rows === 1;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['User Authentication'] = false;
}

// Search / SEO Sitemap: does the sitemap file exist on disk?
$checks['Search / SEO Sitemap'] = file_exists(__DIR__ . '/sitemap.php');

// Persist results so the admin dashboard snapshot stays in sync.
foreach ($checks as $name => $isOnline) {
    $status = $isOnline ? 'online' : 'offline';
    $stmt = $conn->prepare('UPDATE service_status SET status = ? WHERE service_name = ?');
    $stmt->bind_param('ss', $status, $name);
    $stmt->execute();
    $stmt->close();
}

$allOnline = !in_array(false, $checks, true);

$pageTitle = 'System Status | BrewLeaf';
$pageDescription = 'Live status of BrewLeaf website services: database, catalogue, cart, checkout, authentication, and search.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section container">
  <h1>System Status</h1>
  <div class="alert <?= $allOnline ? 'alert-success' : 'alert-error' ?>">
    <?= $allOnline ? 'All systems operational.' : 'Some services are currently experiencing issues.' ?>
  </div>

  <table>
    <thead><tr><th>Service</th><th>Status</th><th>Checked At</th></tr></thead>
    <tbody>
      <?php foreach ($checks as $name => $isOnline): ?>
        <tr>
          <td><?= h($name) ?></td>
          <td><span class="status-pill status-<?= $isOnline ? 'online' : 'offline' ?>"><span class="status-dot"></span> <?= $isOnline ? 'Online' : 'Offline' ?></span></td>
          <td><?= h(date('M j, Y g:i A')) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="form-hint mt-lg">This page runs live checks on every load (database ping, table availability, session status) rather than showing stale, hard-coded data.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// monitor.php, public status page, runs live checks and saves them to service_status
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// each check just sets true/false, try/catch so one broken check doesn't crash the page
$checks = [];
// check the database connection first, if that fails the other checks will fail too
try {
    $conn->query('SELECT 1');
    $checks['Database Connection'] = true;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Database Connection'] = false;
}
// check that the products table has at least one row, if not the catalogue is broken
try {
    $result = $conn->query('SELECT id FROM products LIMIT 1');
    $checks['Product Catalogue'] = $result->num_rows > 0;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Product Catalogue'] = false;
}
// check that the session is active, if not the cart service is broken
$checks['Shopping Cart'] = session_status() === PHP_SESSION_ACTIVE;
// check that the orders table exists, if not the checkout service is broken
try {
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    $checks['Checkout Service'] = $result->num_rows === 1;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['Checkout Service'] = false;
}
// check that the users table exists, if not the authentication service is broken
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $checks['User Authentication'] = $result->num_rows === 1;
} catch (Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    $checks['User Authentication'] = false;
}

$checks['Search / SEO Sitemap'] = file_exists(__DIR__ . '/sitemap.php');

// save these to the db so the admin dashboard shows the same thing
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
<!-- HTML for the system status page, including a table of services and their current status -->
<section class="section container">
  <h1>System Status</h1>
  <div class="alert <?= $allOnline ? 'alert-success' : 'alert-error' ?>">
    <?= $allOnline ? 'All systems operational.' : 'Some services are currently experiencing issues.' ?>
  </div>

  <div class="table-scroll">
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
  </div>

  <p class="form-hint mt-lg">This page runs live checks on every load (database ping, table availability, session status) rather than showing stale, hard-coded data.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

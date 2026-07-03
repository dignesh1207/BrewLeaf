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

/**
 * Each check returns true (online) or false (offline). Wrapped in
 * try/catch so a failure in one check can't take down the whole page.
 */
function run_check(callable $fn): bool
{
    try {
        return (bool) $fn();
    } catch (Throwable $e) {
        error_log('Health check failed: ' . $e->getMessage());
        return false;
    }
}

$checks = [
    'Database Connection' => run_check(fn() => $conn->ping()),
    'Product Catalogue'   => run_check(fn() => $conn->query('SELECT id FROM products LIMIT 1')->num_rows > 0),
    'Shopping Cart'       => run_check(function () {
        // Cart relies on PHP sessions -- confirm the session is active.
        return session_status() === PHP_SESSION_ACTIVE;
    }),
    'Checkout Service'    => run_check(fn() => $conn->query("SHOW TABLES LIKE 'orders'")->num_rows === 1),
    'User Authentication' => run_check(fn() => $conn->query("SHOW TABLES LIKE 'users'")->num_rows === 1),
    'Search / SEO Sitemap' => run_check(fn() => file_exists(__DIR__ . '/sitemap.php')),
];

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

  <p class="form-hint" style="margin-top:1.5rem;">This page runs live checks on every load (database ping, table availability, session status) rather than showing stale, hard-coded data.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

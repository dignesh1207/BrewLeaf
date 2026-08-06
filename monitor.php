<?php
// monitor.php
// Public system status page.
// Runs a few checks and updates the service status shown to users/admins.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Store each service check result here.
// true = working, false = problem.
$checks = [];


// Check database connection.
try {

    $conn->query('SELECT 1');

    $checks['Database Connection'] = true;

} catch (Throwable $e) {

    error_log('Health check failed: ' . $e->getMessage());

    $checks['Database Connection'] = false;
}


// Check that products are available.
// If no products exist, the catalogue may not work properly.
try {

    $result = $conn->query('SELECT id FROM products LIMIT 1');

    $checks['Product Catalogue'] = $result->num_rows > 0;

} catch (Throwable $e) {

    error_log('Health check failed: ' . $e->getMessage());

    $checks['Product Catalogue'] = false;
}


// Check that PHP sessions are working.
// The cart depends on sessions being active.
$checks['Shopping Cart'] = session_status() === PHP_SESSION_ACTIVE;


// Check that the orders table exists for checkout.
try {

    $result = $conn->query("SHOW TABLES LIKE 'orders'");

    $checks['Checkout Service'] = $result->num_rows === 1;

} catch (Throwable $e) {

    error_log('Health check failed: ' . $e->getMessage());

    $checks['Checkout Service'] = false;
}


// Check that the users table exists for login/register.
try {

    $result = $conn->query("SHOW TABLES LIKE 'users'");

    $checks['User Authentication'] = $result->num_rows === 1;

} catch (Throwable $e) {

    error_log('Health check failed: ' . $e->getMessage());

    $checks['User Authentication'] = false;
}


// Check if the sitemap file exists.
$checks['Search / SEO Sitemap'] = file_exists(__DIR__ . '/sitemap.php');


// Save the latest status of each service.
// The admin dashboard reads this information.
foreach ($checks as $name => $isOnline) {

    $status = $isOnline ? 'online' : 'offline';

    $stmt = $conn->prepare(
        'UPDATE service_status SET status = ? WHERE service_name = ?'
    );

    $stmt->bind_param('ss', $status, $name);

    $stmt->execute();

    $stmt->close();
}


// Used to show the overall status message.
$allOnline = !in_array(false, $checks, true);


$pageTitle = 'System Status | BrewLeaf';

$pageDescription = 'Live status of BrewLeaf website services: database, catalogue, cart, checkout, authentication, and search.';


require_once __DIR__ . '/includes/header.php';
?>

<!-- System status page -->

<section class="section container">

  <h1>System Status</h1>


  <div class="alert <?= $allOnline ? 'alert-success' : 'alert-error' ?>">

    <?= $allOnline
        ? 'All systems operational.'
        : 'Some services are currently experiencing issues.' ?>

  </div>


  <div class="table-scroll">

    <table>

      <thead>

        <tr>
          <th>Service</th>
          <th>Status</th>
          <th>Checked At</th>
        </tr>

      </thead>


      <tbody>

        <?php foreach ($checks as $name => $isOnline): ?>

          <tr>

            <td>
              <?= h($name) ?>
            </td>


            <td>

              <span class="status-pill status-<?= $isOnline ? 'online' : 'offline' ?>">

                <span class="status-dot"></span>

                <?= $isOnline ? 'Online' : 'Offline' ?>

              </span>

            </td>


            <td>
              <?= h(date('M j, Y g:i A')) ?>
            </td>


          </tr>


        <?php endforeach; ?>


      </tbody>


    </table>


  </div>


  <p class="form-hint mt-lg">
    This page runs live checks every time it loads instead of showing saved test data.
  </p>


</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
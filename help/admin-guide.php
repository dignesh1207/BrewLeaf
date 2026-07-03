<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Admin Guide | BrewLeaf Help';
$pageDescription = 'How BrewLeaf site administrators manage products, orders, users, and the site template.';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section container" style="max-width:760px;">
  <p><a href="index.php">&larr; Back to Help</a></p>
  <h1>Admin Guide</h1>
  <p>This guide is for site administrators (role = <code>admin</code>). Log in at <code>login.php</code>
  with an admin account to access <code>/admin/</code>.</p>

  <h2>Dashboard</h2>
  <p><code>admin/dashboard.php</code> shows product/order/customer counts, total revenue, a 14-day
  revenue chart, and a snapshot of the system status page.</p>

  <h2>Managing Products</h2>
  <p><code>admin/products.php</code> lists every product. Click <strong>Add New Product</strong> or
  <strong>Edit</strong> to open <code>admin/product-edit.php</code>, where you can set the name,
  category, origin, price, description, image path, and visibility -- and add or remove its
  Size/Grind option rows.</p>

  <h2>Managing Orders</h2>
  <p><code>admin/orders.php</code> lists every order placed. Change the status dropdown on any row
  to update it instantly; customers see the new status on their account page.</p>

  <h2>Managing Users</h2>
  <p><code>admin/users.php</code> lists every registered account. Click <strong>Disable</strong> to
  block a user from logging in (their password stays intact in case you re-enable them later).</p>

  <h2>Switching the Site Template</h2>
  <p><code>admin/theme.php</code> lets you switch the whole site between three templates --
  Regular, Harvest (Autumn), and Frost (Winter) -- with one click. The change applies to every
  visitor immediately.</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

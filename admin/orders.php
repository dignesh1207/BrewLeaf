<?php
// admin/orders.php
// This page lets the admin view all customer orders
// and update the current order status.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Only administrators are allowed to access this page
require_admin();

// Handle the status update when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {

    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'] ?? 'pending';

    // List of valid order statuses.
    // This prevents unexpected values from being saved.
    $valid = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if (in_array($status, $valid, true)) {

        $upd = $conn->prepare(
            'UPDATE orders SET status = ? WHERE id = ?'
        );

        $upd->bind_param('si', $status, $orderId);
        $upd->execute();
        $upd->close();
    }

    // Refresh the page after updating the status
    header('Location: orders.php?updated=1');
    exit;
}

// Get every order along with the customer's
// name and email address for display.
$orders = $conn->query(
    'SELECT o.id,
            o.status,
            o.total,
            o.created_at,
            u.full_name,
            u.email
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC'
);

$pageTitle = 'Manage Orders | BrewLeaf Admin';

require_once __DIR__ . '/../includes/header.php';

$adminActive = 'orders';

require_once __DIR__ . '/../includes/admin-nav.php';
?>

<!-- Manage Orders Page -->

<section class="section container">

  <h1>Manage Orders</h1>

  <!-- Show a confirmation message after updating an order -->
  <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">
      Order status updated.
    </div>
  <?php endif; ?>

  <?php if ($orders->num_rows === 0): ?>

    <!-- Display this if there are no customer orders yet -->
    <p>No orders placed yet.</p>

  <?php else: ?>

    <div class="table-scroll">

      <table>

        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>

          <?php while ($o = $orders->fetch_assoc()): ?>

            <tr>

              <!-- Order number -->
              <td>#<?= (int) $o['id'] ?></td>

              <!-- Customer name and email -->
              <td>
                <?= h($o['full_name']) ?>
                <br>
                <small class="text-muted-sm">
                  <?= h($o['email']) ?>
                </small>
              </td>

              <!-- Date the order was created -->
              <td><?= h(date('M j, Y', strtotime($o['created_at']))) ?></td>

              <!-- Total amount -->
              <td><?= money((float) $o['total']) ?></td>

              <!-- Dropdown for changing the order status -->
              <td>

                <form method="post" action="orders.php" class="table-actions">

                  <input
                    type="hidden"
                    name="order_id"
                    value="<?= (int) $o['id'] ?>">

                  <select name="status" class="auto-submit">

                    <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>

                      <option
                        value="<?= $s ?>"
                        <?= $o['status'] === $s ? 'selected' : '' ?>>

                        <?= ucfirst($s) ?>

                      </option>

                    <?php endforeach; ?>

                  </select>

                </form>

              </td>

            </tr>

          <?php endwhile; ?>

        </tbody>

      </table>

    </div>

  <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
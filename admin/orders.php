<?php
/**
 * admin/orders.php -- View all orders and update their status (used by
 * customers to "track order" on their profile page).
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'] ?? 'pending';
    $valid = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $valid, true)) {
        $upd = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $upd->bind_param('si', $status, $orderId);
        $upd->execute();
        $upd->close();
    }
    header('Location: orders.php?updated=1');
    exit;
}

$orders = $conn->query(
    'SELECT o.id, o.status, o.total, o.created_at, u.full_name, u.email
     FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC'
);

$pageTitle = 'Manage Orders | BrewLeaf Admin';
require_once __DIR__ . '/../includes/header.php';
$adminActive = 'orders';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<section class="section container">
  <h1>Manage Orders</h1>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Order status updated.</div><?php endif; ?>

  <?php if ($orders->num_rows === 0): ?>
    <p>No orders placed yet.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
          <tr>
            <td>#<?= (int) $o['id'] ?></td>
            <td><?= h($o['full_name']) ?><br><small style="color:var(--color-text-muted);"><?= h($o['email']) ?></small></td>
            <td><?= h(date('M j, Y', strtotime($o['created_at']))) ?></td>
            <td><?= money((float) $o['total']) ?></td>
            <td>
              <form method="post" action="orders.php" style="display:flex;gap:.4rem;">
                <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                <select name="status" onchange="this.form.submit()">
                  <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

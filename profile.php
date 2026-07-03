<?php
/**
 * profile.php -- Private area (requires login). Shows account details,
 * lets the user update their profile, and lists their order history /
 * tracking status.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = (int) $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid name and email.';
    } else {
        $upd = $conn->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
        $upd->bind_param('ssi', $fullName, $email, $userId);
        $upd->execute();
        $upd->close();
        $_SESSION['full_name'] = $fullName;
        $message = 'Profile updated.';
    }
}

$userStmt = $conn->prepare('SELECT username, email, full_name, created_at FROM users WHERE id = ?');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$ordersStmt = $conn->prepare('SELECT id, status, total, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$ordersStmt->bind_param('i', $userId);
$ordersStmt->execute();
$orders = $ordersStmt->get_result();

$pageTitle = 'My Account | BrewLeaf';
$pageDescription = 'Manage your BrewLeaf account and view your order history.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section container">
  <h1>My Account</h1>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

  <div class="product-detail">
    <div>
      <h2>Account Details</h2>
      <form method="post" action="profile.php" data-validate>
        <div class="form-row">
          <label>Username</label>
          <input type="text" value="<?= h($user['username']) ?>" disabled>
        </div>
        <div class="form-row">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" value="<?= h($user['full_name']) ?>" required>
        </div>
        <div class="form-row">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= h($user['email']) ?>" required>
        </div>
        <div class="form-row">
          <label>Member Since</label>
          <input type="text" value="<?= h(date('F j, Y', strtotime($user['created_at']))) ?>" disabled>
        </div>
        <button type="submit" name="update_profile" value="1" class="btn">Save Changes</button>
      </form>
    </div>

    <div>
      <h2>Order History &amp; Tracking</h2>
      <?php if ($orders->num_rows === 0): ?>
        <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a>.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Order #</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
          <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
              <tr>
                <td>#<?= (int) $o['id'] ?></td>
                <td><?= h(date('M j, Y', strtotime($o['created_at']))) ?></td>
                <td><span class="badge" style="background:var(--color-accent);text-transform:capitalize;"><?= h($o['status']) ?></span></td>
                <td><?= money((float) $o['total']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php $ordersStmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>

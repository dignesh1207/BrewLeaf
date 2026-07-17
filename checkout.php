<?php
/**
 * checkout.php -- Converts the logged-in user's cart into an order.
 * Requires login (guests are asked to log in / register first, keeping the
 * order history tied to a real account for the "track order and history"
 * feature on profile.php).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = false;

$stmt = $conn->prepare(
    'SELECT ci.id, ci.quantity, ci.unit_price, ci.selected_options, p.id AS product_id, p.name
     FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$cartRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$subtotal = 0.0;
foreach ($cartRows as $row) {
    $subtotal += $row['unit_price'] * $row['quantity'];
}
$shipping = $subtotal > 0 && $subtotal < 40 ? 5.99 : 0.0;
$total = $subtotal + $shipping;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    if ($address === '') {
        $error = 'Please enter a shipping address.';
    } elseif (empty($cartRows)) {
        $error = 'Your cart is empty.';
    } else {
        $conn->begin_transaction();
        try {
            $orderIns = $conn->prepare('INSERT INTO orders (user_id, status, shipping_address, total) VALUES (?, "pending", ?, ?)');
            $orderIns->bind_param('isd', $userId, $address, $total);
            $orderIns->execute();
            $orderId = $conn->insert_id;
            $orderIns->close();

            $itemIns = $conn->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, selected_options, quantity, unit_price)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($cartRows as $row) {
                $itemIns->bind_param(
                    'iissid',
                    $orderId,
                    $row['product_id'],
                    $row['name'],
                    $row['selected_options'],
                    $row['quantity'],
                    $row['unit_price']
                );
                $itemIns->execute();
            }
            $itemIns->close();

            $clear = $conn->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $clear->bind_param('i', $userId);
            $clear->execute();
            $clear->close();

            $conn->commit();
            $success = true;
            $completedOrderId = $orderId;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('Checkout failed: ' . $e->getMessage());
            $error = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout | BrewLeaf';
$pageDescription = 'Complete your BrewLeaf order.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section container">
  <h1>Checkout</h1>

  <?php if ($success): ?>
    <div class="alert alert-success">
      Order #<?= (int) $completedOrderId ?> placed successfully! You can track it any time from
      <a href="profile.php">your profile</a>.
    </div>
  <?php elseif (empty($cartRows)): ?>
    <p>Your cart is empty. <a href="products.php">Continue shopping</a>.</p>
  <?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

    <div class="product-detail">
      <div>
        <h2>Shipping Details</h2>
        <form method="post" action="checkout.php" data-validate>
          <div class="form-row">
            <label for="shipping_address">Shipping Address</label>
            <textarea id="shipping_address" name="shipping_address" rows="3" required placeholder="Street, City, Province, Postal Code"><?= h($_POST['shipping_address'] ?? ($_SESSION['full_name'] ?? '')) ?></textarea>
          </div>
          <div class="form-grid">
            <div class="form-row">
              <label for="card_number">Card Number (demo only)</label>
              <input type="text" id="card_number" name="card_number" placeholder="4242 4242 4242 4242" required>
            </div>
            <div class="form-row">
              <label for="card_exp">Expiry</label>
              <input type="text" id="card_exp" name="card_exp" placeholder="MM/YY" required>
            </div>
          </div>
          <p class="form-hint">This is a school project demo -- no real payment is processed.</p>
          <button type="submit" class="btn btn-accent">Place Order (<?= money($total) ?>)</button>
        </form>
      </div>

      <div>
        <h2>Order Summary</h2>
        <table>
          <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($cartRows as $row): ?>
              <tr>
                <td><?= h($row['name']) ?><br><small class="text-muted-sm"><?= h(format_selected_options($row['selected_options'])) ?></small></td>
                <td><?= (int) $row['quantity'] ?></td>
                <td><?= money($row['unit_price'] * $row['quantity']) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr><td colspan="2">Subtotal</td><td><?= money($subtotal) ?></td></tr>
            <tr><td colspan="2">Shipping</td><td><?= $shipping > 0 ? money($shipping) : 'Free' ?></td></tr>
            <tr><td colspan="2"><strong>Total</strong></td><td><strong><?= money($total) ?></strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

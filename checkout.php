<?php
// checkout.php
// Handles placing an order from the user's shopping cart.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// User must be logged in before they can check out.
require_login();

// Admins shouldn't be able to place customer orders.
if (is_admin()) {
    header('Location: ' . SITE_BASE_URL . '/admin/dashboard.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = false;

// Load everything currently in the user's cart.
$stmt = $conn->prepare(
    'SELECT ci.id, ci.quantity, ci.unit_price, ci.selected_options, p.id AS product_id, p.name
     FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?'
);

$stmt->bind_param('i', $userId);
$stmt->execute();
$cartRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Work out the cart subtotal.
$subtotal = 0.0;

foreach ($cartRows as $row) {
    $subtotal += $row['unit_price'] * $row['quantity'];
}

// Shipping is free for orders over $40.
$shipping = $subtotal > 0 && $subtotal < 40 ? 5.99 : 0.0;

$total = $subtotal + $shipping;

// Run the checkout after the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $address = trim($_POST['shipping_address'] ?? '');

    // Basic validation.
    if ($address === '') {
        $error = 'Please enter a shipping address.';
    } elseif (empty($cartRows)) {
        $error = 'Your cart is empty.';
    } else {

        // Save everything as one transaction.
        $conn->begin_transaction();

        try {

            // Create the order.
            $orderIns = $conn->prepare(
                'INSERT INTO orders (user_id, status, shipping_address, total)
                 VALUES (?, "pending", ?, ?)'
            );

            $orderIns->bind_param('isd', $userId, $address, $total);
            $orderIns->execute();

            // Remember the new order ID.
            $orderId = $conn->insert_id;

            $orderIns->close();

            // Copy every cart item into the order.
            $itemIns = $conn->prepare(
                'INSERT INTO order_items
                (order_id, product_id, product_name, selected_options, quantity, unit_price)
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

            // Empty the cart after the order is saved.
            $clear = $conn->prepare('DELETE FROM cart_items WHERE user_id = ?');

            $clear->bind_param('i', $userId);
            $clear->execute();
            $clear->close();

            // Finish the transaction.
            $conn->commit();

            $success = true;
            $completedOrderId = $orderId;

        } catch (Throwable $e) {

            // Undo everything if something failed.
            $conn->rollback();

            error_log('Checkout failed: ' . $e->getMessage());

            $error = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout | BrewLeaf';
$pageDescription = 'Complete your BrewLeaf order.';
$pageRobots = 'noindex, nofollow';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Checkout page -->
<section class="section container">

  <h1>Checkout</h1>

  <?php if ($success): ?>

    <!-- Success message after placing the order -->
    <div class="alert alert-success">
      Order #<?= (int) $completedOrderId ?> placed successfully! You can track it any time from
      <a href="profile.php">your profile</a>.
    </div>

  <?php elseif (empty($cartRows)): ?>

    <!-- Show this if the user somehow reaches checkout with no items -->
    <p>Your cart is empty. <a href="products.php">Continue shopping</a>.</p>

  <?php else: ?>

    <!-- Display any validation or processing errors -->
    <?php if ($error): ?>
      <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="product-detail">

      <!-- Shipping form -->
      <div>

        <h2>Shipping Details</h2>

        <form method="post" action="checkout.php" data-validate>

          <div class="form-row">
            <label for="shipping_address">Shipping Address</label>

            <textarea
                id="shipping_address"
                name="shipping_address"
                rows="3"
                required
                placeholder="Street, City, Province, Postal Code"><?= h($_POST['shipping_address'] ?? ($_SESSION['full_name'] ?? '')) ?></textarea>
          </div>

          <div class="form-grid">

            <!-- Demo payment fields (not connected to a payment gateway) -->
            <div class="form-row">
              <label for="card_number">Card Number (demo only)</label>

              <input
                  type="text"
                  id="card_number"
                  name="card_number"
                  placeholder="4242 4242 4242 4242"
                  pattern="[0-9\s]{13,19}"
                  maxlength="19"
                  required>
            </div>

            <div class="form-row">
              <label for="card_exp">Expiry</label>

              <input
                  type="text"
                  id="card_exp"
                  name="card_exp"
                  placeholder="MM/YY"
                  pattern="(0[1-9]|1[0-2])\/[0-9]{2}"
                  maxlength="5"
                  required>
            </div>

          </div>

          <p class="form-hint">
            This is a school project demo, so no real payment is processed.
          </p>

          <button type="submit" class="btn btn-accent">
            Place Order (<?= money($total) ?>)
          </button>

        </form>

      </div>

      <!-- Order summary -->
      <div>

        <h2>Order Summary</h2>

        <div class="table-scroll">

          <table>

            <thead>
              <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Total</th>
              </tr>
            </thead>

            <tbody>

              <?php foreach ($cartRows as $row): ?>

                <tr>

                  <td>
                    <?= h($row['name']) ?>
                    <br>

                    <!-- Display selected options such as size or roast -->
                    <small class="text-muted-sm">
                      <?= h(format_selected_options($row['selected_options'])) ?>
                    </small>
                  </td>

                  <td><?= (int) $row['quantity'] ?></td>

                  <td><?= money($row['unit_price'] * $row['quantity']) ?></td>

                </tr>

              <?php endforeach; ?>

              <!-- Cost breakdown -->
              <tr>
                <td colspan="2">Subtotal</td>
                <td><?= money($subtotal) ?></td>
              </tr>

              <tr>
                <td colspan="2">Shipping</td>
                <td><?= $shipping > 0 ? money($shipping) : 'Free' ?></td>
              </tr>

              <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td><strong><?= money($total) ?></strong></td>
              </tr>

            </tbody>

          </table>

        </div>

      </div>

    </div>

  <?php endif; ?>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
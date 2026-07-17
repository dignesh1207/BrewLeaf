<?php
/**
 * cart.php -- View and manage the shopping cart (guest or logged-in).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
$guestId = is_logged_in() ? null : get_guest_session_id();

if ($userId) {
    $stmt = $conn->prepare(
        'SELECT ci.id, ci.quantity, ci.unit_price, ci.selected_options, p.name, p.slug, p.image
         FROM cart_items ci JOIN products p ON p.id = ci.product_id
         WHERE ci.user_id = ? ORDER BY ci.created_at DESC'
    );
    $stmt->bind_param('i', $userId);
} else {
    $stmt = $conn->prepare(
        'SELECT ci.id, ci.quantity, ci.unit_price, ci.selected_options, p.name, p.slug, p.image
         FROM cart_items ci JOIN products p ON p.id = ci.product_id
         WHERE ci.session_id = ? ORDER BY ci.created_at DESC'
    );
    $stmt->bind_param('s', $guestId);
}
$stmt->execute();
$items = $stmt->get_result();

$subtotal = 0.0;
$rows = [];
while ($row = $items->fetch_assoc()) {
    $row['line_total'] = $row['unit_price'] * $row['quantity'];
    $subtotal += $row['line_total'];
    $rows[] = $row;
}
$shipping = $subtotal > 0 && $subtotal < 40 ? 5.99 : 0.0;
$total = $subtotal + $shipping;

$pageTitle = 'Your Cart | BrewLeaf';
$pageDescription = 'Review the items in your BrewLeaf shopping cart before checkout.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section container">
  <h1>Your Cart</h1>

  <?php if (empty($rows)): ?>
    <p>Your cart is empty. <a href="products.php">Continue shopping</a>.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Product</th><th>Options</th><th>Unit Price</th><th>Quantity</th><th>Line Total</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td class="table-cell-flex">
              <img src="<?= h($row['image']) ?>" alt="" class="table-thumb">
              <a href="product.php?slug=<?= h($row['slug']) ?>"><?= h($row['name']) ?></a>
            </td>
            <td><?= h(format_selected_options($row['selected_options'])) ?: '&mdash;' ?></td>
            <td><?= money((float) $row['unit_price']) ?></td>
            <td>
              <form method="post" action="cart_update.php" class="qty-stepper">
                <input type="hidden" name="item_id" value="<?= (int) $row['id'] ?>">
                <input type="hidden" name="action" value="update">
                <button type="button" class="btn btn-sm btn-outline qty-minus" aria-label="Decrease quantity">-</button>
                <input type="number" name="quantity" value="<?= (int) $row['quantity'] ?>" min="1" class="qty-input">
                <button type="button" class="btn btn-sm btn-outline qty-plus" aria-label="Increase quantity">+</button>
              </form>
            </td>
            <td><?= money((float) $row['line_total']) ?></td>
            <td>
              <form method="post" action="cart_update.php">
                <input type="hidden" name="item_id" value="<?= (int) $row['id'] ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="cart-summary">
      <table>
        <tbody>
          <tr><td>Subtotal</td><td class="text-right"><?= money($subtotal) ?></td></tr>
          <tr><td>Shipping</td><td class="text-right"><?= $shipping > 0 ? money($shipping) : 'Free' ?></td></tr>
          <tr><td><strong>Total</strong></td><td class="text-right"><strong><?= money($total) ?></strong></td></tr>
        </tbody>
      </table>
      <a href="checkout.php" class="btn btn-accent btn-block mt-md">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</section>

<?php $stmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>

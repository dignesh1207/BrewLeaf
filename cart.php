<?php
// cart.php -- shows the cart, works for guests too not just logged in users
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// admins shouldn't be shopping, kick them to their dashboard instead.
// this checks the session role on the server so someone can't just fake it in the browser
if (is_admin()) {
    header('Location: ' . SITE_BASE_URL . '/admin/dashboard.php');
    exit;
}

// if logged in use user_id, otherwise fall back to the guest session id
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
// free shipping over $40, and empty cart obviously has no shipping cost
$shipping = $subtotal > 0 && $subtotal < 40 ? 5.99 : 0.0;
$total = $subtotal + $shipping;

$pageTitle = 'Your Cart | BrewLeaf';
$pageDescription = 'Review the items in your BrewLeaf shopping cart before checkout.';
$pageRobots = 'noindex, nofollow';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HTML for the cart page, including a table of items, quantity controls, and a summary of totals -->
<section class="section container">
  <h1>Your Cart</h1>

  <div id="cartError" class="alert alert-error" hidden></div>

  <div id="cartEmptyState" class="cart-empty-state" <?= empty($rows) ? '' : 'hidden' ?>>
    <p>Your cart is empty.</p>
    <a href="products.php" class="btn btn-accent">Continue Shopping</a>
  </div>

  <div id="cartContent" <?= empty($rows) ? 'hidden' : '' ?>>
    <div class="table-scroll">
      <table class="cart-table">
        <colgroup>
          <col class="cart-col-product"><col class="cart-col-options"><col class="cart-col-price">
          <col class="cart-col-qty"><col class="cart-col-total"><col class="cart-col-actions">
        </colgroup>
        <thead>
          <tr><th scope="col">Product</th><th scope="col">Options</th><th scope="col">Unit Price</th><th scope="col">Quantity</th><th scope="col">Line Total</th><th scope="col">Actions</th></tr>
        </thead>
        <tbody id="cartItemsBody">
          <?php foreach ($rows as $row): ?>
            <tr data-row-id="<?= (int) $row['id'] ?>">
              <td class="table-cell-flex">
                <img src="<?= h($row['image']) ?>" alt="<?= h($row['name']) ?>" class="cart-thumb">
                <a href="product.php?slug=<?= h($row['slug']) ?>"><?= h($row['name']) ?></a>
              </td>
              <td><?= h(format_selected_options($row['selected_options'])) ?: '&mdash;' ?></td>
              <td><?= money((float) $row['unit_price']) ?></td>
              <td>
                <form method="post" action="cart_update.php" class="qty-stepper" data-item-id="<?= (int) $row['id'] ?>">
                  <input type="hidden" name="item_id" value="<?= (int) $row['id'] ?>">
                  <input type="hidden" name="action" value="update">
                  <button type="button" class="btn btn-sm btn-outline qty-minus" aria-label="Decrease quantity">&minus;</button>
                  <input type="number" name="quantity" value="<?= (int) $row['quantity'] ?>" min="1" class="qty-input" aria-label="Quantity">
                  <button type="button" class="btn btn-sm btn-outline qty-plus" aria-label="Increase quantity">+</button>
                  <span class="spinner" hidden aria-hidden="true"></span>
                </form>
              </td>
              <td class="cart-line-total"><?= money((float) $row['line_total']) ?></td>
              <td class="actions-td">
                <form method="post" action="cart_update.php" class="remove-item-form" data-confirm="Remove &quot;<?= h($row['name']) ?>&quot; from your cart?">
                  <input type="hidden" name="item_id" value="<?= (int) $row['id'] ?>">
                  <input type="hidden" name="action" value="remove">
                  <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="cart-summary">
      <div class="summary-row">
        <span>Subtotal</span>
        <span id="cartSubtotal"><?= money($subtotal) ?></span>
      </div>
      <div class="summary-row">
        <span>Shipping</span>
        <span id="cartShipping"><?= $shipping > 0 ? money($shipping) : 'Free' ?></span>
      </div>
      <div class="summary-row summary-row-total">
        <strong>Total</strong>
        <strong id="cartTotal"><?= money($total) ?></strong>
      </div>
      <a href="checkout.php" class="btn btn-accent btn-block mt-md">Proceed to Checkout</a>
    </div>
  </div>
</section>

<?php $stmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>

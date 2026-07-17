<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Ordering & Checkout | BrewLeaf Help';
$pageDescription = 'How product options, the shopping cart, and checkout work on BrewLeaf.';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section container page-narrow-lg">
  <p><a href="index.php">&larr; Back to Help</a></p>
  <h1>Ordering &amp; Checkout</h1>

  <h2>Choosing Options</h2>
  <p>Every product has two option groups -- for example <strong>Size</strong> (250g/500g/1kg) and
  <strong>Grind</strong> (Whole Bean/Ground/Espresso). Click a pill to select it; the displayed
  price updates instantly to include any size upgrade.</p>

  <h2>Reviewing Your Cart</h2>
  <p>Open <strong>Cart</strong> from the header at any time to see every item, its chosen options,
  quantity, and line total. Use the +/- buttons to change quantity, or click <strong>Remove</strong>
  to delete a line. Orders under $40 include a flat $5.99 shipping fee; $40+ ships free.</p>

  <h2>Checking Out</h2>
  <p>Click <strong>Proceed to Checkout</strong>. If you aren't logged in yet, you'll be asked to log
  in first so your order can be saved to your account. Enter a shipping address, then click
  <strong>Place Order</strong>. (This is a school-project demo -- no real payment is charged.)</p>

  <h2>Tracking Your Order</h2>
  <p>After checkout, visit <a href="managing-account.php">your account</a> to see order status,
  which an admin updates as it moves through Pending &rarr; Processing &rarr; Shipped &rarr; Delivered.</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

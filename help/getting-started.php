<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Getting Started | BrewLeaf Help';
$pageDescription = 'How to create a BrewLeaf account and place your first order.';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section container" style="max-width:760px;">
  <p><a href="index.php">&larr; Back to Help</a></p>
  <h1>Getting Started</h1>

  <h2>1. Browse the Catalogue</h2>
  <p>Click <strong>Shop</strong> in the main menu, or jump straight to <strong>Coffee</strong> or <strong>Tea</strong>.
  Use the search box and sort menu on the Shop page to narrow results by keyword, price, or rating.</p>

  <h2>2. Create an Account</h2>
  <p>Click <strong>Sign up</strong> in the top right, fill in your name, username, email and a password of
  at least 8 characters, then submit. You'll be logged in automatically.</p>

  <h2>3. View a Product</h2>
  <p>Click any product card to see its full description, customer reviews, and available options
  (like size and grind/style). The price updates live as you change options.</p>

  <h2>4. Add to Cart</h2>
  <p>Choose your options and quantity, then click <strong>Add to Cart</strong>. A confirmation
  message appears and the cart icon in the header updates with your item count.</p>

  <p>Next: <a href="ordering-and-checkout.php">Ordering &amp; Checkout &rarr;</a></p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * about.php -- Static page. Contains the required business-case description
 * (rubric item 1) plus company story content.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us | BrewLeaf Artisan Coffee & Tea Co.';
$pageDescription = 'Learn about BrewLeaf Artisan Coffee & Tea Co., an online catalogue of ethically sourced coffee beans and loose-leaf teas.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container"><h1>About BrewLeaf</h1></div>
</section>

<section class="section container" style="max-width:800px;">
  <h2>Our Business</h2>
  <p>
    BrewLeaf Artisan Coffee &amp; Tea Co. is an online catalogue and storefront for small-batch,
    ethically sourced coffee beans and loose-leaf teas. We work directly with growers and
    cooperatives across ten countries -- including Ethiopia, Colombia, Kenya, China, India, and
    Japan -- to bring customers a rotating catalogue of twenty single-origin coffees and specialty
    teas. Each product can be customized by size (from 50g sample bags up to 1kg) and by
    preparation style (whole bean, ground, loose leaf, or tea bags), so customers can order exactly
    what suits their brewing routine. Shoppers can browse and search the catalogue, read and leave
    star ratings and reviews, add items to a cart with live price calculation, check out, and track
    their order status from a personal account -- while our team manages the catalogue, order
    fulfillment, and customer accounts from an administrative dashboard.
  </p>

  <img src="assets/images/about-roastery.jpg" alt="BrewLeaf roastery" style="border-radius:10px;margin:1.5rem 0;box-shadow:var(--shadow-md);">

  <h2>Our Story</h2>
  <p>BrewLeaf started as a single roasting drum in a garage and grew into a small team obsessed with
  freshness: every bag is roasted or blended within a week of shipping, never sitting in a warehouse
  for months on end.</p>

  <h2>Our Values</h2>
  <div class="feature-grid">
    <div class="feature-box"><span class="icon">&#129309;</span><h3>Direct Trade</h3><p>We pay growers fair, transparent prices well above commodity rates.</p></div>
    <div class="feature-box"><span class="icon">&#9749;</span><h3>Small Batch</h3><p>Roasted and blended weekly in batches under 50kg for peak freshness.</p></div>
    <div class="feature-box"><span class="icon">&#9851;</span><h3>Sustainable</h3><p>Compostable packaging and carbon-neutral shipping on every order.</p></div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

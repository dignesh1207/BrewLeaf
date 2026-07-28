<?php
// about page - just the company story and business blurb
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

<section class="section container page-narrow-lg">
  <h2 class="text-center">Our Business</h2>
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

  <img src="assets/images/about-roastery.jpg" alt="BrewLeaf roastery" class="content-image">

  <h2 class="text-center">Our Story</h2>
  <p>BrewLeaf started as a single roasting drum in a garage and grew into a small team obsessed with
  freshness: every bag is roasted or blended within a week of shipping, never sitting in a warehouse
  for months on end.</p>

  <h2 class="text-center">Our Values</h2>
  <div class="feature-grid">

    <div class="feature-box"><span class="icon">&#127760;</span><h3>Ethically Sourced</h3><p>We work directly with growers and cooperatives to ensure fair pay and sustainable practices.</p></div>
    <div class="feature-box"><span class="icon">&#129309;</span><h3>Direct Trade</h3><p>We pay growers fair, transparent prices well above commodity rates.</p></div>
    <div class="feature-box"><span class="icon">&#9749;</span><h3>Small Batch</h3><p>Roasted and blended weekly in batches under 50kg for peak freshness.</p></div>
    <div class="feature-box"><span class="icon">&#9851;</span><h3>Sustainable</h3><p>Compostable packaging and carbon-neutral shipping on every order.</p></div>
  
  </div>
  <br>
  <br>

  <h2 class="text-center">See BrewLeaf in Action</h2>
  <div class="video-gallery">
    
    <!-- this one is BrewLeaf footage, shows the roasting process -->
    <div class="video-card">
      <video controls controlsList="nodownload" preload="metadata" poster="assets/images/video-roastery-tour.jpg">
        <source src="assets/videos/roastery-tour.mp4" type="video/mp4">
        Your browser does not support embedded video.
      </video>
      <div class="video-card-body">
        <h3>Inside the Roastery</h3>
        <p>A close look at the copper drum roaster where our beans get their signature depth of flavor.</p>
        <p class="text-muted-sm">Footage: "Tonefoto grapher" via <a href="https://www.vecteezy.com/video/5442704-coffee-bean-is-roasting-in-roaster-machine-smoking-from-coffee">Vecteezy</a></p>
      </div>
    </div>

    <!-- this one is a stock video, not BrewLeaf footage, but it shows the roasting process well -->
    <div class="video-card">
      <video controls controlsList="nodownload" preload="metadata" poster="assets/images/video-roasting-process.jpg">
        <source src="assets/videos/roasting-process.mp4" type="video/mp4">
        Your browser does not support embedded video.
      </video>
      <div class="video-card-body">
        <h3>Watch How We Roast</h3>
        <p>From coffee cherry to roasted bean: a quick look at our small-batch roasting process.</p>
        <p class="text-muted-sm">Made with Canva AI</p>
      </div>
    </div>

    <!-- this one is a stock video, not BrewLeaf footage, but it shows the brewing process well -->
    <div class="video-card">
      <video controls controlsList="nodownload" preload="metadata" poster="assets/images/video-brewing-guide.jpg">
        <source src="assets/videos/brewing-guide.mp4" type="video/mp4">
        Your browser does not support embedded video.
      </video>
      <div class="video-card-body">
        <h3>Brewing the Perfect Cup</h3>
        <p>A simple pour-over guide for getting the most out of your beans at home.</p>
        <p class="text-muted-sm">Footage via <a href="https://pixabay.com/videos/coffee-cappuccino-caffeine-209424/">Pixabay</a></p>
      </div>
    </div>

  </div>
 
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

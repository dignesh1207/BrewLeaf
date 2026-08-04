<?php
// footer that goes on the bottom of every page, plus the closing html tags
?>

<!-- HTML for the footer, including site links, address, and theme switcher -->
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-col">
      <h3>BrewLeaf</h3>
      <p>Ethically sourced artisan coffee &amp; tea, roasted and blended in small batches.</p>
    </div>
    <div class="footer-col">
      <h3>Shop</h3>
      <ul>
        <li><a href="<?= h(SITE_BASE_URL) ?>/products.php?category=coffee">Coffee</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/products.php?category=tea">Tea</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/cart.php">Your Cart</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Support</h3>
      <ul>
        <li><a href="<?= h(SITE_BASE_URL) ?>/help/index.html">Help &amp; Wiki</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/contact.php">Contact Us</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/monitor.php">System Status</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/project-docs.html">Project Docs</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Find Our Roastery</h3>

      <!-- address is commented out because the map is more useful, but leaving it here in case we want to show it later -->
      <!-- <div class="footer-address">
        <span class="footer-address-icon" aria-hidden="true">&#128205;</span>
        <address>
          <?= h(BUSINESS_ADDRESS_LINE1) ?><br>
          <?= h(BUSINESS_ADDRESS_LINE2) ?>
        </address>
      </div> -->
      <!-- no api key needed for this basic embed, visitor can pan/zoom right from the footer -->
      <div class="footer-map">
        <iframe
          src="https://www.google.com/maps?q=<?= urlencode(BUSINESS_ADDRESS_LINE1 . ', ' . BUSINESS_ADDRESS_LINE2) ?>&output=embed"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Map showing the BrewLeaf roastery at <?= h(BUSINESS_ADDRESS_LINE1) ?>, <?= h(BUSINESS_ADDRESS_LINE2) ?>"
        ></iframe>
      </div>
    </div>
    <div class="footer-col">
      <h3>Site Look</h3>
      <p>Anyone can switch the look for the whole site right here.</p>
      <?php
      // $activeTheme comes from header.php which gets included before this file
      $themeDots = [
          'white'   => ['label' => 'Clean White',      'color' => '#ffffff'],
          'regular' => ['label' => 'Regular Roastery',  'color' => '#6f4e37'],
          'autumn'  => ['label' => 'Harvest (Autumn)',  'color' => '#c1521f'],
          'winter'  => ['label' => 'Frost (Winter)',    'color' => '#2b5a72'],
      ];
      ?>
      <!-- anyone can use this, no login needed - posts to set-theme.php, not admin/theme.php -->
      <form method="post" action="<?= h(SITE_BASE_URL) ?>/set-theme.php" class="theme-switcher">
        <input type="hidden" name="redirect_to" value="<?= h($_SERVER['REQUEST_URI']) ?>">
        <?php foreach ($themeDots as $key => $dot): ?>
          <button
            type="submit"
            name="theme"
            value="<?= $key ?>"
            class="theme-dot <?= $activeTheme === $key ? 'active' : '' ?>"
            style="background:<?= $dot['color'] ?>;"
            title="<?= h($dot['label']) ?>"
            aria-label="Switch site to the <?= h($dot['label']) ?> theme"
          ></button>
        <?php endforeach; ?>
      </form>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>&copy; <?= date('Y') ?> BrewLeaf Artisan Coffee &amp; Tea Co. All rights reserved.</p>
  </div>
</footer>

<!-- same script tags on every page, each one just does nothing if its element isn't on the page -->
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/nav.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/product-options.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/cart.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/form-validation.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/tabs.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/hero-video.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/auto-submit.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/confirm-submit.js"></script>
</body>
</html>

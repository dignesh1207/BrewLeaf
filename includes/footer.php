<?php
/** Shared site footer + closing tags. Included at the bottom of every page. */
?>
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
        <li><a href="<?= h(SITE_BASE_URL) ?>/help/index.php">Help &amp; Wiki</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/contact.php">Contact Us</a></li>
        <li><a href="<?= h(SITE_BASE_URL) ?>/monitor.php">System Status</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Find Our Roastery</h3>
      <!-- OpenStreetMap embed, no API key needed -->
      <div class="map-embed">
        <iframe title="BrewLeaf Roastery Location" src="https://www.openstreetmap.org/export/embed.html?bbox=-83.0670%2C42.2980%2C-83.0430%2C42.3160&layer=mapnik" loading="lazy"></iframe>
      </div>
    </div>
    <div class="footer-col">
      <h3>Site Look</h3>
      <p>Anyone can switch the look for the whole site right here.</p>
      <?php
      // $activeTheme is set in includes/header.php, included earlier
      $themeDots = [
          'white'   => ['label' => 'Clean White',      'color' => '#ffffff'],
          'regular' => ['label' => 'Regular Roastery',  'color' => '#6f4e37'],
          'autumn'  => ['label' => 'Harvest (Autumn)',  'color' => '#c1521f'],
          'winter'  => ['label' => 'Frost (Winter)',    'color' => '#2b5a72'],
      ];
      ?>
      <!-- Public theme switcher: no login required (intentional) -- posts
           to set-theme.php, unlike the admin-only admin/theme.php. -->
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

<!-- Every page loads this same JS bundle; each file no-ops if its element isn't present. -->
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/nav.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/product-options.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/cart.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/form-validation.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/tabs.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/hero-video.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/catalogue-chart.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/revenue-chart.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/auto-submit.js"></script>
<script src="<?= h(SITE_BASE_URL) ?>/assets/js/confirm-submit.js"></script>
</body>
</html>

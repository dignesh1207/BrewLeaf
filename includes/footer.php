<?php
/**
 * includes/footer.php
 * ---------------------------------------------------------------------------
 * Shared site footer + closing tags. Included at the bottom of every page.
 * ---------------------------------------------------------------------------
 */
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
      <div class="map-embed">
        <iframe title="BrewLeaf Roastery Location" src="https://www.openstreetmap.org/export/embed.html?bbox=-83.0670%2C42.2980%2C-83.0430%2C42.3160&layer=mapnik" loading="lazy"></iframe>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>&copy; <?= date('Y') ?> BrewLeaf Artisan Coffee &amp; Tea Co. All rights reserved.</p>
  </div>
</footer>

<script src="<?= h(SITE_BASE_URL) ?>/assets/js/main.js"></script>
</body>
</html>

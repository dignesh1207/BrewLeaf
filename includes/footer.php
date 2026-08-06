<?php
// footer.php
// This file is included at the bottom of every page.
// It contains the footer content and closes the HTML document.
?>

</main>

<!-- Website footer -->
<footer class="site-footer">

  <div class="container footer-inner">

    <!-- Company information -->
    <div class="footer-col">
      <h3>BrewLeaf</h3>
      <p>
        Ethically sourced artisan coffee &amp; tea, roasted and blended in small batches.
      </p>
    </div>


    <!-- Shopping links -->
    <div class="footer-col">
      <h3>Shop</h3>

      <ul>
        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/products.php?category=coffee">
            Coffee
          </a>
        </li>

        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/products.php?category=tea">
            Tea
          </a>
        </li>

        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/cart.php">
            Your Cart
          </a>
        </li>
      </ul>
    </div>


    <!-- Help and support links -->
    <div class="footer-col">
      <h3>Support</h3>

      <ul>
        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/help/index.html">
            Help &amp; Wiki
          </a>
        </li>

        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/contact.php">
            Contact Us
          </a>
        </li>

        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/monitor.php">
            System Status
          </a>
        </li>

        <li>
          <a href="<?= h(SITE_BASE_URL) ?>/project-docs.html">
            Project Docs
          </a>
        </li>
      </ul>
    </div>


    <!-- Location map -->
    <div class="footer-col">

      <h3>Find Our Roastery</h3>


      <?php
      // Address display is currently disabled.
      // The map below gives users the location directly.
      ?>

      <div class="footer-map">

        <iframe
          src="https://www.google.com/maps?q=<?= urlencode(BUSINESS_ADDRESS_LINE1 . ', ' . BUSINESS_ADDRESS_LINE2) ?>&output=embed"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Map showing the BrewLeaf roastery at <?= h(BUSINESS_ADDRESS_LINE1) ?>, <?= h(BUSINESS_ADDRESS_LINE2) ?>"
        ></iframe>

      </div>

    </div>


    <!-- Theme selector -->
    <div class="footer-col">

      <h3>Site Look</h3>

      <p>
        Anyone can switch the look for the whole site right here.
      </p>


      <?php
      // Theme information used for the theme buttons.
      // $activeTheme is loaded from header.php.
      $themeDots = [
          'white' => [
              'label' => 'Clean White',
              'color' => '#ffffff'
          ],

          'regular' => [
              'label' => 'Regular Roastery',
              'color' => '#6f4e37'
          ],

          'autumn' => [
              'label' => 'Harvest (Autumn)',
              'color' => '#c1521f'
          ],

          'winter' => [
              'label' => 'Frost (Winter)',
              'color' => '#2b5a72'
          ],
      ];
      ?>


      <!-- Sends the selected theme to set-theme.php -->
      <form 
        method="post" 
        action="<?= h(SITE_BASE_URL) ?>/set-theme.php" 
        class="theme-switcher"
      >

        <input 
          type="hidden" 
          name="redirect_to" 
          value="<?= h($_SERVER['REQUEST_URI']) ?>"
        >


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


  <!-- Copyright section -->
  <div class="container footer-bottom">

    <p>
      &copy; <?= date('Y') ?> BrewLeaf Artisan Coffee &amp; Tea Co. 
      All rights reserved.
    </p>

  </div>


</footer>


<!-- JavaScript files used throughout the website -->
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
<?php
/**
 * help/index.php -- Wiki landing page. Every page on the site links to
 * "Help" in the main nav (see includes/header.php), and individual pages
 * can deep-link into a specific article for context-sensitive help.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Help & Wiki | BrewLeaf';
$pageDescription = 'Step-by-step guides for shopping, checkout, account management, and (for admins) managing the BrewLeaf site.';
require_once __DIR__ . '/../includes/header.php';

$articles = [
    ['getting-started.php', 'Getting Started', 'Create an account, browse the catalogue, and place your first order.'],
    ['ordering-and-checkout.php', 'Ordering & Checkout', 'How product options, cart, and checkout work.'],
    ['managing-account.php', 'Managing Your Account', 'Update your profile, view order history, and track orders.'],
    ['admin-guide.php', 'Admin Guide', 'For site administrators: manage products, orders, users, and templates.'],
    ['updating-content.php', 'Updating Site Content (No Coding Required)', 'How a non-programmer can add products, images, and video.'],
];
?>

<section class="hero">
  <div class="container"><h1>Help &amp; Wiki</h1><p>Guides for shoppers and site administrators.</p></div>
</section>

<section class="section container">
  <div class="feature-grid">
    <?php foreach ($articles as [$file, $title, $desc]): ?>
      <a class="feature-box" href="<?= h($file) ?>" style="color:inherit;">
        <h3><?= h($title) ?></h3>
        <p><?= h($desc) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

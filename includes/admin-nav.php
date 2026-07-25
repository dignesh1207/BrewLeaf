<?php
/**
 * Secondary admin nav. Include AFTER includes/header.php.
 * Expects $adminActive (dashboard|products|users|theme|orders) to highlight the current section.
 */

$adminActive = $adminActive ?? '';

// section key => label/url; add a new admin page by adding one entry here
$adminLinks = [
    'dashboard' => ['label' => 'Dashboard',     'url' => SITE_BASE_URL . '/admin/dashboard.php'],
    'products'  => ['label' => 'Products',      'url' => SITE_BASE_URL . '/admin/products.php'],
    'orders'    => ['label' => 'Orders',        'url' => SITE_BASE_URL . '/admin/orders.php'],
    'users'     => ['label' => 'Users',         'url' => SITE_BASE_URL . '/admin/users.php'],
    'theme'     => ['label' => 'Site Template', 'url' => SITE_BASE_URL . '/admin/theme.php'],
];

?>


<div class="admin-bar">
  <nav class="container" aria-label="Admin section">
    <ul>
      <?php foreach ($adminLinks as $key => $link): ?>
        <li>
          <!-- "active" class when $adminActive matches this section -->
          <a href="<?= h($link['url']) ?>" class="<?= $adminActive === $key ? 'active' : '' ?>">
            <?= h($link['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li class="admin-bar-back"><a href="<?= h(SITE_BASE_URL) ?>/index.php">&larr; Back to Site</a></li>
    </ul>
  </nav>
</div>

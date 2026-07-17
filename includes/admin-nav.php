<?php
/**
 * includes/admin-nav.php -- Secondary navigation shown at the top of every
 * admin page. Include AFTER includes/header.php. Expects an
 * $adminActive string (one of: dashboard, products, users, theme, orders)
 * to highlight the current section.
 */
$adminActive = $adminActive ?? '';
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
          <a href="<?= h($link['url']) ?>" class="<?= $adminActive === $key ? 'active' : '' ?>">
            <?= h($link['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li class="admin-bar-back"><a href="<?= h(SITE_BASE_URL) ?>/index.php">&larr; Back to Site</a></li>
    </ul>
  </nav>
</div>

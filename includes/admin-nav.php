<?php
// this is the little nav bar for admin pages, goes right after header.php
// set $adminActive before including this (dashboard/products/orders/users/theme) so it knows what to highlight

$adminActive = $adminActive ?? '';

// key => label/url, just add another line here if we make a new admin page
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
          <!-- highlights the current section -->
          <a href="<?= h($link['url']) ?>" class="<?= $adminActive === $key ? 'active' : '' ?>">
            <?= h($link['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li class="admin-bar-back"><a href="<?= h(SITE_BASE_URL) ?>/index.php">&larr; Back to Site</a></li>
    </ul>
  </nav>
</div>

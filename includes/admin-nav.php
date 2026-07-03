<?php
/**
 * includes/admin-nav.php -- Secondary navigation shown at the top of every
 * admin page. Include AFTER includes/header.php. Expects an
 * $adminActive string (one of: dashboard, products, users, theme, orders)
 * to highlight the current section.
 */
$adminActive = $adminActive ?? '';
$adminLinks = [
    'dashboard' => ['Dashboard', SITE_BASE_URL . '/admin/dashboard.php'],
    'products'  => ['Products', SITE_BASE_URL . '/admin/products.php'],
    'orders'    => ['Orders', SITE_BASE_URL . '/admin/orders.php'],
    'users'     => ['Users', SITE_BASE_URL . '/admin/users.php'],
    'theme'     => ['Site Template', SITE_BASE_URL . '/admin/theme.php'],
];
?>
<div style="background:var(--color-primary-dark);">
  <nav class="container" aria-label="Admin section">
    <ul style="display:flex;gap:1.2rem;padding:.8rem 0;flex-wrap:wrap;">
      <?php foreach ($adminLinks as $key => [$label, $url]): ?>
        <li>
          <a href="<?= h($url) ?>" style="color:<?= $adminActive === $key ? '#fff' : 'rgba(255,255,255,.65)' ?>;font-weight:<?= $adminActive === $key ? '700' : '500' ?>;">
            <?= h($label) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li style="margin-left:auto;"><a href="<?= h(SITE_BASE_URL) ?>/index.php" style="color:rgba(255,255,255,.65);">&larr; Back to Site</a></li>
    </ul>
  </nav>
</div>

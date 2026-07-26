<?php
// this is the top of every page - head tags, meta stuff, all the css links, and the nav bar
// pages need to set $pageTitle and $pageDescription before including this one ($pageKeywords is optional)
// also needs config/db.php and auth.php to already be included before this

// fall back to these if the page didn't set its own
$pageTitle       = $pageTitle ?? 'BrewLeaf Artisan Coffee & Tea Co.';
$pageDescription = $pageDescription ?? 'BrewLeaf is an online artisan coffee and tea shop offering ethically sourced coffee beans and loose leaf teas from around the world.';
$pageKeywords    = $pageKeywords ?? 'coffee, tea, artisan coffee, loose leaf tea, online coffee shop, specialty tea';
// which theme css to load down below, see get_active_theme() in functions.php
$activeTheme     = get_active_theme($conn);

// need these to know which nav link should get the "active" class
$navScript   = basename($_SERVER['SCRIPT_NAME']);
$navCategory = $_GET['category'] ?? '';
$navIsHelp   = str_contains($_SERVER['SCRIPT_NAME'], '/help/');

// counting cart items - logged in users use their user_id, guests use the session id instead.
// admins don't have a cart since they don't shop, so just skip this whole query for them
// (cart.php also blocks admins from the cart page itself, this is just for the nav badge)
$cartCount = 0;
if (!is_admin()) {
    if (is_logged_in()) {
        $cs = $conn->prepare('SELECT COALESCE(SUM(quantity),0) AS n FROM cart_items WHERE user_id = ?');
        $cs->bind_param('i', $_SESSION['user_id']);
    } else {
        $gid = get_guest_session_id();
        $cs = $conn->prepare('SELECT COALESCE(SUM(quantity),0) AS n FROM cart_items WHERE session_id = ?');
        $cs->bind_param('s', $gid);
    }
    $cs->execute();
    $cartCount = (int) ($cs->get_result()->fetch_assoc()['n'] ?? 0);
    $cs->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- seo meta tags -->
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($pageDescription) ?>">
<meta name="keywords" content="<?= h($pageKeywords) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="BrewLeaf Artisan Coffee & Tea Co.">
<link rel="canonical" href="<?= h(SITE_BASE_URL . $_SERVER['REQUEST_URI']) ?>">
<!-- these are for when a link gets shared on social media -->
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<meta property="og:type" content="website">
<!-- favicon -->
<link rel="icon" type="image/png" href="<?= h(SITE_BASE_URL) ?>/assets/images/favicon.png">
<!--
  the order of these css files actually matters, learned this the hard way.
  variables.css sets up the css variables first, then the theme-*.css file
  overrides some of them for whichever theme is active, then base.css resets
  everything, then all the component css files, and utilities.css has to go
  last so it can override stuff from the files above it
-->
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/variables.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/theme-<?= h($activeTheme) ?>.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/base.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/buttons.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/forms.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/header.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/footer.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/hero.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/sections.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/product-card.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/tabs.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/tables.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?= h(SITE_BASE_URL) ?>/assets/css/utilities.css">
<!-- preconnect so the google fonts load a little faster -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="theme-<?= h($activeTheme) ?>">

<a class="skip-link" href="#main-content">Skip to main content</a>

<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?= h(SITE_BASE_URL) ?>/index.php">BrewLeaf</a>

    <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="primaryNav" aria-label="Toggle navigation menu">
      <span></span><span></span><span></span>
    </button>

    <?php if (is_admin()): ?>
      <!-- admins get their own nav, no shop/cart links since admins manage products,
           they don't buy stuff as a customer (cart.php redirects them away too, just in case) -->
      <nav class="primary-nav" id="primaryNav" aria-label="Primary">
        <ul>
          <li><a href="<?= h(SITE_BASE_URL) ?>/index.php" class="<?= $navScript === 'index.php' ? 'active' : '' ?>">Storefront</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/admin/dashboard.php" class="<?= $navScript === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/admin/products.php" class="<?= $navScript === 'products.php' && str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? 'active' : '' ?>">Products</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/admin/orders.php" class="<?= $navScript === 'orders.php' ? 'active' : '' ?>">Orders</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/admin/users.php" class="<?= $navScript === 'users.php' ? 'active' : '' ?>">Customers</a></li>
        </ul>
        <ul class="nav-utility">
          <!-- just one link here instead of a greeting + separate admin link, seemed redundant -->
          <li><a href="<?= h(SITE_BASE_URL) ?>/profile.php"><?= h($_SESSION['full_name']) ?></a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/logout.php">Log out</a></li>
        </ul>
      </nav>
    <?php else: ?>
      <nav class="primary-nav" id="primaryNav" aria-label="Primary">
        <ul>
          <!-- active class stuff is calculated up above -->
          <li><a href="<?= h(SITE_BASE_URL) ?>/index.php" class="<?= $navScript === 'index.php' ? 'active' : '' ?>">Home</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/products.php" class="<?= $navScript === 'products.php' && $navCategory === '' ? 'active' : '' ?>">Shop</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/products.php?category=coffee" class="<?= $navCategory === 'coffee' ? 'active' : '' ?>">Coffee</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/products.php?category=tea" class="<?= $navCategory === 'tea' ? 'active' : '' ?>">Tea</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/about.php" class="<?= $navScript === 'about.php' ? 'active' : '' ?>">About</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/help/index.php" class="<?= $navIsHelp ? 'active' : '' ?>">Help</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/contact.php" class="<?= $navScript === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
        </ul>
        <ul class="nav-utility">
          <li>
            <a href="<?= h(SITE_BASE_URL) ?>/cart.php" class="cart-link" aria-label="<?= $cartCount > 0 ? 'View cart, ' . (int) $cartCount . ' item' . ($cartCount === 1 ? '' : 's') : 'View cart, empty' ?>">
              &#128722; Cart
              <?php if ($cartCount > 0): ?><span class="cart-badge"><?= (int) $cartCount ?></span><?php endif; ?>
            </a>
          </li>
          <?php if (is_logged_in()): ?>
            <li><a href="<?= h(SITE_BASE_URL) ?>/profile.php">Hi, <?= h($_SESSION['full_name']) ?></a></li>
            <li><a href="<?= h(SITE_BASE_URL) ?>/logout.php">Log out</a></li>
          <?php else: ?>
            <li><a href="<?= h(SITE_BASE_URL) ?>/login.php">Log in</a></li>
            <li><a href="<?= h(SITE_BASE_URL) ?>/register.php" class="btn-nav">Sign up</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</header>

<main id="main-content">

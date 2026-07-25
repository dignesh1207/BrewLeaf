<?php
/**
 * includes/header.php -- Shared page head + top navigation.
 * Calling page should set before including:
 *   $pageTitle        (string, required)
 *   $pageDescription  (string, required)
 *   $pageKeywords     (string, optional)
 * Requires config/db.php and includes/auth.php already included.
 */

// site-wide defaults if the calling page didn't set these
$pageTitle       = $pageTitle ?? 'BrewLeaf Artisan Coffee & Tea Co.';
$pageDescription = $pageDescription ?? 'BrewLeaf is an online artisan coffee and tea shop offering ethically sourced coffee beans and loose leaf teas from around the world.';
$pageKeywords    = $pageKeywords ?? 'coffee, tea, artisan coffee, loose leaf tea, online coffee shop, specialty tea';
// theme file to load below -- see get_active_theme()
$activeTheme     = get_active_theme($conn);

// current script/category, used to highlight the active nav link
$navScript   = basename($_SERVER['SCRIPT_NAME']);
$navCategory = $_GET['category'] ?? '';
$navIsHelp   = str_contains($_SERVER['SCRIPT_NAME'], '/help/');

// cart item count: keyed by user_id when logged in, guest session id otherwise
$cartCount = 0;
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- SEO meta from $pageTitle/$pageDescription/$pageKeywords -->
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($pageDescription) ?>">
<meta name="keywords" content="<?= h($pageKeywords) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="BrewLeaf Artisan Coffee & Tea Co.">
<link rel="canonical" href="<?= h(SITE_BASE_URL . $_SERVER['REQUEST_URI']) ?>">
<!-- Open Graph / social preview tags -->
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<meta property="og:type" content="website">
<!-- Favicon -->
<link rel="icon" type="image/png" href="<?= h(SITE_BASE_URL) ?>/assets/images/favicon.png">
<!--
  Load order matters: variables.css defines vars, theme-*.css overrides
  them for the active theme, base.css resets, component CSS follows,
  utilities.css loads last so it can fine-tune spacing above it.
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
<!-- preconnect warms up the Google Fonts connection early -->
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

    <nav class="primary-nav" id="primaryNav" aria-label="Primary">
      <ul>
        <!-- "active" class based on $navScript/$navCategory/$navIsHelp above -->
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
          <a href="<?= h(SITE_BASE_URL) ?>/cart.php" class="cart-link" aria-label="View cart">
            &#128722; Cart
            <?php if ($cartCount > 0): ?><span class="cart-badge"><?= (int) $cartCount ?></span><?php endif; ?>
          </a>
        </li>
        <?php if (is_logged_in()): ?>
          <li><a href="<?= h(SITE_BASE_URL) ?>/profile.php">Hi, <?= h($_SESSION['full_name']) ?></a></li>
          <?php if (is_admin()): ?><li><a href="<?= h(SITE_BASE_URL) ?>/admin/dashboard.php">Admin</a></li><?php endif; ?>
          <li><a href="<?= h(SITE_BASE_URL) ?>/logout.php">Log out</a></li>
        <?php else: ?>
          <li><a href="<?= h(SITE_BASE_URL) ?>/login.php">Log in</a></li>
          <li><a href="<?= h(SITE_BASE_URL) ?>/register.php" class="btn-nav">Sign up</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="main-content">

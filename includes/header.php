<?php
/**
 * includes/header.php
 * ---------------------------------------------------------------------------
 * Shared page head + top navigation. A calling page should set these
 * variables BEFORE including this file:
 *
 *   $pageTitle        (string, required)  e.g. "Shop | BrewLeaf"
 *   $pageDescription  (string, required)  1-2 sentence SEO meta description
 *   $pageKeywords     (string, optional)  comma separated SEO keywords
 *
 * Requires config/db.php and includes/auth.php to already be included so
 * $conn and session helpers are available.
 * ---------------------------------------------------------------------------
 */

$pageTitle       = $pageTitle ?? 'BrewLeaf Artisan Coffee & Tea Co.';
$pageDescription = $pageDescription ?? 'BrewLeaf is an online artisan coffee and tea shop offering ethically sourced coffee beans and loose leaf teas from around the world.';
$pageKeywords    = $pageKeywords ?? 'coffee, tea, artisan coffee, loose leaf tea, online coffee shop, specialty tea';
$activeTheme     = get_active_theme($conn);

// Which nav link should be highlighted as "you are here" (see header.css
// for the .active pill style). Based on the current script's filename and,
// for the shop links, the ?category= in the URL.
$navScript   = basename($_SERVER['SCRIPT_NAME']);
$navCategory = $_GET['category'] ?? '';
$navIsHelp   = str_contains($_SERVER['SCRIPT_NAME'], '/help/');

// Cart item count badge (works for both guests and logged-in users).
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
<!-- SEO meta tags -->
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($pageDescription) ?>">
<meta name="keywords" content="<?= h($pageKeywords) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="BrewLeaf Artisan Coffee & Tea Co.">
<link rel="canonical" href="<?= h(SITE_BASE_URL . $_SERVER['REQUEST_URI']) ?>">
<!-- Open Graph / social preview -->
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<meta property="og:type" content="website">
<!-- Favicon -->
<link rel="icon" type="image/png" href="<?= h(SITE_BASE_URL) ?>/assets/images/favicon.png">
<!--
  Every page loads the SAME set of small CSS files, in this order:
    1. variables.css   -- colors/fonts as reusable variables
    2. theme-*.css     -- overrides some of those variables for the active
                          theme (switchable site-wide in admin/theme.php)
    3. base.css         -- reset + default text/page styling
    4-13. one file per component (buttons, forms, header, footer, hero,
          sections, product cards, tables, dashboard, admin)
    14. utilities.css   -- small helper classes, loaded last so they can
                          fine-tune spacing set by the files above
  Only editing ONE thing? This list tells you which file to open.
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

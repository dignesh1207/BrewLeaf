<?php
// header.php
// Included at the top of every page.
// Sets up page information, styles, navigation, and user/cart details.


// Default page information if a page does not provide its own values
$pageTitle = $pageTitle ?? 'BrewLeaf Artisan Coffee & Tea Co.';
$pageDescription = $pageDescription ?? 'BrewLeaf is an online artisan coffee and tea shop offering ethically sourced coffee beans and loose leaf teas from around the world.';
$pageKeywords = $pageKeywords ?? 'coffee, tea, artisan coffee, loose leaf tea, online coffee shop, specialty tea';
$pageRobots = $pageRobots ?? 'index, follow';


// Default image used when sharing pages
$pageImage = $pageImage ?? '/assets/images/hero-banner.jpg';


// Get the currently selected website theme
$activeTheme = get_active_theme($conn);


// Information used for highlighting navigation links
$navScript = basename($_SERVER['SCRIPT_NAME']);
$navCategory = $_GET['category'] ?? '';
$navIsHelp = str_contains($_SERVER['SCRIPT_NAME'], '/help/');


// Select the correct help page depending on where the user is
$helpArticle = 'index.html';

if (str_contains($_SERVER['SCRIPT_NAME'], '/admin/')) {

    $helpArticle = 'admin-guide.php';

} elseif (in_array($navScript, ['cart.php', 'checkout.php'], true)) {

    $helpArticle = 'ordering-and-checkout.html';

} elseif ($navScript === 'profile.php') {

    $helpArticle = 'managing-account.html';

} elseif (
    in_array(
        $navScript,
        ['login.php', 'register.php', 'products.php', 'product.php'],
        true
    )
) {

    $helpArticle = 'getting-started.html';
}


$contextHelpUrl = SITE_BASE_URL . '/help/' . $helpArticle;


// Login link that returns the user back to the current page
$loginUrl = SITE_BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/');


// Count cart items for the navigation bar
$cartCount = 0;


if (!is_admin()) {

    if (is_logged_in()) {

        // Logged-in users use their account cart
        $cs = $conn->prepare(
            'SELECT COALESCE(SUM(quantity),0) AS n 
             FROM cart_items 
             WHERE user_id = ?'
        );

        $cs->bind_param('i', $_SESSION['user_id']);

    } else {

        // Guests use their session cart
        $gid = get_guest_session_id();

        $cs = $conn->prepare(
            'SELECT COALESCE(SUM(quantity),0) AS n 
             FROM cart_items 
             WHERE session_id = ?'
        );

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


<!-- Page SEO information -->
<title><?= h($pageTitle) ?></title>

<meta name="description" content="<?= h($pageDescription) ?>">

<meta name="keywords" content="<?= h($pageKeywords) ?>">

<meta name="robots" content="<?= h($pageRobots) ?>">

<meta name="Student" content="Dignesh Solanki">

<meta name="author" content="BrewLeaf Artisan Coffee & Tea Co.">


<link rel="canonical" href="<?= h(SITE_BASE_URL . $_SERVER['REQUEST_URI']) ?>">


<!-- Social media preview information -->
<meta property="og:title" content="<?= h($pageTitle) ?>">

<meta property="og:description" content="<?= h($pageDescription) ?>">

<meta property="og:image" content="<?= h(SITE_BASE_URL . $pageImage) ?>">

<meta property="og:type" content="website">


<!-- Website icon -->
<link rel="icon" type="image/png" href="<?= h(SITE_BASE_URL) ?>/assets/images/favicon.png">


<!-- CSS files -->
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


<!-- Google fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">


</head>


<body class="theme-<?= h($activeTheme) ?>">


<a class="skip-link" href="#main-content">
    Skip to main content
</a>


<header class="site-header">

<div class="container header-inner">


<a class="logo" href="<?= h(SITE_BASE_URL) ?>/index.php">
    BrewLeaf
</a>


<button 
    class="nav-toggle" 
    id="navToggle" 
    aria-expanded="false" 
    aria-controls="primaryNav" 
    aria-label="Toggle navigation menu"
>
    <span></span>
    <span></span>
    <span></span>
</button>


<?php if (is_admin()): ?>

<!-- Admin navigation -->

<nav class="primary-nav" id="primaryNav" aria-label="Primary">

<ul>

<li>
<a href="<?= h(SITE_BASE_URL) ?>/index.php"
class="<?= $navScript === 'index.php' ? 'active' : '' ?>">
Storefront
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/admin/dashboard.php"
class="<?= $navScript === 'dashboard.php' ? 'active' : '' ?>">
Dashboard
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/admin/products.php"
class="<?= $navScript === 'products.php' && str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? 'active' : '' ?>">
Products
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/admin/orders.php"
class="<?= $navScript === 'orders.php' ? 'active' : '' ?>">
Orders
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/admin/users.php"
class="<?= $navScript === 'users.php' ? 'active' : '' ?>">
Customers
</a>
</li>


<li>
<a href="<?= h($contextHelpUrl) ?>">
Help
</a>
</li>

</ul>


<ul class="nav-utility">

<li>
<a href="<?= h(SITE_BASE_URL) ?>/profile.php">
<?= h($_SESSION['full_name']) ?>
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/logout.php">
Log out
</a>
</li>


</ul>

</nav>


<?php else: ?>


<!-- Customer navigation -->

<nav class="primary-nav" id="primaryNav" aria-label="Primary">

<ul>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/index.php"
class="<?= $navScript === 'index.php' ? 'active' : '' ?>">
Home
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/products.php"
class="<?= $navScript === 'products.php' && $navCategory === '' ? 'active' : '' ?>">
Shop
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/products.php?category=coffee"
class="<?= $navCategory === 'coffee' ? 'active' : '' ?>">
Coffee
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/products.php?category=tea"
class="<?= $navCategory === 'tea' ? 'active' : '' ?>">
Tea
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/about.php"
class="<?= $navScript === 'about.php' ? 'active' : '' ?>">
About
</a>
</li>


<li>
<a href="<?= h($contextHelpUrl) ?>"
class="<?= $navIsHelp ? 'active' : '' ?>">
Help
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/contact.php"
class="<?= $navScript === 'contact.php' ? 'active' : '' ?>">
Contact
</a>
</li>


</ul>


<ul class="nav-utility">


<li>

<a 
href="<?= h(SITE_BASE_URL) ?>/cart.php" 
class="cart-link"
aria-label="<?= $cartCount > 0 ? 'View cart, ' . (int) $cartCount . ' item' . ($cartCount === 1 ? '' : 's') : 'View cart, empty' ?>"
>

&#128722; Cart

<?php if ($cartCount > 0): ?>

<span class="cart-badge">
<?= (int) $cartCount ?>
</span>

<?php endif; ?>

</a>

</li>


<?php if (is_logged_in()): ?>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/profile.php">
Hi, <?= h($_SESSION['full_name']) ?>
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/logout.php">
Log out
</a>
</li>


<?php else: ?>


<li>
<a href="<?= h($loginUrl) ?>">
Log in
</a>
</li>


<li>
<a href="<?= h(SITE_BASE_URL) ?>/register.php" class="btn-nav">
Sign up
</a>
</li>


<?php endif; ?>


</ul>

</nav>


<?php endif; ?>


</div>

</header>


<main id="main-content">
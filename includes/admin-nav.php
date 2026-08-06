<?php
// Admin navigation menu.
// This file is included after header.php.
// Set $adminActive before including this file to highlight the current page.

$adminActive = $adminActive ?? '';


// List of admin pages.
// Add another item here if a new admin page is created.
$adminLinks = [
    'dashboard' => [
        'label' => 'Dashboard',
        'url' => SITE_BASE_URL . '/admin/dashboard.php'
    ],

    'products' => [
        'label' => 'Products',
        'url' => SITE_BASE_URL . '/admin/products.php'
    ],

    'orders' => [
        'label' => 'Orders',
        'url' => SITE_BASE_URL . '/admin/orders.php'
    ],

    'users' => [
        'label' => 'Users',
        'url' => SITE_BASE_URL . '/admin/users.php'
    ],

    'theme' => [
        'label' => 'Site Template',
        'url' => SITE_BASE_URL . '/admin/theme.php'
    ],
];

?>

<!-- Admin navigation bar -->
<div class="admin-bar">

    <nav class="container" aria-label="Admin section">

        <ul>

            <?php foreach ($adminLinks as $key => $link): ?>

                <li>
                    <?php
                    // Add active class to show the current admin page
                    $activeClass = $adminActive === $key ? 'active' : '';
                    ?>

                    <a href="<?= h($link['url']) ?>" class="<?= $activeClass ?>">
                        <?= h($link['label']) ?>
                    </a>
                </li>

            <?php endforeach; ?>


            <!-- Link back to the main website -->
            <li class="admin-bar-back">
                <a href="<?= h(SITE_BASE_URL) ?>/index.php">
                    &larr; Back to Site
                </a>
            </li>

        </ul>

    </nav>

</div>
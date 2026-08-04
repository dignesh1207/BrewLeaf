<?php
// sitemap.php, generates the xml sitemap, lists static pages plus every active product page
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');

// product pages get added below in the loop since we can't hard-code every slug
$staticPages = [
    'index.php', 'products.php', 'about.php', 'contact.php',
    'help/index.html', 'help/getting-started.html', 'help/ordering-and-checkout.html',
    'help/managing-account.html', 'help/updating-content.html',
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// add the static pages first
foreach ($staticPages as $page) {
    echo '  <url><loc>' . htmlspecialchars(SITE_BASE_URL . '/' . $page) . '</loc><changefreq>weekly</changefreq></url>' . "\n";
}

// add the product pages, using the slug and created_at date for lastmod
$result = $conn->query('SELECT slug, created_at FROM products WHERE is_active = 1');
while ($row = $result->fetch_assoc()) {
    echo '  <url><loc>' . htmlspecialchars(SITE_BASE_URL . '/product.php?slug=' . $row['slug']) . '</loc>'
       . '<lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>'
       . '<changefreq>monthly</changefreq></url>' . "\n";
}

echo '</urlset>';

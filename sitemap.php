<?php
// sitemap.php
// Creates the XML sitemap for search engines.
// It includes fixed website pages and all active product pages.

require_once __DIR__ . '/config/db.php';


// Tell the browser this file contains XML data
header('Content-Type: application/xml; charset=utf-8');


// Pages that do not come from the database
$staticPages = [
    'index.php',
    'products.php',
    'about.php',
    'contact.php',
    'help/index.html',
    'help/getting-started.html',
    'help/ordering-and-checkout.html',
    'help/managing-account.html',
    'help/updating-content.html',
];


// Start the sitemap XML document
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";


// Add normal website pages to the sitemap
foreach ($staticPages as $page) {

    $pageUrl = SITE_BASE_URL . '/' . $page;

    echo '  <url>';
    echo '<loc>' . htmlspecialchars($pageUrl) . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '</url>' . "\n";
}


// Get all active products from the database
$result = $conn->query(
    'SELECT slug, created_at FROM products WHERE is_active = 1'
);


// Add each product page to the sitemap
while ($row = $result->fetch_assoc()) {

    $productUrl = SITE_BASE_URL . '/product.php?slug=' . $row['slug'];
    $lastUpdated = date('Y-m-d', strtotime($row['created_at']));

    echo '  <url>';
    echo '<loc>' . htmlspecialchars($productUrl) . '</loc>';
    echo '<lastmod>' . $lastUpdated . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '</url>' . "\n";
}


// Finish the sitemap
echo '</urlset>';
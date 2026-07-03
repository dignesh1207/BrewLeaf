<?php
/**
 * sitemap.php -- Dynamically generated XML sitemap for SEO. Lists all
 * static pages plus every active product detail page. Link this from
 * robots.txt (see /robots.txt) so search engines can discover it.
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');

$staticPages = ['index.php', 'products.php', 'about.php', 'contact.php', 'help/index.php'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticPages as $page) {
    echo '  <url><loc>' . htmlspecialchars(SITE_BASE_URL . '/' . $page) . '</loc><changefreq>weekly</changefreq></url>' . "\n";
}

$result = $conn->query('SELECT slug, created_at FROM products WHERE is_active = 1');
while ($row = $result->fetch_assoc()) {
    echo '  <url><loc>' . htmlspecialchars(SITE_BASE_URL . '/product.php?slug=' . $row['slug']) . '</loc>'
       . '<lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>'
       . '<changefreq>monthly</changefreq></url>' . "\n";
}

echo '</urlset>';

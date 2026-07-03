<?php
/**
 * config/db.php
 * ---------------------------------------------------------------------------
 * Central database connection using MySQLi (procedural-free, object style).
 * EVERY page includes this file to get a $conn handle.
 *
 * !! EDIT THESE FOUR CONSTANTS FOR YOUR HOSTING ENVIRONMENT !!
 * On myweb.cs.uwindsor.ca (or any shared host) these are provided by your
 * hosting control panel / DB admin.
 * ---------------------------------------------------------------------------
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'brewleaf');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL of the site (used for building absolute links, sitemap, canonical
// tags for SEO). Change to your live URL when deploying, e.g.
// 'https://myweb.cs.uwindsor.ca/~yourusername'
define('SITE_BASE_URL', '');

// mysqli should throw exceptions on error instead of failing silently.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Fail safely: log detail server-side, show a generic message to users.
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h1>Service temporarily unavailable</h1><p>Please try again shortly. (Database connection error)</p>');
}

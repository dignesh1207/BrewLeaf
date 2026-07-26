<?php
// db connection - every page includes this to get $conn
// change these 4 values depending on where it's hosted

define('DB_HOST', 'localhost');
define('DB_NAME', 'brewleaf');
define('DB_USER', 'root');
define('DB_PASS', '');

// used for building links/canonical tags. change when deploying, e.g.
// 'https://myweb.cs.uwindsor.ca/~yourusername(solank86)/brewleaf'
define('SITE_BASE_URL', '');

// make mysqli throw errors instead of failing silently
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // log the real error, show a generic message to users
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h1>Service temporarily unavailable</h1><p>Please try again shortly. (Database connection error)</p>');
}

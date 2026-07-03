<?php
/** logout.php -- Destroys the session and returns to the home page. */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
logout();
header('Location: index.php');
exit;

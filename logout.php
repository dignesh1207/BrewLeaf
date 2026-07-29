<?php
// logout.php -- kills the session, sends back to home page simple!

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

logout();
header('Location: index.php');
exit;

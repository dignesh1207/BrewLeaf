<?php
/** admin/index.php -- Redirect: admins to dashboard, everyone else to login. */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Location: ' . (is_admin() ? 'dashboard.php' : '../login.php'));
exit;

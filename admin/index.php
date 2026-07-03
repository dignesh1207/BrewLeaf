<?php
/**
 * admin/index.php -- Convenience redirect: send logged-in admins to the
 * dashboard, everyone else to the shared login page.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Location: ' . (is_admin() ? 'dashboard.php' : '../login.php'));
exit;

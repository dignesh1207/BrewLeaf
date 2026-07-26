<?php
// admin/index.php - sends admins to the dashboard, everyone else back to login
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Location: ' . (is_admin() ? 'dashboard.php' : '../login.php'));
exit;

<?php
// set-theme.php, switches the site theme from the footer, anyone can use
// it, no login needed. writes to the same site_settings row as admin/theme.php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $chosen = $_POST['theme'];
    // only these 4 theme names are allowed, anything else just gets ignored
    if (in_array($chosen, ['white', 'regular', 'autumn', 'winter'], true)) {
        // insert or update, there's only ever one active_theme row in site_settings
        $stmt = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES ("active_theme", ?)
                                 ON DUPLICATE KEY UPDATE setting_value = ?');
        $stmt->bind_param('ss', $chosen, $chosen);
        $stmt->execute();
        $stmt->close();
    }
}

// go back to whatever page sent us here. has to start with "/" or someone
// could use this to redirect people off to some other site
$backTo = $_POST['redirect_to'] ?? '';
if (!str_starts_with($backTo, '/')) {
    $backTo = '/index.php';
}
header('Location: ' . SITE_BASE_URL . $backTo);
exit;

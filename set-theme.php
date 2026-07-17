<?php
/**
 * set-theme.php -- Lets ANY visitor change the site-wide theme from the
 * theme switcher in the footer (see includes/footer.php), no login needed.
 * This is the same site_settings.active_theme write that admin/theme.php
 * does -- that page still exists too, this is just a second, public way
 * to change the same setting.
 */
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $chosen = $_POST['theme'];
    if (in_array($chosen, ['white', 'regular', 'autumn', 'winter'], true)) {
        $stmt = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES ("active_theme", ?)
                                 ON DUPLICATE KEY UPDATE setting_value = ?');
        $stmt->bind_param('ss', $chosen, $chosen);
        $stmt->execute();
        $stmt->close();
    }
}

// Send the visitor back to whichever page they clicked the switcher from,
// so picking a theme doesn't knock them back to the home page. Only allow
// a redirect back into this same site (must start with "/") -- otherwise
// someone could craft a link that uses this page to redirect elsewhere.
$backTo = $_POST['redirect_to'] ?? '';
if (!str_starts_with($backTo, '/')) {
    $backTo = '/index.php';
}
header('Location: ' . SITE_BASE_URL . $backTo);
exit;

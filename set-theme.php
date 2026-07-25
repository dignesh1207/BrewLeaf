<?php
/**
 * set-theme.php -- Lets any visitor change the site-wide theme from the
 * footer switcher, no login needed. Writes the same site_settings row
 * that admin/theme.php does.
 */
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $chosen = $_POST['theme'];
    // Whitelisted theme names only; anything else is silently ignored.
    if (in_array($chosen, ['white', 'regular', 'autumn', 'winter'], true)) {
        // Upsert: site_settings only ever has one active_theme row.
        $stmt = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES ("active_theme", ?)
                                 ON DUPLICATE KEY UPDATE setting_value = ?');
        $stmt->bind_param('ss', $chosen, $chosen);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the referring page. Must start with "/" -- prevents
// this endpoint being used as an open redirect to another site.
$backTo = $_POST['redirect_to'] ?? '';
if (!str_starts_with($backTo, '/')) {
    $backTo = '/index.php';
}
header('Location: ' . SITE_BASE_URL . $backTo);
exit;

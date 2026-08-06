<?php
// set-theme.php
// This file changes the website theme selected from the footer.
// Users do not need to be logged in to change the theme.

require_once __DIR__ . '/config/db.php';


// Check if the form was submitted and a theme was selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {

    $chosen = $_POST['theme'];

    // Only allow the available themes
    $allowedThemes = ['white', 'regular', 'autumn', 'winter'];

    if (in_array($chosen, $allowedThemes, true)) {

        // Save the selected theme in the database.
        // If the setting already exists, update it instead.
        $stmt = $conn->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) 
             VALUES ("active_theme", ?)
             ON DUPLICATE KEY UPDATE setting_value = ?'
        );

        $stmt->bind_param('ss', $chosen, $chosen);

        $stmt->execute();
        $stmt->close();
    }
}


// Redirect the user back to the page they came from
$backTo = $_POST['redirect_to'] ?? '';


// Only allow internal redirects for security
if (!str_starts_with($backTo, '/')) {
    $backTo = '/index.php';
}


header('Location: ' . SITE_BASE_URL . $backTo);
exit;
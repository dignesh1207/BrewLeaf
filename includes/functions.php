<?php
// functions.php
// Contains helper functions used throughout the website.


// Safely display text inside HTML to prevent XSS attacks
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


// Format a number as money
// Example: 14.99 becomes $14.99
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}


// Get all options for a product and group them by option name
function get_product_options(mysqli $conn, int $productId): array
{
    $stmt = $conn->prepare(
        'SELECT id, option_group, option_value, price_modifier
         FROM product_options 
         WHERE product_id = ? 
         ORDER BY option_group, id'
    );

    $stmt->bind_param('i', $productId);

    $stmt->execute();

    $result = $stmt->get_result();


    $grouped = [];

    while ($row = $result->fetch_assoc()) {

        // Store options under their group name
        $grouped[$row['option_group']][] = $row;
    }


    $stmt->close();

    return $grouped;
}


// Find the active website theme
// Checks preview theme first, then checks the database
function get_active_theme(mysqli $conn): string
{
    $availableThemes = [
        'regular',
        'autumn',
        'winter',
        'white'
    ];


    // Allow temporary theme preview through URL
    if (
        isset($_GET['preview_theme']) &&
        in_array($_GET['preview_theme'], $availableThemes, true)
    ) {
        return $_GET['preview_theme'];
    }


    // Get saved theme from database
    $result = $conn->query(
        "SELECT setting_value 
         FROM site_settings 
         WHERE setting_key = 'active_theme' 
         LIMIT 1"
    );


    $row = $result ? $result->fetch_assoc() : null;

    $theme = $row['setting_value'] ?? 'white';


    // Make sure the saved theme is valid
    return in_array($theme, $availableThemes, true)
        ? $theme
        : 'white';
}


// Convert rating number into star display
function render_stars(float $rating): string
{
    $full = (int) floor($rating);

    $half = ($rating - $full) >= 0.5 ? 1 : 0;

    $empty = 5 - $full - $half;


    return str_repeat('&#9733;', $full)
        . ($half ? '&#189;' : '')
        . str_repeat('&#9734;', $empty);
}


// Create a guest ID for users who are not logged in
function get_guest_session_id(): string
{
    if (empty($_SESSION['guest_id'])) {

        // Generate a random ID for the guest cart
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }


    return $_SESSION['guest_id'];
}


// Get selected product options from submitted option IDs
function resolve_selected_options(mysqli $conn, array $optionIds): array
{
    // Clean the option IDs before using them
    $cleanIds = [];

    foreach ($optionIds as $id) {

        $id = (int) $id;

        if ($id > 0 && !in_array($id, $cleanIds, true)) {
            $cleanIds[] = $id;
        }
    }


    $optionIds = $cleanIds;


    // No options selected
    if (!$optionIds) {
        return [
            'options' => [],
            'modifierTotal' => 0.0
        ];
    }


    // Create placeholders for SQL query
    $placeholders = implode(
        ',',
        array_fill(0, count($optionIds), '?')
    );


    $types = str_repeat('i', count($optionIds));


    $stmt = $conn->prepare(
        "SELECT id, option_group, option_value, price_modifier
         FROM product_options 
         WHERE id IN ($placeholders)"
    );


    $stmt->bind_param($types, ...$optionIds);

    $stmt->execute();


    $result = $stmt->get_result();


    $options = [];

    $modifierTotal = 0.0;


    // Store option details and calculate extra cost
    while ($row = $result->fetch_assoc()) {

        $options[] = [
            'option_id' => (int) $row['id'],
            'group' => $row['option_group'],
            'value' => $row['option_value'],
            'price_modifier' => (float) $row['price_modifier'],
        ];


        $modifierTotal += (float) $row['price_modifier'];
    }


    $stmt->close();


    return [
        'options' => $options,
        'modifierTotal' => $modifierTotal
    ];
}


// Convert saved JSON options into readable text
// Example: Size: Large, Grind: Whole Bean
function format_selected_options(?string $json): string
{
    $options = json_decode($json ?? '[]', true) ?: [];


    $parts = [];


    foreach ($options as $option) {

        $parts[] = $option['group'] . ': ' . $option['value'];
    }


    return implode(', ', $parts);
}
<?php
// helper functions used all over the site

// escapes stuff for html so we don't get xss, wrap any variable in this before printing it
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// turns 14.99 into "$14.99"
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

// gets a product's options grouped by name, e.g. ['Size' => [...], 'Grind' => [...]]
function get_product_options(mysqli $conn, int $productId): array
{
    // ordering by option_group so rows in the same group end up next to each other for the loop below
    $stmt = $conn->prepare(
        'SELECT id, option_group, option_value, price_modifier
         FROM product_options WHERE product_id = ? ORDER BY option_group, id'
    );
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $grouped = [];
    while ($row = $result->fetch_assoc()) {
        $grouped[$row['option_group']][] = $row;
    }
    $stmt->close();
    return $grouped;
}

// figures out which theme is active - ?preview_theme= first, then site_settings in the db
function get_active_theme(mysqli $conn): string
{
    // only allow these exact values, don't want a random ?preview_theme= messing with the file path
    if (isset($_GET['preview_theme']) && in_array($_GET['preview_theme'], ['regular', 'autumn', 'winter', 'white'], true)) {
        return $_GET['preview_theme'];
    }

    $result = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_theme' LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    $theme = $row['setting_value'] ?? 'white';
    // checking again in case whatever's saved in the db is empty or not a real theme name
    return in_array($theme, ['regular', 'autumn', 'winter', 'white'], true) ? $theme : 'white';
}

// takes a rating like 3.5 and turns it into star html (filled/half/empty)
function render_stars(float $rating): string
{
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('&#9733;', $full) . ($half ? '&#189;' : '') . str_repeat('&#9734;', $empty);
}

// gives a logged-out guest a random id, so their cart_items can use session_id
function get_guest_session_id(): string
{
    if (empty($_SESSION['guest_id'])) {
        // random_bytes so this can't be guessed
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_id'];
}

// turns posted option ids into full rows to save as json, plus the total price modifier
function resolve_selected_options(mysqli $conn, array $optionIds): array
{
    // make everything an int and drop zeros/dupes
    $cleanIds = [];
    foreach ($optionIds as $id) {
        $id = (int) $id;
        if ($id > 0 && !in_array($id, $cleanIds, true)) {
            $cleanIds[] = $id;
        }
    }
    $optionIds = $cleanIds;

    // an empty IN () breaks the query so just bail out early
    if (!$optionIds) {
        return ['options' => [], 'modifierTotal' => 0.0];
    }

    // need one ? placeholder for each id
    $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
    $types = str_repeat('i', count($optionIds));
    $stmt = $conn->prepare("SELECT id, option_group, option_value, price_modifier FROM product_options WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$optionIds);
    $stmt->execute();
    $result = $stmt->get_result();

    // build the array we'll save as json and total up the price modifier at the same time
    $options = [];
    $modifierTotal = 0.0;
    while ($row = $result->fetch_assoc()) {
        $options[] = [
            'option_id'      => (int) $row['id'],
            'group'          => $row['option_group'],
            'value'          => $row['option_value'],
            'price_modifier' => (float) $row['price_modifier'],
        ];
        $modifierTotal += (float) $row['price_modifier'];
    }
    $stmt->close();

    return ['options' => $options, 'modifierTotal' => $modifierTotal];
}

// turns the saved json back into readable text, e.g. "Size: Large, Grind: Whole Bean"
function format_selected_options(?string $json): string
{
    // if json_decode fails for some reason just treat it as empty instead of erroring
    $options = json_decode($json ?? '[]', true) ?: [];
    $parts = [];
    foreach ($options as $option) {
        $parts[] = $option['group'] . ': ' . $option['value'];
    }
    return implode(', ', $parts);
}

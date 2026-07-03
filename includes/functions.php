<?php
/**
 * includes/functions.php
 * ---------------------------------------------------------------------------
 * Small shared helper functions used across the front-end. Keeping these in
 * one place avoids duplicating logic on every page.
 * ---------------------------------------------------------------------------
 */

/** Escape output for safe HTML insertion (prevents XSS). */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a price as currency, e.g. 14.99 -> "$14.99". */
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/** Get all product option groups for a product, grouped by option_group. */
function get_product_options(mysqli $conn, int $productId): array
{
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

/** Return the currently active site template name ('regular'|'autumn'|'winter'). */
function get_active_theme(mysqli $conn): string
{
    // Allow a per-visitor preview override via ?preview_theme= without
    // changing the site-wide default (useful for the admin theme picker).
    if (isset($_GET['preview_theme']) && in_array($_GET['preview_theme'], ['regular', 'autumn', 'winter'], true)) {
        return $_GET['preview_theme'];
    }

    $result = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_theme' LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    $theme = $row['setting_value'] ?? 'regular';
    return in_array($theme, ['regular', 'autumn', 'winter'], true) ? $theme : 'regular';
}

/** Simple star rating renderer (returns HTML). */
function render_stars(float $rating): string
{
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('&#9733;', $full) . ($half ? '&#189;' : '') . str_repeat('&#9734;', $empty);
}

/** Get or create a stable guest identifier for anonymous shopping carts. */
function get_guest_session_id(): string
{
    if (empty($_SESSION['guest_id'])) {
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_id'];
}

/**
 * Given an array of product_options.id values (one per option group the
 * shopper picked, e.g. Size + Grind), fetch the full rows and return both
 * a JSON-ready array (for storing in cart_items/order_items.selected_options)
 * and the total price modifier to add to the product's base price.
 *
 * @return array{options: array, modifierTotal: float}
 */
function resolve_selected_options(mysqli $conn, array $optionIds): array
{
    $optionIds = array_values(array_unique(array_filter(array_map('intval', $optionIds))));
    if (!$optionIds) {
        return ['options' => [], 'modifierTotal' => 0.0];
    }

    $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
    $types = str_repeat('i', count($optionIds));
    $stmt = $conn->prepare("SELECT id, option_group, option_value, price_modifier FROM product_options WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$optionIds);
    $stmt->execute();
    $result = $stmt->get_result();

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

/** Render a decoded selected_options JSON array as a short human-readable string. */
function format_selected_options(?string $json): string
{
    $options = json_decode($json ?? '[]', true) ?: [];
    $parts = array_map(fn($o) => $o['group'] . ': ' . $o['value'], $options);
    return implode(', ', $parts);
}

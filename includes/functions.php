<?php
/** Small shared helper functions used across the front-end. */

/** Escape a string for safe HTML output (XSS prevention). Wrap every dynamic value in h(...) before echoing it. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a float as currency, e.g. 14.99 -> "$14.99". */
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Get a product's option rows grouped by option_group, e.g.
 * ['Size' => [...], 'Grind' => [...]]. Empty array if none.
 */
function get_product_options(mysqli $conn, int $productId): array
{
    // order by option_group so rows for the same group are adjacent, for the grouping loop below
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

/**
 * Active site theme name ('regular'|'autumn'|'winter'|'white'). Reads
 * site_settings, or ?preview_theme= for a per-visitor preview. Always
 * whitelisted -- the value builds a CSS filename, so an unchecked value
 * could be abused to include an arbitrary file.
 */
function get_active_theme(mysqli $conn): string
{
    // whitelist prevents a crafted ?preview_theme= from pointing at an arbitrary file
    if (isset($_GET['preview_theme']) && in_array($_GET['preview_theme'], ['regular', 'autumn', 'winter', 'white'], true)) {
        return $_GET['preview_theme'];
    }

    $result = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_theme' LIMIT 1");
    $row = $result ? $result->fetch_assoc() : null;
    $theme = $row['setting_value'] ?? 'white';
    // re-check whitelist in case the stored value is missing/invalid
    return in_array($theme, ['regular', 'autumn', 'winter', 'white'], true) ? $theme : 'white';
}

/** Render a 0-5 rating (decimals allowed) as filled/half/outline star HTML. */
function render_stars(float $rating): string
{
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('&#9733;', $full) . ($half ? '&#189;' : '') . str_repeat('&#9734;', $empty);
}

/**
 * Get (or create) a stable per-session guest id for anonymous carts,
 * so cart_items can key off session_id when there's no user_id.
 */
function get_guest_session_id(): string
{
    if (empty($_SESSION['guest_id'])) {
        // cryptographically secure so it can't be guessed
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_id'];
}

/**
 * Resolve selected product_options ids into full rows (for storing as JSON)
 * plus the total price modifier. $optionIds is the raw posted list (may
 * contain junk/0s/duplicates, cleaned up here).
 *
 * @return array{options: array, modifierTotal: float}
 */
function resolve_selected_options(mysqli $conn, array $optionIds): array
{
    // cast to int, drop zeros/duplicates
    $cleanIds = [];
    foreach ($optionIds as $id) {
        $id = (int) $id;
        if ($id > 0 && !in_array($id, $cleanIds, true)) {
            $cleanIds[] = $id;
        }
    }
    $optionIds = $cleanIds;

    // empty IN (...) would be invalid SQL
    if (!$optionIds) {
        return ['options' => [], 'modifierTotal' => 0.0];
    }

    // one "?" placeholder per id
    $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
    $types = str_repeat('i', count($optionIds));
    $stmt = $conn->prepare("SELECT id, option_group, option_value, price_modifier FROM product_options WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$optionIds);
    $stmt->execute();
    $result = $stmt->get_result();

    // reshape for JSON storage, accumulate the price modifier total
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

/**
 * Render selected_options JSON (from resolve_selected_options()) as
 * "Size: Large, Grind: Whole Bean". Empty string if none.
 */
function format_selected_options(?string $json): string
{
    // fall back to [] if decoding fails
    $options = json_decode($json ?? '[]', true) ?: [];
    $parts = [];
    foreach ($options as $option) {
        $parts[] = $option['group'] . ': ' . $option['value'];
    }
    return implode(', ', $parts);
}

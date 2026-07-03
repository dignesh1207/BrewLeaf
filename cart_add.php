<?php
/**
 * cart_add.php -- POST endpoint for the add-to-cart form on product.php.
 * Handles both AJAX (fetch, returns JSON) and a plain form-submit fallback
 * (redirects to cart.php) since main.js progressively enhances the form.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function respond(bool $ok, string $message, bool $isAjax, mysqli $conn, ?int $userId, ?string $guestId, string $redirect = 'cart.php')
{
    if ($isAjax) {
        header('Content-Type: application/json');
        $cartCount = 0;
        if ($userId) {
            $s = $conn->prepare('SELECT COALESCE(SUM(quantity),0) AS n FROM cart_items WHERE user_id = ?');
            $s->bind_param('i', $userId);
        } else {
            $s = $conn->prepare('SELECT COALESCE(SUM(quantity),0) AS n FROM cart_items WHERE session_id = ?');
            $s->bind_param('s', $guestId);
        }
        $s->execute();
        $cartCount = (int) ($s->get_result()->fetch_assoc()['n'] ?? 0);
        $s->close();

        echo json_encode(['success' => $ok, 'message' => $message, 'cartCount' => $cartCount]);
        exit;
    }
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', $isAjax, $conn, is_logged_in() ? (int) $_SESSION['user_id'] : null, is_logged_in() ? null : get_guest_session_id());
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

$prodStmt = $conn->prepare('SELECT id, name, base_price FROM products WHERE id = ? AND is_active = 1');
$prodStmt->bind_param('i', $productId);
$prodStmt->execute();
$product = $prodStmt->get_result()->fetch_assoc();
$prodStmt->close();

$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
$guestId = is_logged_in() ? null : get_guest_session_id();

if (!$product) {
    respond(false, 'Product not found.', $isAjax, $conn, $userId, $guestId);
}

// Collect every posted field named option_* (one per option group, e.g.
// option_size, option_grind) -- their values are product_options.id.
$optionIds = [];
foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'option_') && $value !== '') {
        $optionIds[] = (int) $value;
    }
}
$resolved = resolve_selected_options($conn, $optionIds);
$unitPrice = (float) $product['base_price'] + $resolved['modifierTotal'];
$optionsJson = json_encode($resolved['options']);

$ins = $conn->prepare(
    'INSERT INTO cart_items (user_id, session_id, product_id, selected_options, unit_price, quantity)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$ins->bind_param('isisdi', $userId, $guestId, $productId, $optionsJson, $unitPrice, $quantity);
$ins->execute();
$ins->close();

respond(true, h($product['name']) . ' added to your cart.', $isAjax, $conn, $userId, $guestId);

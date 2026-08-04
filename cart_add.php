<?php
// handles the add-to-cart form from product.php - works via AJAX or a normal redirect
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// check if this came from fetch() in main.js or a normal form post
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

// sends back JSON + new cart count if AJAX, otherwise just redirects. exits either way
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

// admins can't add to cart, role comes from the session not the request
if (is_admin()) {
    respond(false, 'Admin accounts cannot add items to a cart.', $isAjax, $conn, (int) $_SESSION['user_id'], null, SITE_BASE_URL . '/admin/dashboard.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

// grab the real product from the db, is_active = 1 also blocks delisted products
$prodStmt = $conn->prepare('SELECT id, name, base_price FROM products WHERE id = ? AND is_active = 1');
$prodStmt->bind_param('i', $productId);
$prodStmt->execute();
$product = $prodStmt->get_result()->fetch_assoc();
$prodStmt->close();

$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
$guestId = is_logged_in() ? null : get_guest_session_id();

// if the product doesn't exist or is inactive, don't add it to the cart
if (!$product) {
    respond(false, 'Product not found.', $isAjax, $conn, $userId, $guestId);
}

// grab all the option_* fields from the post, these are product_options ids
$optionIds = [];
foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'option_') && $value !== '') {
        $optionIds[] = (int) $value;
    }
}
$resolved = resolve_selected_options($conn, $optionIds);
$unitPrice = (float) $product['base_price'] + $resolved['modifierTotal'];
// save as JSON so even if product_options changes later, this cart row still shows what was picked
$optionsJson = json_encode($resolved['options']);

// note: just inserts a new row every time, doesn't merge with an existing matching line
$ins = $conn->prepare(
    'INSERT INTO cart_items (user_id, session_id, product_id, selected_options, unit_price, quantity)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$ins->bind_param('isisdi', $userId, $guestId, $productId, $optionsJson, $unitPrice, $quantity);
$ins->execute();
$ins->close();

respond(true, h($product['name']) . ' added to your cart.', $isAjax, $conn, $userId, $guestId);

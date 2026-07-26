<?php
// cart.php posts here to change a quantity or remove a line
// same AJAX-or-redirect thing as cart_add.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

// re-adds up the whole cart from the db after any change, so the JSON we
// send back is correct and not just based on the one row that changed
function cart_update_summary(mysqli $conn, ?int $userId, ?string $guestId): array
{
    if ($userId) {
        $stmt = $conn->prepare('SELECT quantity, unit_price FROM cart_items WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
    } else {
        $stmt = $conn->prepare('SELECT quantity, unit_price FROM cart_items WHERE session_id = ?');
        $stmt->bind_param('s', $guestId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $subtotal = 0.0;
    $cartCount = 0;
    while ($row = $result->fetch_assoc()) {
        $subtotal += (float) $row['unit_price'] * (int) $row['quantity'];
        $cartCount += (int) $row['quantity'];
    }
    $stmt->close();

    $shipping = $subtotal > 0 && $subtotal < 40 ? 5.99 : 0.0;
    $total = $subtotal + $shipping;

    return [
        'subtotal' => $subtotal,
        'subtotalFormatted' => money($subtotal),
        'shipping' => $shipping,
        'shippingFormatted' => $shipping > 0 ? money($shipping) : 'Free',
        'total' => $total,
        'totalFormatted' => money($total),
        'cartCount' => $cartCount,
        'cartEmpty' => $cartCount === 0,
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
    header('Location: cart.php');
    exit;
}

// admins have no cart to update, same session role check as the other cart files
if (is_admin()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin accounts cannot manage a cart.']);
        exit;
    }
    header('Location: ' . SITE_BASE_URL . '/admin/dashboard.php');
    exit;
}

// action will be either 'update' or 'remove'
$itemId = (int) ($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? 'update';

$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
$guestId = is_logged_in() ? null : get_guest_session_id();

// make sure this cart row actually belongs to this user/guest before touching it
if ($userId) {
    $owns = $conn->prepare('SELECT id FROM cart_items WHERE id = ? AND user_id = ?');
    $owns->bind_param('ii', $itemId, $userId);
} else {
    $owns = $conn->prepare('SELECT id FROM cart_items WHERE id = ? AND session_id = ?');
    $owns->bind_param('is', $itemId, $guestId);
}
$owns->execute();
$ownsRow = $owns->get_result()->fetch_assoc();
$owns->close();

if (!$ownsRow) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'That item is no longer in your cart.']);
        exit;
    }
    header('Location: cart.php');
    exit;
}

$lineTotal = null;
$quantity = null;

if ($action === 'remove') {
    $del = $conn->prepare('DELETE FROM cart_items WHERE id = ?');
    $del->bind_param('i', $itemId);
    $del->execute();
    $del->close();
} else {
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $upd = $conn->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
    $upd->bind_param('ii', $quantity, $itemId);
    $upd->execute();
    $upd->close();

    $priceStmt = $conn->prepare('SELECT unit_price FROM cart_items WHERE id = ?');
    $priceStmt->bind_param('i', $itemId);
    $priceStmt->execute();
    $unitPrice = (float) ($priceStmt->get_result()->fetch_assoc()['unit_price'] ?? 0);
    $priceStmt->close();
    $lineTotal = $unitPrice * $quantity;
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(
        [
            'success' => true,
            'action' => $action,
            'itemId' => $itemId,
            'quantity' => $quantity,
            'lineTotal' => $lineTotal,
            'lineTotalFormatted' => $lineTotal !== null ? money($lineTotal) : null,
        ],
        cart_update_summary($conn, $userId, $guestId)
    ));
    exit;
}

header('Location: cart.php');
exit;

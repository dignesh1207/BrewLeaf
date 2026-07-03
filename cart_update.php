<?php
/**
 * cart_update.php -- POST endpoint used by cart.php to change a line's
 * quantity or remove it entirely. Always redirects back to cart.php.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$itemId = (int) ($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? 'update';

$userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
$guestId = is_logged_in() ? null : get_guest_session_id();

// Ownership check: only allow updating a cart row that belongs to this
// user/guest session, never an arbitrary item_id.
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

if ($ownsRow) {
    if ($action === 'remove') {
        $del = $conn->prepare('DELETE FROM cart_items WHERE id = ?');
        $del->bind_param('i', $itemId);
        $del->execute();
        $del->close();
    } else {
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $upd = $conn->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $upd->bind_param('ii', $qty, $itemId);
        $upd->execute();
        $upd->close();
    }
}

header('Location: cart.php');
exit;

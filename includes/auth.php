<?php
/**
 * includes/auth.php
 * ---------------------------------------------------------------------------
 * Session bootstrap + authentication helper functions.
 * Include this AFTER config/db.php on any page that needs to know who
 * (if anyone) is logged in, or that needs to protect a private area.
 * ---------------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is a user currently logged in? */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Is the current logged-in user an admin? */
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Redirect to login.php if not authenticated, preserving the return URL.
 * Uses SITE_BASE_URL so this works correctly whether called from the site
 * root or from a subfolder like /admin/.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SITE_BASE_URL . '/login.php?redirect=' . $return);
        exit;
    }
}

/** Redirect to the shared login page if not an authenticated admin. */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . SITE_BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Attempt to authenticate a user by username/email + password.
 * Returns the user row (array) on success, or null on failure.
 * Disabled accounts are rejected even with a correct password.
 */
function attempt_login(mysqli $conn, string $identifier, string $password): ?array
{
    $stmt = $conn->prepare(
        'SELECT id, username, email, password_hash, full_name, role, status
         FROM users WHERE username = ? OR email = ? LIMIT 1'
    );
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    if ($user['status'] !== 'active') {
        return null; // account disabled by admin
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    return $user;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

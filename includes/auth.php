<?php
/**
 * Session bootstrap + authentication helpers.
 * Include AFTER config/db.php.
 */

// avoid a "session already started" warning on double-include
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** True if a user is logged in (checks session user_id, set by attempt_login()). */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** True if the logged-in user has the admin role. Use require_admin() to actually block a page. */
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Redirect to login.php (preserving return URL) if not logged in.
 * Call as the first line of any page requiring sign-in; exits on redirect.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SITE_BASE_URL . '/login.php?redirect=' . $return);
        exit;
    }
}

/** Redirect to login.php if not an authenticated admin. Put at the top of every /admin/ page. */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . SITE_BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Authenticate by username/email + password (checked against both columns).
 * Returns the user row on success and stores it in the session, or null on
 * failure/disabled account. Uses a prepared statement to avoid SQL injection.
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

    // password_verify() checks against the stored bcrypt hash; also fails safely if no user was found
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

/** Log out: clears session data and destroys the session. */
function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

<?php
// this file starts the session and has all my login-related functions
// needs config/db.php loaded before this one


// don't want to call session_start() twice or php throws a warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

// checks if the logged in user is an admin
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

// use this at the top of a page to force someone to log in first
// sends them to login.php and keeps track of where they were trying to go
function require_login(): void
{
    if (!is_logged_in()) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SITE_BASE_URL . '/login.php?redirect=' . $return);
        exit;
    }
}

// same idea but for admin only pages
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . SITE_BASE_URL . '/login.php');
        exit;
    }
}

// checks username or email + password, logs them in if it matches
// returns the user row if it worked, null if it didn't
function attempt_login(mysqli $conn, string $identifier, string $password): ?array
{
    // this is a prepared statement so we don't have to worry about SQL injection
    $stmt = $conn->prepare(
        'SELECT id, username, email, password_hash, full_name, role, status
         FROM users WHERE username = ? OR email = ? LIMIT 1'
    );
    // bind the same $identifier to both placeholders, so they can log in with either username or email
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // this also handles the "no user found" case since $user would be null/false then
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    // don't let a disabled account log in even if the password is right
    if ($user['status'] !== 'active') {
        return null;
    }

    // save the user info in the session so is_logged_in()/is_admin() etc work
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    return $user;
}

//
function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

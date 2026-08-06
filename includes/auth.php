<?php
// auth.php
// Starts the session and contains functions related to login and user access.


// Start the session only if it has not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Check if a user is currently logged in
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}


// Check if the current user has admin access
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}


// Redirect users to login if they are not logged in
function require_login(): void
{
    if (!is_logged_in()) {

        // Save the current page so the user can return after logging in
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '/');

        header(
            'Location: ' . SITE_BASE_URL . '/login.php?redirect=' . $return
        );

        exit;
    }
}


// Redirect users who are not admins
function require_admin(): void
{
    if (!is_admin()) {

        header('Location: ' . SITE_BASE_URL . '/login.php');

        exit;
    }
}


// Try to log a user in using username/email and password
function attempt_login(mysqli $conn, string $identifier, string $password): ?array
{
    // Find the user by username or email
    $stmt = $conn->prepare(
        'SELECT id, username, email, password_hash, full_name, role, status
         FROM users WHERE username = ? OR email = ? LIMIT 1'
    );

    // Same value is used because users can enter either username or email
    $stmt->bind_param('ss', $identifier, $identifier);

    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    $stmt->close();


    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }


    // Prevent inactive accounts from logging in
    if ($user['status'] !== 'active') {
        return null;
    }


    // Store user information in the session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];


    // Move guest cart items into the user's account after login
    if (!empty($_SESSION['guest_id'])) {

        $merge = $conn->prepare(
            'UPDATE cart_items 
             SET user_id = ?, session_id = NULL 
             WHERE session_id = ?'
        );

        $merge->bind_param('is', $user['id'], $_SESSION['guest_id']);

        $merge->execute();

        $merge->close();
    }


    return $user;
}


// Log the user out and clear session data
function logout(): void
{
    $_SESSION = [];

    session_destroy();
}
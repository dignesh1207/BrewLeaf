<?php
// register.php - page where new customers create an account

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If the user is already logged in, send them to their profile
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';

// Run this code only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the values entered by the user
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    // Check if all required fields have been filled
    if ($username === '' || $email === '' || $fullName === '' || $password === '') {

        $error = 'Please fill in every field.';

    // Check if email format is correct
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    // Check password length
    } elseif (strlen($password) < 8) {

        $error = 'Password must be at least 8 characters.';

    // Check if both password fields match
    } elseif ($password !== $confirm) {

        $error = 'Passwords do not match.';

    } else {

        // Check if username or email already exists in the database
        $check = $conn->prepare(
            'SELECT id FROM users WHERE username = ? OR email = ?'
        );

        $check->bind_param('ss', $username, $email);
        $check->execute();

        $existingUser = $check->get_result()->fetch_assoc();

        if ($existingUser) {

            $error = 'That username or email is already registered.';

        } else {

            // Encrypt the password before saving it
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Add the new user to the database
            $ins = $conn->prepare(
                'INSERT INTO users 
                (username, email, password_hash, full_name, role, status) 
                VALUES (?, ?, ?, ?, "customer", "active")'
            );

            $ins->bind_param(
                'ssss',
                $username,
                $email,
                $hash,
                $fullName
            );

            $ins->execute();
            $ins->close();

            // Automatically log the new user in
            attempt_login($conn, $username, $password);

            // Redirect to profile page after successful registration
            header('Location: profile.php');
            exit;
        }

        $check->close();
    }
}

$pageTitle = 'Create Account | BrewLeaf';
$pageDescription = 'Create a free BrewLeaf account to track orders, save favorites, and leave reviews.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Registration form -->
<section class="section container page-narrow-sm">

    <h1>Create Your Account</h1>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" data-validate>

        <div class="form-row">
            <label for="full_name">Full Name</label>
            <input 
                type="text" 
                id="full_name" 
                name="full_name" 
                value="<?= h($_POST['full_name'] ?? '') ?>" 
                required
            >
        </div>

        <div class="form-row">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?= h($_POST['username'] ?? '') ?>" 
                required
            >
        </div>

        <div class="form-row">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= h($_POST['email'] ?? '') ?>" 
                required
            >
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                minlength="8"
            >

            <p class="form-hint">
                At least 8 characters.
            </p>
        </div>

        <div class="form-row">
            <label for="password_confirm">Confirm Password</label>
            <input 
                type="password" 
                id="password_confirm" 
                name="password_confirm" 
                required 
                minlength="8"
            >
        </div>

        <button type="submit" class="btn btn-accent btn-block">
            Create Account
        </button>

    </form>

    <p class="mt-md">
        Already have an account?
        <a href="login.php">Log in</a>.
    </p>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
/**
 * register.php -- New customer account creation.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $fullName === '' || $password === '') {
        $error = 'Please fill in every field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $check->bind_param('ss', $username, $email);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $error = 'That username or email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $conn->prepare('INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, "customer", "active")');
            $ins->bind_param('ssss', $username, $email, $hash, $fullName);
            $ins->execute();
            $ins->close();

            attempt_login($conn, $username, $password);
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

<section class="section container page-narrow-sm">
  <h1>Create Your Account</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

  <form method="post" action="register.php" data-validate>
    <div class="form-row">
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" value="<?= h($_POST['full_name'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= h($_POST['username'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="8">
      <p class="form-hint">At least 8 characters.</p>
    </div>
    <div class="form-row">
      <label for="password_confirm">Confirm Password</label>
      <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
    </div>
    <button type="submit" class="btn btn-accent btn-block">Create Account</button>
  </form>
  <p class="mt-md">Already have an account? <a href="login.php">Log in</a>.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

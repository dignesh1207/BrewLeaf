<?php
/**
 * login.php -- Customer / admin login (single form; role determines
 * where we redirect afterwards).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'profile.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = attempt_login($conn, $identifier, $password);

    if ($user) {
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : urldecode($redirect)));
        exit;
    }
    $error = 'Invalid credentials, or this account has been disabled. Please try again.';
}

$pageTitle = 'Log In | BrewLeaf';
$pageDescription = 'Log in to your BrewLeaf account.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section container" style="max-width:420px;">
  <h1>Log In</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

  <form method="post" action="login.php<?= $redirect !== 'profile.php' ? '?redirect=' . urlencode($redirect) : '' ?>" data-validate>
    <div class="form-row">
      <label for="identifier">Username or Email</label>
      <input type="text" id="identifier" name="identifier" required autofocus>
    </div>
    <div class="form-row">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-accent" style="width:100%;">Log In</button>
  </form>
  <p style="margin-top:1rem;">New here? <a href="register.php">Create an account</a>.</p>
  <p class="form-hint">Demo admin: <code>admin</code> / <code>Admin123!</code> &middot; Demo customer: <code>jsmith</code> / <code>Admin123!</code></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

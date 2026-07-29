<?php
// login.php -- one form for both customers and admins, role decides where they go after
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';
// require_login() sends people here with ?redirect= set to whatever page they
// wanted originally, so we know where to send them back after they log in
$redirect = $_GET['redirect'] ?? 'profile.php';

// if the user is already logged in, we don't want to show them the login form again. Instead, we redirect them to their profile page or the page they were trying to access before being prompted to log in.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = attempt_login($conn, $identifier, $password);

    if ($user) {
        // admins go to the dashboard, everyone else goes back to $redirect
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : urldecode($redirect)));
        exit;
    }
    // keeping this vague on purpose so we're not telling people if the username exists
    $error = 'Invalid credentials, or this account has been disabled. Please try again.';
}

$pageTitle = 'Log In | BrewLeaf';
$pageDescription = 'Log in to your BrewLeaf account.';
$pageRobots = 'noindex, nofollow'; // no reason for this to show up in search results
require_once __DIR__ . '/includes/header.php';
?>
<!--HTML for the login page, including a form for username/email and password, and links to register or reset password -->
<section class="section container page-narrow-sm">
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
    <button type="submit" class="btn btn-accent btn-block">Log In</button>
  </form>
  <p class="mt-md">New here? <a href="register.php">Create an account</a>.</p>
  <p class="form-hint">Demo admin: <code>admin</code> / <code>Admin123!</code> &middot; Demo customer: <code>jsmith</code> / <code>Admin123!</code></p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

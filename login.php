<?php
// login.php
// Handles login for both customers and admins.
// After login, users are redirected based on their role.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If the user is already logged in, send them to their profile.
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error = '';

// If a user was trying to access a protected page,
// they will be sent back there after logging in.
$redirect = $_GET['redirect'] ?? 'profile.php';


// Check login details when the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = attempt_login($conn, $identifier, $password);

    if ($user) {

        // Admins go to the admin dashboard.
        // Customers go back to the page they originally wanted.
        header(
            'Location: ' .
            ($user['role'] === 'admin'
                ? 'admin/dashboard.php'
                : urldecode($redirect))
        );

        exit;

    } else {

        // Keep the message general so we don't reveal
        // whether a username exists or not.
        $error = 'Invalid credentials, or this account has been disabled. Please try again.';
    }
}


$pageTitle = 'Log In | BrewLeaf';
$pageDescription = 'Log in to your BrewLeaf account.';
$pageRobots = 'noindex, nofollow';

// Login pages do not need to appear in search engines.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Login page -->

<section class="section container page-narrow-sm">

  <h1>Log In</h1>

  <?php if ($error): ?>

    <div class="alert alert-error">
      <?= h($error) ?>
    </div>

  <?php endif; ?>


  <form method="post" action="login.php<?= $redirect !== 'profile.php' ? '?redirect=' . urlencode($redirect) : '' ?>" data-validate>


    <div class="form-row">

      <label for="identifier">
        Username or Email
      </label>

      <input
        type="text"
        id="identifier"
        name="identifier"
        required
        autofocus>

    </div>


    <div class="form-row">

      <label for="password">
        Password
      </label>

      <input
        type="password"
        id="password"
        name="password"
        required>

    </div>


    <button type="submit" class="btn btn-accent btn-block">
      Log In
    </button>


  </form>


  <p class="mt-md">
    New here?
    <a href="register.php">Create an account</a>.
  </p>


  <p class="mt-sm">
    <a href="contact.php">
      Forgot your password? Please contact admin
    </a>
  </p>


  <p class="mt-sm">
    <a href="index.php">
      Back to home
    </a>
  </p>


</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
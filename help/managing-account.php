<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Managing Your Account | BrewLeaf Help';
$pageDescription = 'How to update your profile and view order history on BrewLeaf.';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section container" style="max-width:760px;">
  <p><a href="index.php">&larr; Back to Help</a></p>
  <h1>Managing Your Account</h1>

  <h2>Updating Your Profile</h2>
  <p>Go to <strong>Hi, [Your Name]</strong> in the header (or visit <code>profile.php</code>
  directly) to update your full name or email address. Your username cannot be changed.</p>

  <h2>Viewing Order History</h2>
  <p>The same page lists every order you've placed, with its date, current status, and total.
  Statuses are updated by BrewLeaf staff as your order ships.</p>

  <h2>Leaving Reviews</h2>
  <p>Visit any product you've purchased and scroll to <strong>Customer Reviews</strong> to leave a
  star rating and comment. You must be logged in to submit a review.</p>

  <h2>Logging Out</h2>
  <p>Click <strong>Log out</strong> in the header at any time to end your session.</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

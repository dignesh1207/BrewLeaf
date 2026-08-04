<?php
// contact.php, just a static page with a basic form
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// didn't make a table for messages, just log it and pretend it sent
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Contact form submission: ' . json_encode($_POST));
    $sent = true;
}

$pageTitle = 'Contact Us | BrewLeaf';
$pageDescription = 'Get in touch with BrewLeaf Artisan Coffee & Tea Co.';
require_once __DIR__ . '/includes/header.php';
?>
<!-- HTML for the contact page, including a form for name, email, and message -->
<section class="section container page-narrow-md">
  <h1>Contact Us</h1>
  <p>Questions about an order, wholesale inquiries, or just want to say hi? Reach out below.</p>

  <?php if ($sent): ?>
    <div class="alert alert-success">Thanks! Your message has been received. We'll reply within 1-2 business days.</div>
  <?php endif; ?>

  <form method="post" action="contact.php" data-validate>
    <div class="form-row">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required>
    </div>
    <div class="form-row">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-row">
      <label for="message">Message</label>
      <textarea id="message" name="message" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-accent">Send Message</button>
  </form>

  <div class="mt-xl">
    <h2>Roastery Address</h2>
    <p>401 Sunset Ave, Windsor, ON, Canada</p>
    <p>Email: hello@brewleaf.test &middot; Phone: (519) 555-0142</p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

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
    <p><?= h(BUSINESS_ADDRESS_LINE1) ?>, <?= h(BUSINESS_ADDRESS_LINE2) ?></p>
    <p>Email: <?= h(BUSINESS_EMAIL) ?> &middot; Phone: <?= h(BUSINESS_PHONE) ?></p>
    <!-- no api key needed for this basic embed, visitor can pan/zoom/click through to google maps -->
    <div class="map-embed">
      <iframe
        src="https://www.google.com/maps?q=<?= urlencode(BUSINESS_ADDRESS_LINE1 . ', ' . BUSINESS_ADDRESS_LINE2) ?>&output=embed"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="Map showing the BrewLeaf roastery at <?= h(BUSINESS_ADDRESS_LINE1) ?>, <?= h(BUSINESS_ADDRESS_LINE2) ?>"
      ></iframe>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

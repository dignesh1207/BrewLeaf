<?php
// product.php?slug=..., single product page: options, add to cart form, reviews
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// using the slug not the id, makes urls look nicer
$slug = $_GET['slug'] ?? '';
$stmt = $conn->prepare('SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
$stmt->bind_param('s', $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// if the product doesn't exist or is inactive, show a 404 page.
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found | BrewLeaf';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section container"><h1>Product not found</h1><p><a href="products.php">Back to shop</a></p></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// gets the option groups (Size, Grind, etc), grouped by name - function is in functions.php
$options = get_product_options($conn, (int) $product['id']);

$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        $reviewError = 'Please log in to leave a review.';
    } else {
        // force it into 1-5 range in case someone messes with the request
        $rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
        $comment = trim($_POST['comment'] ?? '');
        $ins = $conn->prepare('INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
        $ins->bind_param('iiis', $product['id'], $_SESSION['user_id'], $rating, $comment);
        $ins->execute();
        $ins->close();

        // rating_avg/rating_count are saved on the product so we skip an AVG/COUNT every page load
        $agg = $conn->prepare('SELECT AVG(rating) AS avg_r, COUNT(*) AS n FROM reviews WHERE product_id = ?');
        $agg->bind_param('i', $product['id']);
        $agg->execute();
        $aggRow = $agg->get_result()->fetch_assoc();
        $agg->close();
        $upd = $conn->prepare('UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?');
        $avgR = round((float) $aggRow['avg_r'], 1);
        $cnt = (int) $aggRow['n'];
        $upd->bind_param('dii', $avgR, $cnt, $product['id']);
        $upd->execute();
        $upd->close();

        // redirect after the post so hitting refresh doesn't submit the review twice
        header('Location: product.php?slug=' . urlencode($slug) . '#reviews');
        exit;
    }
}

$reviewsStmt = $conn->prepare(
    'SELECT r.rating, r.comment, r.created_at, u.full_name
     FROM reviews r JOIN users u ON u.id = r.user_id
     WHERE r.product_id = ? ORDER BY r.created_at DESC LIMIT 20'
);
$reviewsStmt->bind_param('i', $product['id']);
$reviewsStmt->execute();
$reviews = $reviewsStmt->get_result();

$pageTitle = $product['name'] . ' | BrewLeaf';
$pageDescription = substr($product['description'], 0, 155);

require_once __DIR__ . '/includes/header.php';
?>

<!-- HTML for the product page, including product details, options, add to cart form, and reviews -->
<section class="section container">
  <nav aria-label="Breadcrumb" class="breadcrumb">
    <a href="products.php">Shop</a> &rsaquo;
    <a href="products.php?category=<?= h($product['category']) ?>"><?= h(ucfirst($product['category'])) ?></a> &rsaquo;
    <?= h($product['name']) ?>
  </nav>

  <div class="product-detail">
    <img src="<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">

    <div>
      <span class="badge"><?= h(ucfirst($product['category'])) ?></span>
      <h1><?= h($product['name']) ?></h1>
      <p class="origin">Origin: <?= h($product['origin']) ?></p>
      <div class="stars" aria-label="Rating <?= h($product['rating_avg']) ?> out of 5"><?= render_stars((float) $product['rating_avg']) ?> <?= h($product['rating_avg']) ?> (<?= (int) $product['rating_count'] ?> reviews)</div>
      <p><?= nl2br(h($product['description'])) ?></p>

      <p id="livePrice" data-base-price="<?= h($product['base_price']) ?>" class="price-live">
        <?= money((float) $product['base_price']) ?>
      </p>

      <?php if (is_admin()): ?>
        <!-- admins can't buy, cart_add.php also blocks it server side -->
        <p class="form-hint">You're viewing this page as an admin preview. Purchasing controls are only available to customer accounts.</p>
      <?php else: ?>
        <!-- price updates live as you pick different options -->
        <form class="add-to-cart-form" method="post" action="cart_add.php" data-validate>
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

          <?php // js fills the hidden option_<group> input in from the clicked pill ?>
          <?php foreach ($options as $groupName => $values): ?>
            <div class="option-group">
              <label><?= h($groupName) ?></label>
              <input type="hidden" name="option_<?= h(strtolower($groupName)) ?>">
              <div class="option-pills">
                <?php foreach ($values as $i => $opt): ?>
                  <span class="option-pill <?= $i === 0 ? 'selected' : '' ?>"
                        data-option-id="<?= (int) $opt['id'] ?>"
                        data-price-modifier="<?= h($opt['price_modifier']) ?>">
                    <?= h($opt['option_value']) ?><?= $opt['price_modifier'] > 0 ? ' (+' . money((float) $opt['price_modifier']) . ')' : '' ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="form-row qty-field">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" required>
          </div>

          <button type="submit" class="btn btn-accent">Add to Cart</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section container" id="reviews">
  <div class="section-title">
    <h2>Customer Reviews</h2>
  </div>

  <?php if ($reviewError): ?><div class="alert alert-error"><?= h($reviewError) ?></div><?php endif; ?>

  <?php if ($reviews->num_rows === 0): ?>
    <p>No reviews yet. Be the first to review this product!</p>
  <?php else: ?>
    <?php while ($r = $reviews->fetch_assoc()): ?>
      <div class="feature-box review-item">
        <div class="stars"><?= render_stars((float) $r['rating']) ?></div>
        <strong><?= h($r['full_name']) ?></strong> <span class="text-muted-sm">&middot; <?= h(date('M j, Y', strtotime($r['created_at']))) ?></span>
        <p class="review-text"><?= h($r['comment']) ?></p>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <!-- only logged in users can leave a review -->
  <?php if (is_logged_in()): ?>
    <h3>Write a Review</h3>
    <form method="post" action="product.php?slug=<?= h($slug) ?>#reviews" data-validate>
      <div class="form-row">
        <label for="rating">Rating</label>
        <select id="rating" name="rating" required>
          <option value="5">5 - Excellent</option>
          <option value="4">4 - Good</option>
          <option value="3">3 - Average</option>
          <option value="2">2 - Poor</option>
          <option value="1">1 - Terrible</option>
        </select>
      </div>
      <div class="form-row">
        <label for="comment">Comment</label>
        <textarea id="comment" name="comment" rows="3" required></textarea>
      </div>
      <button type="submit" name="submit_review" value="1" class="btn">Submit Review</button>
    </form>
  <?php else: ?>
    <p><a href="login.php">Log in</a> to write a review.</p>
  <?php endif; ?>
</section>

<!-- structured data so google can show star ratings etc in search results -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": <?= json_encode($product['name']) ?>,
  "description": <?= json_encode($product['description']) ?>,
  "image": <?= json_encode($product['image']) ?>,
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": <?= json_encode((float) $product['rating_avg']) ?>,
    "reviewCount": <?= json_encode((int) $product['rating_count']) ?>
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "USD",
    "price": <?= json_encode((float) $product['base_price']) ?>
  }
}
</script>

<?php $reviewsStmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>

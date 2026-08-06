<?php
// product.php
// Shows a single product page.
// Handles product details, customization options, cart adding, and reviews.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// Get product using the slug from the URL.
// Slugs make URLs easier to read compared to using product IDs.
$slug = $_GET['slug'] ?? '';

$stmt = $conn->prepare(
    'SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1'
);

$stmt->bind_param('s', $slug);
$stmt->execute();

$product = $stmt->get_result()->fetch_assoc();

$stmt->close();


// If product does not exist, show a 404 page.
if (!$product) {

    http_response_code(404);

    $pageTitle = 'Product Not Found | BrewLeaf';

    require_once __DIR__ . '/includes/header.php';

    echo '<section class="section container">
            <h1>Product not found</h1>
            <p><a href="products.php">Back to shop</a></p>
          </section>';

    require_once __DIR__ . '/includes/footer.php';

    exit;
}


// Load product options like size, grind type, etc.
$options = get_product_options($conn, (int) $product['id']);

$reviewError = '';


// Handle review submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {


    // User must login before writing a review.
    if (!is_logged_in()) {

        $reviewError = 'Please log in to leave a review.';

    } else {


        // Keep rating between 1 and 5.
        $rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));

        $comment = trim($_POST['comment'] ?? '');


        // Save the new review.
        $ins = $conn->prepare(
            'INSERT INTO reviews (product_id, user_id, rating, comment)
             VALUES (?, ?, ?, ?)'
        );

        $ins->bind_param(
            'iiis',
            $product['id'],
            $_SESSION['user_id'],
            $rating,
            $comment
        );

        $ins->execute();

        $ins->close();



        // Update product rating information.
        // This avoids calculating AVG and COUNT every time the page loads.
        $agg = $conn->prepare(
            'SELECT AVG(rating) AS avg_r, COUNT(*) AS n
             FROM reviews
             WHERE product_id = ?'
        );

        $agg->bind_param('i', $product['id']);

        $agg->execute();

        $aggRow = $agg->get_result()->fetch_assoc();

        $agg->close();



        $upd = $conn->prepare(
            'UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?'
        );


        $avgR = round((float) $aggRow['avg_r'], 1);

        $cnt = (int) $aggRow['n'];


        $upd->bind_param(
            'dii',
            $avgR,
            $cnt,
            $product['id']
        );


        $upd->execute();

        $upd->close();



        // Refresh page after submitting to prevent duplicate reviews on refresh.
        header(
            'Location: product.php?slug=' . urlencode($slug) . '#reviews'
        );

        exit;
    }
}



// Get latest reviews for this product.
$reviewsStmt = $conn->prepare(
    'SELECT r.rating, r.comment, r.created_at, u.full_name
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.product_id = ?
     ORDER BY r.created_at DESC
     LIMIT 20'
);


$reviewsStmt->bind_param('i', $product['id']);

$reviewsStmt->execute();

$reviews = $reviewsStmt->get_result();



$pageTitle = $product['name'] . ' | BrewLeaf';

$pageDescription = substr($product['description'], 0, 155);

$pageImage = '/' . $product['image'];


require_once __DIR__ . '/includes/header.php';
?>


<!-- Product details section -->

<section class="section container">


<nav aria-label="Breadcrumb" class="breadcrumb">

  <a href="products.php">Shop</a> &rsaquo;

  <a href="products.php?category=<?= h($product['category']) ?>">
    <?= h(ucfirst($product['category'])) ?>
  </a>

  &rsaquo;

  <?= h($product['name']) ?>

</nav>



<div class="product-detail">


<img src="<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">



<div>

<span class="badge">
<?= h(ucfirst($product['category'])) ?>
</span>


<h1>
<?= h($product['name']) ?>
</h1>


<p class="origin">
Origin: <?= h($product['origin']) ?>
</p>


<div class="stars">

<?= render_stars((float) $product['rating_avg']) ?>

<?= h($product['rating_avg']) ?>

(<?= (int) $product['rating_count'] ?> reviews)

</div>


<p>
<?= nl2br(h($product['description'])) ?>
</p>



<p id="livePrice"
   data-base-price="<?= h($product['base_price']) ?>"
   class="price-live">

<?= money((float) $product['base_price']) ?>

</p>



<?php if (is_admin()): ?>

<!-- Admins can view products but cannot purchase -->

<p class="form-hint">
You're viewing this page as an admin preview.
Purchasing is only available for customer accounts.
</p>


<?php else: ?>


<!-- Add product to cart form -->

<form class="add-to-cart-form"
      method="post"
      action="cart_add.php"
      data-validate>


<input type="hidden"
       name="product_id"
       value="<?= (int) $product['id'] ?>">



<?php foreach ($options as $groupName => $values): ?>


<div class="option-group">

<label>
<?= h($groupName) ?>
</label>


<input type="hidden"
       name="option_<?= h(strtolower($groupName)) ?>">



<div class="option-pills">


<?php foreach ($values as $i => $opt): ?>


<span class="option-pill <?= $i === 0 ? 'selected' : '' ?>"
      data-option-id="<?= (int) $opt['id'] ?>"
      data-price-modifier="<?= h($opt['price_modifier']) ?>">


<?= h($opt['option_value']) ?>


<?php if ($opt['price_modifier'] > 0): ?>

(+<?= money((float) $opt['price_modifier']) ?>)

<?php endif; ?>


</span>


<?php endforeach; ?>


</div>


</div>


<?php endforeach; ?>



<div class="form-row qty-field">

<label for="quantity">
Quantity
</label>


<input type="number"
       id="quantity"
       name="quantity"
       value="1"
       min="1"
       required>

</div>



<button type="submit" class="btn btn-accent">
Add to Cart
</button>


</form>


<?php endif; ?>


</div>


</div>


</section>
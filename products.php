<?php
/**
 * products.php -- Dynamic product listing with search, category filter,
 * and sort. All query building uses prepared statements (safe from SQL
 * injection) even though every clause is optional.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$category = $_GET['category'] ?? '';
$search   = trim($_GET['q'] ?? '');
$sort     = $_GET['sort'] ?? 'popular';

$where = ['is_active = 1'];
$params = [];
$types = '';

if (in_array($category, ['coffee', 'tea'], true)) {
    $where[] = 'category = ?';
    $params[] = $category;
    $types .= 's';
}
if ($search !== '') {
    $where[] = '(name LIKE ? OR origin LIKE ? OR description LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}

$orderBy = match ($sort) {
    'price_low'  => 'base_price ASC',
    'price_high' => 'base_price DESC',
    'rating'     => 'rating_avg DESC',
    'name'       => 'name ASC',
    default      => 'rating_count DESC, rating_avg DESC',
};

$sql = 'SELECT id, name, slug, category, origin, base_price, image, rating_avg, rating_count
        FROM products WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy;

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
$resultCount = $products->num_rows;

$pageTitle = ($category ? ucfirst($category) . ' — ' : '') . 'Shop All Products | BrewLeaf';
$pageDescription = 'Browse our full catalogue of artisan coffee and specialty tea. Filter by category, search by origin, and sort by price or rating.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="section container">
  <div class="section-title">
    <h1><?= $category ? h(ucfirst($category)) : 'Shop All Products' ?></h1>
    <p><?= (int) $resultCount ?> product<?= $resultCount === 1 ? '' : 's' ?> found</p>
  </div>

  <form class="filter-bar" method="get" action="products.php" role="search" aria-label="Product filters">
    <div class="form-row">
      <label for="q">Search</label>
      <input type="text" id="q" name="q" value="<?= h($search) ?>" placeholder="e.g. Ethiopian, jasmine...">
    </div>
    <div class="form-row">
      <label for="category">Category</label>
      <select id="category" name="category">
        <option value="">All</option>
        <option value="coffee" <?= $category === 'coffee' ? 'selected' : '' ?>>Coffee</option>
        <option value="tea" <?= $category === 'tea' ? 'selected' : '' ?>>Tea</option>
      </select>
    </div>
    <div class="form-row">
      <label for="sort">Sort by</label>
      <select id="sort" name="sort">
        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
      </select>
    </div>
    <div class="form-row" style="flex:0;">
      <button type="submit" class="btn">Apply</button>
    </div>
  </form>

  <?php if ($resultCount === 0): ?>
    <p>No products matched your search. <a href="products.php">Clear filters</a>.</p>
  <?php else: ?>
    <div class="product-grid">
      <?php while ($p = $products->fetch_assoc()): ?>
        <a class="product-card" href="product.php?slug=<?= h($p['slug']) ?>" style="color:inherit;">
          <img src="<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
          <div class="body">
            <span class="badge"><?= h(ucfirst($p['category'])) ?></span>
            <h3><?= h($p['name']) ?></h3>
            <div class="origin">Origin: <?= h($p['origin']) ?></div>
            <div class="stars" aria-label="Rating <?= h($p['rating_avg']) ?> out of 5"><?= render_stars((float) $p['rating_avg']) ?> (<?= (int) $p['rating_count'] ?>)</div>
            <div class="price"><?= money((float) $p['base_price']) ?></div>
          </div>
        </a>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<?php $stmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>

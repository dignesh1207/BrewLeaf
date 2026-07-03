<?php
/**
 * index.php -- Home page.
 * Dynamic: pulls featured products and category stats live from MySQL.
 * Includes: hero video, featured product grid, interactive category tabs,
 * a data-visualization chart (Chart.js, CDN), and an embedded map (footer).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'BrewLeaf Artisan Coffee & Tea Co. | Ethically Sourced, Small-Batch Roasted';
$pageDescription = 'Shop small-batch artisan coffee and specialty loose-leaf tea. Ethically sourced from Ethiopia, Colombia, Japan and more. Free shipping over $40.';

// Featured products: top 8 by rating.
$featured = $conn->query(
    'SELECT id, name, slug, category, origin, base_price, image, rating_avg, rating_count
     FROM products WHERE is_active = 1 ORDER BY rating_avg DESC, rating_count DESC LIMIT 8'
);

// Data for the "products per category" chart used by Chart.js below.
$catStats = $conn->query(
    "SELECT category, COUNT(*) AS n, ROUND(AVG(rating_avg),2) AS avg_rating
     FROM products WHERE is_active = 1 GROUP BY category"
);
$chartLabels = [];
$chartCounts = [];
$chartRatings = [];
while ($row = $catStats->fetch_assoc()) {
    $chartLabels[] = ucfirst($row['category']);
    $chartCounts[] = (int) $row['n'];
    $chartRatings[] = (float) $row['avg_rating'];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container">
    <h1>Small-Batch Coffee &amp; Tea, Roasted With Care</h1>
    <p>Ethically sourced beans and leaves from Ethiopia to Japan, roasted and blended in small batches every week.</p>
    <a href="products.php" class="btn btn-accent">Shop the Collection</a>
  </div>
</section>

<section class="section container">
  <div class="section-title">
    <h2>Watch How We Roast</h2>
    <p>A quick look inside the BrewLeaf roastery.</p>
  </div>
  <video controls preload="none" poster="assets/images/about-roastery.jpg" style="width:100%;max-width:720px;display:block;margin:0 auto;border-radius:10px;box-shadow:var(--shadow-md);">
    <source src="assets/videos/roasting-process.mp4" type="video/mp4">
    Your browser does not support embedded video.
  </video>
</section>

<section class="section container">
  <div class="section-title">
    <h2>Shop by Category</h2>
    <p>Use the tabs below to jump straight to coffee or tea.</p>
  </div>

  <!-- Interactive tabbed menu (no page reload) -->
  <div class="category-tabs" data-component="tabs">
    <div class="tab-buttons" role="tablist">
      <button class="tab-btn active" role="tab" aria-selected="true" data-target="tab-coffee">Coffee</button>
      <button class="tab-btn" role="tab" aria-selected="false" data-target="tab-tea">Tea</button>
    </div>
    <div class="tab-panel" id="tab-coffee">
      <p>Bold, bright, and everything in between -- ten single-origin and blended roasts, from washed Ethiopian florals to syrupy espresso blends.</p>
      <a href="products.php?category=coffee" class="btn btn-outline btn-sm">Browse Coffee</a>
    </div>
    <div class="tab-panel" id="tab-tea" hidden>
      <p>Green, black, white, oolong, and caffeine-free herbal infusions sourced from China, India, Japan, and beyond.</p>
      <a href="products.php?category=tea" class="btn btn-outline btn-sm">Browse Tea</a>
    </div>
  </div>
</section>

<section class="section" style="background:var(--color-surface);border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);">
  <div class="container">
    <div class="section-title">
      <h2>Featured Products</h2>
      <p>Our highest-rated coffees and teas this month.</p>
    </div>
    <div class="product-grid">
      <?php while ($p = $featured->fetch_assoc()): ?>
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
  </div>
</section>

<section class="section container">
  <div class="section-title">
    <h2>Catalogue at a Glance</h2>
    <p>Live data straight from our product database.</p>
  </div>
  <div class="chart-card">
    <canvas id="catalogueChart" height="90"></canvas>
  </div>
</section>

<section class="section container">
  <div class="section-title">
    <h2>Why BrewLeaf</h2>
  </div>
  <div class="feature-grid">
    <div class="feature-box"><span class="icon">&#127793;</span><h3>Ethically Sourced</h3><p>Direct-trade relationships with growers across 10+ countries.</p></div>
    <div class="feature-box"><span class="icon">&#128293;</span><h3>Small-Batch Roasted</h3><p>Roasted weekly in small batches for peak freshness.</p></div>
    <div class="feature-box"><span class="icon">&#128666;</span><h3>Fast Shipping</h3><p>Orders ship within 24 hours, free over $40.</p></div>
    <div class="feature-box"><span class="icon">&#11088;</span><h3>Loved by Customers</h3><p>4.5+ average rating across our full catalogue.</p></div>
  </div>
</section>

<!-- Chart.js from CDN, per project single-file-friendly rule (no local build step needed) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
  // Category tab switching (interactive menu, no page reload).
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
      document.querySelectorAll('.tab-panel').forEach(function (p) { p.hidden = true; });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      document.getElementById(btn.dataset.target).hidden = false;
    });
  });

  // Data visualization: products per category + average rating.
  var ctx = document.getElementById('catalogueChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
          { label: 'Products', data: <?= json_encode($chartCounts) ?>, backgroundColor: '#6f4e37' },
          { label: 'Avg. Rating (x10)', data: <?= json_encode(array_map(fn($r) => round($r * 2, 1), $chartRatings)) ?>, backgroundColor: '#c98a3b' }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
</script>

<style>
.category-tabs { max-width: 640px; margin: 0 auto; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius); padding: 1.5rem; }
.tab-buttons { display: flex; gap: .5rem; margin-bottom: 1rem; }
.tab-btn { flex: 1; padding: .6rem; border: 1px solid var(--color-border); background: var(--color-bg); border-radius: 8px; font-weight: 600; }
.tab-btn.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

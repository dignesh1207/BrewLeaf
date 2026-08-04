<?php
// admin/products.php - product list w/ search, filters, pagination, edit/delete
// the actual add/edit form is a separate page, product-edit.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
// non-admins get sent away
require_admin();

// handle the delete form submission, which is just a POST with a product id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    $del = $conn->prepare('DELETE FROM products WHERE id = ?');
    $del->bind_param('i', $id);
    $del->execute();
    $del->close();
    header('Location: products.php?deleted=1');
    exit;
}

$search   = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$status   = $_GET['status'] ?? '';
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 10;

// $where, $params and $types all build up together so they stay in sync
$where = [];
$params = [];
$types = '';

// only add a search filter if the user actually typed something in the search box
if ($search !== '') {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
// only allow the category values below, so someone can't put random stuff in the url
if (in_array($category, ['coffee', 'tea'], true)) {
    $where[] = 'category = ?';
    $params[] = $category;
    $types .= 's';
}
if ($status === 'active') {
    $where[] = 'is_active = 1';
} elseif ($status === 'hidden') {
    $where[] = 'is_active = 0';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// count total products matching the filters, so we can work out total pages
$countStmt = $conn->prepare("SELECT COUNT(*) AS n FROM products $whereSql");
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['n'];
$countStmt->close();

// total pages, current page, and the offset for the LIMIT clause
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare(
    "SELECT id, name, category, base_price, image, is_active, rating_avg
     FROM products $whereSql ORDER BY id LIMIT ? OFFSET ?"
);
$listParams = $params;
$listParams[] = $perPage;
$listParams[] = $offset;
$stmt->bind_param($types . 'ii', ...$listParams);
$stmt->execute();
$products = $stmt->get_result();

// makes the prev/next page links, keeps whatever search/filter was already set
function admin_products_page_url(int $page, string $search, string $category, string $status): string
{
    $qs = ['page' => $page];
    if ($search !== '') {
        $qs['q'] = $search;
    }
    if ($category !== '') {
        $qs['category'] = $category;
    }
    if ($status !== '') {
        $qs['status'] = $status;
    }
    return 'products.php?' . http_build_query($qs);
}

$pageTitle = 'Manage Products | BrewLeaf Admin';

require_once __DIR__ . '/../includes/header.php';
$adminActive = 'products';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<!-- HTML for the product list page -->
<section class="section">
<div class="container">
  <div class="header-row">
    <h1>Manage Products</h1>
    <a href="product-edit.php" class="btn btn-accent">+ Add New Product</a>
  </div>

  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Product deleted.</div><?php endif; ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Product saved.</div><?php endif; ?>

  <form class="filter-bar" method="get" action="products.php" role="search" aria-label="Product filters">
    <div class="form-row">
      <label for="q">Search</label>
      <input type="text" id="q" name="q" value="<?= h($search) ?>" placeholder="Search by name...">
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
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="">All</option>
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Hidden</option>
      </select>
    </div>
    <div class="form-row form-row-auto">
      <button type="submit" class="btn">Apply</button>
    </div>
  </form>

  <p class="text-muted-sm mb-md">
    <?= (int) $total ?> product<?= $total === 1 ? '' : 's' ?> found
    <?= ($search !== '' || $category !== '' || $status !== '') ? '&mdash; <a href="products.php">Clear filters</a>' : '' ?>
  </p>

  <?php if ($total === 0): ?>
    <p>No products match your filters. <a href="products.php">Clear filters</a> to see everything.</p>
  <?php else: ?>
    <div class="table-scroll">
      <table class="product-table">
        <caption class="sr-only">All products, with edit and delete actions</caption>
        <colgroup>
          <col class="col-thumb"><col class="col-name"><col class="col-category">
          <col class="col-price"><col class="col-rating"><col class="col-status"><col class="col-actions">
        </colgroup>
        <thead>
          <tr>
            <th scope="col"><span class="sr-only">Photo</span></th>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Price</th>
            <th scope="col">Rating</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = $products->fetch_assoc()): ?>
            <tr>
              <td>
                <img
                  src="<?= h(SITE_BASE_URL . '/' . $p['image']) ?>"
                  alt="<?= h($p['name']) ?>"
                  class="product-thumb"
                  loading="lazy"
                  onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 72 72%27%3E%3Crect width=%2772%27 height=%2772%27 rx=%2710%27 fill=%27%23e7ddd2%27/%3E%3Cpath d=%27M22 30h22v14a11 11 0 0 1-11 11 11 11 0 0 1-11-11z%27 fill=%27none%27 stroke=%27%236b5f57%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M44 33h4a6 6 0 0 1 0 12h-4%27 fill=%27none%27 stroke=%27%236b5f57%27 stroke-width=%272.5%27/%3E%3C/svg%3E';"
                >
              </td>
              <td class="col-name-cell" title="<?= h($p['name']) ?>"><?= h($p['name']) ?></td>
              <td><?= h(ucfirst($p['category'])) ?></td>
              <td><?= money((float) $p['base_price']) ?></td>
              <td><?= h($p['rating_avg']) ?> &#9733;</td>
              <td><span class="status-pill status-<?= $p['is_active'] ? 'online' : 'offline' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
              <td class="actions-td">
                <div class="actions-cell">
                  <a class="btn btn-sm btn-outline" href="product-edit.php?id=<?= (int) $p['id'] ?>" aria-label="Edit <?= h($p['name']) ?>">Edit</a>
                  <form method="post" action="products.php" data-confirm="Delete &quot;<?= h($p['name']) ?>&quot; permanently? This cannot be undone.">
                    <input type="hidden" name="delete_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" aria-label="Delete <?= h($p['name']) ?>">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="pager" aria-label="Product list pages">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-outline" href="<?= h(admin_products_page_url($page - 1, $search, $category, $status)) ?>">&larr; Prev</a>
        <?php else: ?>
          <span class="btn btn-sm btn-outline" aria-disabled="true">&larr; Prev</span>
        <?php endif; ?>

        <span class="text-muted-sm">Page <?= (int) $page ?> of <?= (int) $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-outline" href="<?= h(admin_products_page_url($page + 1, $search, $category, $status)) ?>">Next &rarr;</a>
        <?php else: ?>
          <span class="btn btn-sm btn-outline" aria-disabled="true">Next &rarr;</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
</section>

<?php $stmt->close(); require_once __DIR__ . '/../includes/footer.php'; ?>

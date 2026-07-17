<?php
/**
 * admin/products.php -- List all products with edit/delete actions.
 * Actual create/edit form lives in admin/product-edit.php to keep this
 * page focused on the catalogue overview.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Handle delete.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    $del = $conn->prepare('DELETE FROM products WHERE id = ?');
    $del->bind_param('i', $id);
    $del->execute();
    $del->close();
    header('Location: products.php?deleted=1');
    exit;
}

$products = $conn->query('SELECT id, name, category, base_price, image, is_active, rating_avg FROM products ORDER BY id');

$pageTitle = 'Manage Products | BrewLeaf Admin';
require_once __DIR__ . '/../includes/header.php';
$adminActive = 'products';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<section class="section container">
  <div class="header-row">
    <h1>Manage Products</h1>
    <a href="product-edit.php" class="btn btn-accent">+ Add New Product</a>
  </div>

  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Product deleted.</div><?php endif; ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Product saved.</div><?php endif; ?>

  <table>
    <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <td><img src="<?= h($p['image']) ?>" alt="" class="table-thumb"></td>
          <td><?= h($p['name']) ?></td>
          <td><?= h(ucfirst($p['category'])) ?></td>
          <td><?= money((float) $p['base_price']) ?></td>
          <td><?= h($p['rating_avg']) ?> &#9733;</td>
          <td><span class="status-pill status-<?= $p['is_active'] ? 'online' : 'offline' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
          <td class="table-actions">
            <a class="btn btn-sm btn-outline" href="product-edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
            <form method="post" action="products.php" data-confirm="Delete this product permanently?">
              <input type="hidden" name="delete_id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

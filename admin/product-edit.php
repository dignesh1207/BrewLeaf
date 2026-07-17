<?php
/**
 * admin/product-edit.php -- Add a new product or edit an existing one
 * (?id=). Also manages that product's option rows (Size, Grind, etc.)
 * inline, since options can't exist without a parent product.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = ['name' => '', 'slug' => '', 'category' => 'coffee', 'origin' => '', 'description' => '', 'base_price' => '', 'image' => 'assets/images/product-01.jpg', 'is_active' => 1];
$error = '';

if ($id) {
    $s = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $s->bind_param('i', $id);
    $s->execute();
    $found = $s->get_result()->fetch_assoc();
    $s->close();
    if ($found) $product = $found;
}

// Save product core fields.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = trim($_POST['name'] ?? '');
    $category = in_array($_POST['category'] ?? '', ['coffee', 'tea'], true) ? $_POST['category'] : 'coffee';
    $origin = trim($_POST['origin'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $basePrice = (float) ($_POST['base_price'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $slug = $id ? $product['slug'] : preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
    $slug = trim($slug, '-');

    if ($name === '' || $basePrice <= 0) {
        $error = 'Name and a positive price are required.';
    } elseif ($id) {
        $upd = $conn->prepare('UPDATE products SET name=?, category=?, origin=?, description=?, base_price=?, image=?, is_active=? WHERE id=?');
        $upd->bind_param('ssssdsii', $name, $category, $origin, $description, $basePrice, $image, $isActive, $id);
        $upd->execute();
        $upd->close();
        header('Location: products.php?saved=1');
        exit;
    } else {
        $ins = $conn->prepare('INSERT INTO products (name, slug, category, origin, description, base_price, image, is_active) VALUES (?,?,?,?,?,?,?,?)');
        $ins->bind_param('sssssdsi', $name, $slug, $category, $origin, $description, $basePrice, $image, $isActive);
        $ins->execute();
        $id = $conn->insert_id;
        $ins->close();
        header('Location: product-edit.php?id=' . $id . '&saved=1');
        exit;
    }
}

// Add a new option row.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option']) && $id) {
    $group = trim($_POST['option_group'] ?? '');
    $value = trim($_POST['option_value'] ?? '');
    $modifier = (float) ($_POST['price_modifier'] ?? 0);
    if ($group !== '' && $value !== '') {
        $ins = $conn->prepare('INSERT INTO product_options (product_id, option_group, option_value, price_modifier) VALUES (?,?,?,?)');
        $ins->bind_param('issd', $id, $group, $value, $modifier);
        $ins->execute();
        $ins->close();
    }
    header('Location: product-edit.php?id=' . $id . '#options');
    exit;
}

// Delete an option row.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_option']) && $id) {
    $optId = (int) $_POST['delete_option'];
    $del = $conn->prepare('DELETE FROM product_options WHERE id = ? AND product_id = ?');
    $del->bind_param('ii', $optId, $id);
    $del->execute();
    $del->close();
    header('Location: product-edit.php?id=' . $id . '#options');
    exit;
}

$existingOptions = $id ? get_product_options($conn, $id) : [];

$pageTitle = ($id ? 'Edit' : 'Add') . ' Product | BrewLeaf Admin';
require_once __DIR__ . '/../includes/header.php';
$adminActive = 'products';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<section class="section container">
  <h1><?= $id ? 'Edit Product' : 'Add New Product' ?></h1>
  <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Saved.</div><?php endif; ?>

  <form method="post" action="product-edit.php<?= $id ? '?id=' . $id : '' ?>" data-validate>
    <div class="form-grid">
      <div class="form-row">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" value="<?= h($product['name']) ?>" required>
      </div>
      <div class="form-row">
        <label for="category">Category</label>
        <select id="category" name="category">
          <option value="coffee" <?= $product['category'] === 'coffee' ? 'selected' : '' ?>>Coffee</option>
          <option value="tea" <?= $product['category'] === 'tea' ? 'selected' : '' ?>>Tea</option>
        </select>
      </div>
      <div class="form-row">
        <label for="origin">Origin</label>
        <input type="text" id="origin" name="origin" value="<?= h($product['origin']) ?>">
      </div>
      <div class="form-row">
        <label for="base_price">Base Price ($)</label>
        <input type="number" id="base_price" name="base_price" step="0.01" min="0.01" value="<?= h($product['base_price']) ?>" required>
      </div>
      <div class="form-row">
        <label for="image">Image Path</label>
        <input type="text" id="image" name="image" value="<?= h($product['image']) ?>">
      </div>
      <div class="form-row">
        <label for="is_active">Visibility</label>
        <label class="checkbox-row">
          <input type="checkbox" id="is_active" name="is_active" class="w-auto" <?= $product['is_active'] ? 'checked' : '' ?>> Active (visible in shop)
        </label>
      </div>
    </div>
    <div class="form-row">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?= h($product['description']) ?></textarea>
    </div>
    <button type="submit" name="save_product" value="1" class="btn btn-accent">Save Product</button>
    <a href="products.php" class="btn btn-outline">Cancel</a>
  </form>

  <?php if ($id): ?>
    <h2 id="options" class="mt-xxl">Options (Size, Grind/Style, etc.)</h2>
    <p class="form-hint">Every product needs at least 2 option groups (e.g. Size + Grind) so shoppers can customize their order.</p>
    <table class="mb-lg">
      <thead><tr><th>Group</th><th>Value</th><th>Price Modifier</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($existingOptions as $group => $rows): foreach ($rows as $opt): ?>
          <tr>
            <td><?= h($group) ?></td>
            <td><?= h($opt['option_value']) ?></td>
            <td><?= h($opt['price_modifier']) >= 0 ? '+' . money((float) $opt['price_modifier']) : money((float) $opt['price_modifier']) ?></td>
            <td>
              <form method="post" action="product-edit.php?id=<?= $id ?>" data-confirm="Remove this option?">
                <input type="hidden" name="delete_option" value="<?= (int) $opt['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endforeach; ?>
      </tbody>
    </table>

    <form method="post" action="product-edit.php?id=<?= $id ?>#options" class="form-grid form-grid-end">
      <div class="form-row">
        <label for="option_group">Group Name</label>
        <input type="text" id="option_group" name="option_group" placeholder="e.g. Size">
      </div>
      <div class="form-row">
        <label for="option_value">Value</label>
        <input type="text" id="option_value" name="option_value" placeholder="e.g. 500g">
      </div>
      <div class="form-row">
        <label for="price_modifier">Price Modifier ($)</label>
        <input type="number" id="price_modifier" name="price_modifier" step="0.01" value="0.00">
      </div>
      <div class="form-row">
        <button type="submit" name="add_option" value="1" class="btn">Add Option</button>
      </div>
    </form>
  <?php else: ?>
    <p class="form-hint mt-xl">Save the product first, then you'll be able to add its size/grind options.</p>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Updating Site Content | BrewLeaf Help';
$pageDescription = 'A non-programmer guide to adding products, images, and video to BrewLeaf without touching code.';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section container" style="max-width:760px;">
  <p><a href="index.php">&larr; Back to Help</a></p>
  <h1>Updating Site Content (No Coding Required)</h1>
  <p>Everything below can be done from your web browser through the Admin area -- no code editing needed.</p>

  <h2>Add or Remove a Product</h2>
  <ol>
    <li>Log in as an admin and go to <strong>Admin &rarr; Products</strong>.</li>
    <li>Click <strong>+ Add New Product</strong>, fill in the name, category, origin, price, and description, then click <strong>Save Product</strong>.</li>
    <li>Once saved, scroll to the <strong>Options</strong> section and add at least two option groups (e.g. "Size" / "500g" and "Grind" / "Whole Bean").</li>
    <li>To remove a product, click <strong>Delete</strong> next to it on the Products list.</li>
  </ol>

  <h2>Adding a New Product Photo</h2>
  <ol>
    <li>Save your photo as a <code>.jpg</code> or <code>.png</code> file.</li>
    <li>Upload it into the <code>assets/images/</code> folder on the server (via your hosting file manager or FTP client).</li>
    <li>On the product's Edit page, set the <strong>Image Path</strong> field to <code>assets/images/your-file-name.jpg</code> and save.</li>
  </ol>

  <h2>Adding a New Video</h2>
  <ol>
    <li>Export your video as an <code>.mp4</code> file (keep it under ~20MB for fast loading).</li>
    <li>Upload it into <code>assets/videos/</code>.</li>
    <li>Update the <code>&lt;source src="..."&gt;</code> path on the relevant page (e.g. the home page video), or ask a site maintainer to point a new <code>&lt;video&gt;</code> block at it.</li>
  </ol>

  <h2>Switching the Look of the Whole Site</h2>
  <p>Go to <strong>Admin &rarr; Site Template</strong> and click any of the three theme cards
  (Regular, Autumn, Winter). The whole site updates immediately -- no file editing needed.</p>

  <h2>Need More Help?</h2>
  <p>See the <a href="admin-guide.php">Admin Guide</a> for a full walkthrough of every admin screen.</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

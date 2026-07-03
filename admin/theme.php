<?php
/**
 * admin/theme.php -- Site-wide template switcher. Writes the chosen theme
 * into site_settings.active_theme, which includes/header.php reads on
 * every page load (see get_active_theme() in includes/functions.php).
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$themes = [
    'regular' => ['Regular Roastery', 'Warm coffee browns & cream -- the year-round default.', '#6f4e37', '#4a3225'],
    'autumn'  => ['Harvest (Autumn)', 'Deep pumpkin & rust tones for fall.', '#c1521f', '#7a3210'],
    'winter'  => ['Frost (Winter)', 'Cool slate blues, crisp and minimal.', '#2b5a72', '#163647'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $chosen = $_POST['theme'];
    if (array_key_exists($chosen, $themes)) {
        $stmt = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES ("active_theme", ?)
                                 ON DUPLICATE KEY UPDATE setting_value = ?');
        $stmt->bind_param('ss', $chosen, $chosen);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: theme.php?saved=1');
    exit;
}

$pageTitle = 'Site Template | BrewLeaf Admin';
require_once __DIR__ . '/../includes/header.php';
$adminActive = 'theme';
require_once __DIR__ . '/../includes/admin-nav.php';

$active = get_active_theme($conn);
?>

<section class="section container">
  <h1>Site Template</h1>
  <p>Pick the site-wide look. This changes the theme for every visitor immediately.</p>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Template updated site-wide.</div><?php endif; ?>

  <form method="post" action="theme.php">
    <div class="theme-options">
      <?php foreach ($themes as $key => [$label, $desc, $c1, $c2]): ?>
        <label class="theme-card <?= $active === $key ? 'active' : '' ?>">
          <input type="radio" name="theme" value="<?= $key ?>" style="display:none;" <?= $active === $key ? 'checked' : '' ?> onchange="this.form.submit()">
          <div class="theme-swatch" style="background:linear-gradient(135deg, <?= $c1 ?>, <?= $c2 ?>);"></div>
          <div class="theme-label"><?= h($label) ?><br><small style="font-weight:normal;color:var(--color-text-muted);"><?= h($desc) ?></small></div>
        </label>
      <?php endforeach; ?>
    </div>
  </form>

  <p style="margin-top:1.5rem;">
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=regular" target="_blank" class="btn btn-sm btn-outline">Preview Regular</a>
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=autumn" target="_blank" class="btn btn-sm btn-outline">Preview Autumn</a>
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=winter" target="_blank" class="btn btn-sm btn-outline">Preview Winter</a>
  </p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
// admin/theme.php - lets admin switch the site theme for everyone
// saves the choice into site_settings.active_theme, header.php checks that on
// every page load (get_active_theme() in functions.php)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
// non-admins get redirected out
require_admin();

// these colors need to match the :root vars in each theme-*.css file, they're
// just here so the little preview cards below look right. if the css changes
// update these too or the preview will be wrong
$themes = [
    'white' => [
        'label' => 'Clean White',
        'desc'  => 'Bright white surfaces, near-black text, and a full-bleed photo hero -- the year-round default.',
        'bg' => '#fafaf8', 'surface' => '#ffffff', 'border' => '#eaeae6',
        'primary' => '#2a2a27', 'primary_dark' => '#141412', 'accent' => '#ffffff',
    ],
    'regular' => [
        'label' => 'Regular Roastery',
        'desc'  => 'Warm coffee browns & cream.',
        'bg' => '#faf7f2', 'surface' => '#ffffff', 'border' => '#e7ddd2',
        'primary' => '#6f4e37', 'primary_dark' => '#4a3225', 'accent' => '#c98a3b',
    ],
    'autumn' => [
        'label' => 'Harvest (Autumn)',
        'desc'  => 'Deep pumpkin & rust tones for fall.',
        'bg' => '#fbf1e6', 'surface' => '#fffaf4', 'border' => '#f0d9bd',
        'primary' => '#c1521f', 'primary_dark' => '#7a3210', 'accent' => '#e0a52b',
    ],
    'winter' => [
        'label' => 'Frost (Winter)',
        'desc'  => 'Cool slate blues, crisp and minimal.',
        'bg' => '#f3f6f8', 'surface' => '#ffffff', 'border' => '#dbe6ec',
        'primary' => '#2b5a72', 'primary_dark' => '#163647', 'accent' => '#7fb3c9',
    ],
];

// handle the form submission for changing the theme. this is just a POST with a theme key, no login needed because the admin check is done in header.php on every page load and only admins can see the theme.php page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $chosen = $_POST['theme'];
    // make sure $chosen is actually one of our theme keys and not something random
    if (array_key_exists($chosen, $themes)) {
        // ON DUPLICATE KEY UPDATE does insert-or-update in one go, so we don't
        // have to first select and check if the setting row is already there
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
      <?php foreach ($themes as $key => $t): ?>
        <label class="theme-card <?= $active === $key ? 'active' : '' ?>">
          <input type="radio" name="theme" value="<?= $key ?>" class="radio-hidden auto-submit" <?= $active === $key ? 'checked' : '' ?>>
          <!-- little fake preview of the page, colors pulled from $themes above so inline styles it is -->
          <div class="theme-swatch" style="background:<?= $t['bg'] ?>;">
            <div class="mock-header" style="background:<?= $t['surface'] ?>;border-bottom:1px solid <?= $t['border'] ?>;">
              <span class="mock-dot" style="background:<?= $t['primary'] ?>;"></span>
              <span class="mock-line" style="background:<?= $t['border'] ?>;"></span>
              <span class="mock-line" style="background:<?= $t['border'] ?>;"></span>
            </div>
            <div class="mock-hero" style="background:linear-gradient(135deg, <?= $t['primary'] ?>, <?= $t['primary_dark'] ?>);">
              <span class="mock-btn" style="background:<?= $t['accent'] ?>;"></span>
            </div>
            <div class="mock-body">
              <span class="mock-card" style="background:<?= $t['surface'] ?>;border:1px solid <?= $t['border'] ?>;"></span>
              <span class="mock-card" style="background:<?= $t['surface'] ?>;border:1px solid <?= $t['border'] ?>;"></span>
              <span class="mock-card" style="background:<?= $t['surface'] ?>;border:1px solid <?= $t['border'] ?>;"></span>
            </div>
          </div>
          <div class="theme-label"><?= h($t['label']) ?><br><small class="theme-desc"><?= h($t['desc']) ?></small></div>
        </label>
      <?php endforeach; ?>
    </div>
  </form>

  <p class="mt-lg">
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=white" target="_blank" class="btn btn-sm btn-outline">Preview White</a>
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=regular" target="_blank" class="btn btn-sm btn-outline">Preview Regular</a>
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=autumn" target="_blank" class="btn btn-sm btn-outline">Preview Autumn</a>
    <a href="<?= h(SITE_BASE_URL) ?>/index.php?preview_theme=winter" target="_blank" class="btn btn-sm btn-outline">Preview Winter</a>
  </p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

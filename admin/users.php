<?php
// admin/users.php - see all user accounts, enable/disable them
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
// Kick out non-admins away
require_admin();

// handle the delete form submission, which is just a POST with a product id.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $targetId = (int) $_POST['toggle_id'];
    if ($targetId !== (int) $_SESSION['user_id']) { // don't let an admin disable their own account
        // note to self: this one isn't a prepared statement like everywhere else,
        // just building the query string directly. it's only "safe" because $targetId
        // got cast to (int) above so nothing but a number can end up in the query.
        // should probably fix this to use bind_param like the rest of the app does.
        $conn->query('UPDATE users SET status = IF(status="active","disabled","active") WHERE id = ' . $targetId);
    }
    header('Location: users.php');
    exit;
}

$users = $conn->query('SELECT id, username, email, full_name, role, status, created_at FROM users ORDER BY created_at DESC');

$pageTitle = 'Manage Users | BrewLeaf Admin';

require_once __DIR__ . '/../includes/header.php';
$adminActive = 'users';
require_once __DIR__ . '/../includes/admin-nav.php';
?>

<!-- HTML for the user management page -->
<section class="section container">
  <h1>Manage User Accounts</h1>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
          <tr>
            <td><?= h($u['full_name']) ?></td>
            <td><?= h($u['username']) ?></td>
            <td><?= h($u['email']) ?></td>
            <td><?= h(ucfirst($u['role'])) ?></td>
            <td><span class="status-pill status-<?= $u['status'] === 'active' ? 'online' : 'offline' ?>"><?= h(ucfirst($u['status'])) ?></span></td>
            <td><?= h(date('M j, Y', strtotime($u['created_at']))) ?></td>
            <td>
              <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                <form method="post" action="users.php">
                  <input type="hidden" name="toggle_id" value="<?= (int) $u['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-danger' : '' ?>">
                    <?= $u['status'] === 'active' ? 'Disable' : 'Enable' ?>
                  </button>
                </form>
              <?php else: ?>
                <span class="form-hint">(you)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

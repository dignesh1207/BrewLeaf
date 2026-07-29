<?php
// project-docs.php -- full write-up of how this whole site works, for the course submission
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Project Documentation | BrewLeaf';
$pageDescription = 'Full technical write-up of the BrewLeaf course project: architecture, database, roles, cart, admin panel, and security.';
$pageRobots = 'noindex, nofollow'; // course documentation, not real storefront content

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container"><h1>Project Documentation</h1></div>
</section>

<section class="section container page-narrow-lg">

  <p>
    <strong>Author:</strong> Dignesh Solanki &nbsp;&middot;&nbsp;
    <strong>Student Number:</strong> 110173120 &nbsp;&middot;&nbsp;
    <strong>Course:</strong> COMP3340-91-R-2026S &mdash; World Wide Web Info System Dev
  </p>

  <h2>Overview</h2>
  <p>BrewLeaf is a browsable catalogue of coffee and tea with per-product size/grind options, a cart
  that works for guests and logged-in customers, checkout with order history, product reviews, a
  full admin back office, a public help wiki, and a live backend status monitor.</p>
  <p>It's built with plain PHP and no framework or build step -- every <code>.php</code> file is
  requested directly by the web server, talking to MySQL through raw <code>mysqli</code>. That
  deploys to any standard shared PHP host with nothing to compile.</p>

  <h2>Tech Stack</h2>
  <p><strong>Server:</strong> PHP 8, procedural style, no Composer packages.
  <strong>Data:</strong> MySQL/MariaDB via <code>mysqli</code>, prepared statements throughout.
  <strong>Client:</strong> vanilla JS, one small file per behaviour, no third-party JS libraries
  anywhere on the site. <strong>Styling:</strong> hand-written CSS on custom-property tokens, with
  four swappable theme files.</p>

  <h2>Seeded Accounts</h2>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Username</th><th>Password</th><th>Role</th></tr></thead>
      <tbody>
        <tr><td>admin</td><td><code>Admin123!</code></td><td>admin</td></tr>
        <tr><td>jsmith</td><td><code>Admin123!</code></td><td>customer</td></tr>
      </tbody>
    </table>
  </div>

  <h2>Architecture</h2>
  <p>Every request loads the same small chain: <code>config/db.php</code> (database connection),
  <code>includes/auth.php</code> (session + role helpers), <code>includes/functions.php</code>
  (shared helpers like <code>h()</code> and <code>money()</code>), the shared
  <code>includes/header.php</code>, the page's own logic and markup, then
  <code>includes/footer.php</code>. There's no router -- the URL is the file path.</p>
  <p>Folders: <code>config/</code> (db connection + base URL), <code>includes/</code> (shared
  header/footer/auth), <code>admin/</code> (6 back-office pages), <code>help/</code> (5-article
  public + admin wiki), <code>assets/css</code> and <code>assets/js</code> (one file per
  component/behaviour), and <code>sql/schema.sql</code> (full schema + seed data).</p>

  <h2>Database</h2>
  <p>9 InnoDB tables. <code>selected_options</code> on <code>cart_items</code>/<code>order_items</code>
  stores a JSON array rather than a foreign key, since one product can have multiple option groups
  (Size <em>and</em> Grind) selected on a single line.</p>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Table</th><th>Role</th></tr></thead>
      <tbody>
        <tr><td><code>users</code></td><td>Customers and admins in one table; <code>role</code> splits them, <code>status</code> lets an admin disable an account instead of deleting it.</td></tr>
        <tr><td><code>products</code></td><td>20 seeded rows (10 coffee, 10 tea); <code>rating_avg</code>/<code>rating_count</code> are cached aggregates; <code>is_active</code> is a soft-hide flag.</td></tr>
        <tr><td><code>product_options</code></td><td>One row per variant value (e.g. Size / 500g) with its own price modifier.</td></tr>
        <tr><td><code>reviews</code></td><td>Star rating (1-5) + comment, tied to one product and one user.</td></tr>
        <tr><td><code>cart_items</code></td><td>Lines for a logged-in user (<code>user_id</code>) or a guest (<code>session_id</code>).</td></tr>
        <tr><td><code>orders</code></td><td>Header row per checkout: pending &rarr; processing &rarr; shipped &rarr; delivered / cancelled.</td></tr>
        <tr><td><code>order_items</code></td><td>Snapshots product name/price/options at purchase time, so past orders stay accurate if the product changes later.</td></tr>
        <tr><td><code>site_settings</code></td><td>Key/value store; today holds one key, <code>active_theme</code>.</td></tr>
        <tr><td><code>service_status</code></td><td>Rows polled and rewritten by <code>monitor.php</code> on every visit.</td></tr>
      </tbody>
    </table>
  </div>

  <h2>Roles &amp; Access</h2>
  <p>The site separates the customer experience from the admin one, enforced <strong>server-side</strong>,
  not just by hiding buttons in the UI. Customers/guests see Home, Shop, Coffee, Tea, About, Help,
  Contact, a live cart badge, and an account menu. Admins see a different nav instead -- Storefront,
  Dashboard, Products, Orders, Customers -- with no Cart link, no cart badge, and no Add-to-Cart
  controls anywhere.</p>
  <p><code>cart.php</code>, <code>checkout.php</code>, <code>cart_add.php</code>, and
  <code>cart_update.php</code> all check <code>is_admin()</code> (reading the session role set at
  login) before doing anything, and reject admins regardless of what the client sends -- verified
  directly by sending a raw request to <code>cart_add.php</code> bypassing the UI entirely, which
  still gets rejected.</p>

  <h2>Public Pages</h2>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Page</th><th>Auth</th><th>What it does</th></tr></thead>
      <tbody>
        <tr><td><code>index.php</code></td><td>open</td><td>Home page: hero, roast video, category tabs, catalogue chart.</td></tr>
        <tr><td><code>products.php</code> / <code>product.php</code></td><td>open</td><td>Catalogue with search/filter/sort, and a product page with option pills + live price + reviews.</td></tr>
        <tr><td><code>login.php</code> / <code>register.php</code></td><td>open</td><td>Bcrypt password hashing, validation, auto-login after registration.</td></tr>
        <tr><td><code>profile.php</code></td><td>login required</td><td>Edit account details + full order history.</td></tr>
        <tr><td><code>monitor.php</code></td><td>open</td><td>Runs 6 live checks (DB ping, table existence, session) every request.</td></tr>
      </tbody>
    </table>
  </div>

  <h2>Cart &amp; Checkout</h2>
  <p>Quantity changes and removals save in the background over AJAX (<code>cart_update.php</code>),
  with a spinner and disabled buttons while the request is in flight, an error banner if it fails
  (reverting a quantity edit to its last saved value rather than showing a stale number), and a
  confirmation prompt before removing a line. Checkout wraps the whole conversion in one database
  transaction -- insert order, insert every line item, clear the cart, commit -- and rolls all of it
  back if anything fails. Card fields are collected but never validated or stored, by design (a
  school-project demo, no real payment is processed).</p>

  <h2>Admin Panel</h2>
  <div class="feature-grid">
    <div class="feature-box"><h3>Dashboard</h3><p>KPI cards, a 14-day revenue chart (zero-filled so every day shows, not just days with a sale), and a live service-status snapshot.</p></div>
    <div class="feature-box"><h3>Products</h3><p>Search, category/status filters, pagination, add/edit/delete, and inline management of each product's option rows.</p></div>
    <div class="feature-box"><h3>Orders</h3><p>Update any order's status from a whitelisted set; customers see the change instantly.</p></div>
    <div class="feature-box"><h3>Customers</h3><p>List every account, disable/enable (can't disable your own).</p></div>
  </div>

  <h2>Charts</h2>
  <p>Both charts on the site -- the home page's "Catalogue at a Glance" and the admin dashboard's
  revenue chart -- are plain HTML and CSS, no charting library and no CDN. PHP works out each bar's
  height as a percentage of the highest value and renders it as a styled <code>&lt;div&gt;</code>;
  the browser does the rest.</p>

  <h2>Theming</h2>
  <p>Four CSS files share the same variable names; whichever theme file loads last wins, so
  switching themes (White, Regular, Autumn, Winter) is a single database write from
  <code>admin/theme.php</code>, applied to every visitor immediately.</p>

  <h2>SEO</h2>
  <p>Every page sets a title and description before including the shared header. A
  <code>$pageRobots</code> variable (defaults to letting search engines index the page) is set to
  "noindex" on account/cart/admin pages -- including this page -- so they're reachable but never
  show up in search results, no <code>robots.txt</code> needed. <code>sitemap.php</code> generates
  an XML sitemap of every static page plus every active product, live from the database.</p>

  <h2>Security Model</h2>
  <div class="table-scroll">
    <table>
      <thead><tr><th>Mechanism</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td>Password storage</td><td><span class="status-pill status-online"><span class="status-dot"></span> bcrypt</span></td></tr>
        <tr><td>SQL injection defense</td><td><span class="status-pill status-online"><span class="status-dot"></span> prepared statements</span></td></tr>
        <tr><td>XSS defense</td><td><span class="status-pill status-online"><span class="status-dot"></span> h() escaping everywhere</span></td></tr>
        <tr><td>Role enforcement</td><td><span class="status-pill status-online"><span class="status-dot"></span> server-side</span></td></tr>
        <tr><td>CSRF protection</td><td><span class="status-pill status-offline"><span class="status-dot"></span> none found</span></td></tr>
        <tr><td>Session handling</td><td><span class="status-pill status-offline"><span class="status-dot"></span> no regeneration</span></td></tr>
      </tbody>
    </table>
  </div>
  <p class="form-hint">One prepared-statement exception: <code>admin/users.php</code>'s status
  toggle builds a string query, safe only because the ID is cast to an integer first.</p>

  <h2>Known Gaps</h2>
  <p>No CSRF tokens on any state-changing form. No <code>session_regenerate_id()</code> call on
  login (a session-fixation gap). <code>admin/users.php</code>'s status toggle should be switched
  to a prepared statement for consistency. No <code>LICENSE</code> file for the codebase.</p>

  <h2>Running Locally</h2>
  <p><code>mysql -u root -e "CREATE DATABASE brewleaf CHARACTER SET utf8mb4;"</code>, then
  <code>mysql -u root brewleaf &lt; sql/schema.sql</code>, then <code>php -S localhost:8000</code>.
  <code>config/db.php</code> targets <code>localhost</code> / <code>root</code> / no password /
  database <code>brewleaf</code> by default.</p>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

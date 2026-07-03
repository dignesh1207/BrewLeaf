# BrewLeaf Artisan Coffee & Tea Co. — Installation Guide

BrewLeaf is a PHP + MySQL e-commerce web app: a catalogue of coffee and tea
products with a shopping cart, checkout, user accounts, product reviews, an
admin panel (product/order/user management + switchable site templates), and
a live backend status monitor.

Plain PHP + MySQLi, no framework, no build step — every `.php` file is
requested directly by the web server, so this deploys to any standard
PHP/MySQL host (Apache or Nginx) including university shared hosting like
`myweb.cs.uwindsor.ca`.

## Requirements

- PHP 8.0+ with the `mysqli` extension enabled (standard on most shared hosts).
- MySQL 5.7+ or MariaDB 10.x.
- No Composer packages, no Node build step — nothing to compile.

## Install on a new host (step by step)

1. **Upload the files.** Copy the entire project folder to your web root
   (e.g. via the hosting control panel's File Manager, or `scp`/`sftp`/FTP).
   On `myweb.cs.uwindsor.ca` this is typically your `public_html/` directory.

2. **Create a database.** Using your host's database tool (phpMyAdmin,
   cPanel MySQL Databases, etc.) create a new database, e.g. `brewleaf`,
   and a database user with full privileges on it.

3. **Import the schema.** Upload and import `sql/schema.sql` into that
   database. This creates every table and seeds:
   - 20 products (10 coffees + 10 teas), each with 2 option groups.
   - A default admin account: username `admin`, password `Admin123!`.
   - A default customer account: username `jsmith`, password `Admin123!`.
   - Six backend services tracked by the status monitor.

   Via command line:
   ```
   mysql -u <db_user> -p <db_name> < sql/schema.sql
   ```
   Via phpMyAdmin: open the database, go to **Import**, choose
   `sql/schema.sql`, click **Go**.

4. **Configure the database connection.** Edit `config/db.php` and set:
   ```php
   define('DB_HOST', 'localhost');   // or your host-provided DB hostname
   define('DB_NAME', 'brewleaf');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('SITE_BASE_URL', 'https://myweb.cs.uwindsor.ca/~yourusername');
   ```
   `SITE_BASE_URL` should be empty (`''`) if the site is hosted at the
   domain root, or the sub-path (no trailing slash) if hosted in a
   subdirectory. Every internal link and asset path uses this constant, so
   it's the only thing you need to change when moving hosts.

5. **Change the default admin password.** Log in at `login.php` with
   `admin` / `Admin123!`, then update credentials directly in the `users`
   table (or extend `profile.php`'s update form to support admins too)
   before going live.

6. **Verify.** Visit `/index.php` to confirm the homepage loads with
   products, then `/monitor.php` to confirm all services report **Online**.

## Folder structure

```
brewleaf/
├── config/db.php          # DB connection + site base URL — edit this per host
├── includes/               # Shared header, footer, auth, helper functions
├── admin/                  # Admin panel (products, orders, users, theme, dashboard)
├── help/                   # End-user + admin wiki pages
├── assets/
│   ├── css/                 # style.css (shared) + 3 theme files
│   ├── js/main.js           # Nav toggle, cart AJAX, option pills, form validation
│   ├── images/               # Product photos + hero/about/favicon
│   └── videos/                # Roastery/brewing demo videos
├── sql/schema.sql          # Full DB schema + seed data
├── index.php, products.php, product.php, cart.php, checkout.php, ...
└── docs/README.md          # This file
```

## Updating content without touching code

See the in-app guide at `help/updating-content.php` (linked from the site's
**Help** menu) for step-by-step, non-technical instructions on adding
products, photos, and videos through the Admin panel.

## Source control

This project is tracked with Git (see the repository's commit history for
the full change log). To push it to your own GitHub/GitLab remote:

```
git remote add origin <your-repo-url>
git push -u origin main
```

## Known limitations of this build (see project chat for the full punch list)

This is the **core, functional skeleton** of the 100-point rubric — the
database, cart/checkout flow, auth, admin CRUD, monitor page, SEO basics,
and wiki are complete and working end-to-end. Still to add before final
submission: filling the page count out to 20+ dynamic PHP pages, replacing
placeholder photos/videos with real assets, a live deployment to
`myweb.cs.uwindsor.ca`, and a final responsiveness/cross-device pass.

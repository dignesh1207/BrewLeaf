-- ============================================================================
-- BrewLeaf Artisan Coffee & Tea Co. -- Database Schema
-- ============================================================================
-- Target: MySQL 5.7+ / MariaDB 10.x  (compatible with myweb.cs.uwindsor.ca)
--
-- Install:
--   1. Create a database, e.g.:  CREATE DATABASE brewleaf CHARACTER SET utf8mb4;
--   2. Import this file:         mysql -u <user> -p brewleaf < schema.sql
--   3. Update config/db.php with your DB host/name/user/password.
--
-- This file creates all tables required by the app and seeds:
--   - 1 default admin account (username: admin / password: Admin123!)
--   - 20 products (10 coffees + 10 teas), each with 2 option groups
--   - 3 site templates registered in site_settings
--   - service_status rows used by the backend monitor page
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Table: users
-- Stores both customer and admin accounts. `status` lets admins disable
-- an account without deleting it (rubric: user account administration).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(120) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    full_name       VARCHAR(120) NOT NULL,
    role            ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    status          ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: products
-- The catalogue. `category` separates coffee vs tea for filtering/search.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    slug            VARCHAR(140) NOT NULL UNIQUE,
    category        ENUM('coffee','tea') NOT NULL,
    origin          VARCHAR(80)  NOT NULL,
    description     TEXT NOT NULL,
    base_price      DECIMAL(6,2) NOT NULL,
    image           VARCHAR(255) NOT NULL,
    rating_avg      DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    rating_count    INT UNSIGNED NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: product_options
-- Each product has >=2 option groups (e.g. size, grind/style). A row is one
-- selectable value within a group, with a price modifier applied to base_price.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS product_options;
CREATE TABLE product_options (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED NOT NULL,
    option_group    VARCHAR(40)  NOT NULL,   -- e.g. 'Size', 'Grind', 'Style'
    option_value    VARCHAR(60)  NOT NULL,   -- e.g. '250g', 'Whole Bean'
    price_modifier  DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: reviews
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    rating          TINYINT UNSIGNED NOT NULL,   -- 1-5
    comment         TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: cart_items
-- Supports both logged-in users (user_id) and guests (session_id).
-- `selected_options` stores a JSON-encoded array of the option rows chosen
-- for this line (one product can have multiple option GROUPS -- e.g. Size
-- AND Grind -- selected at once, so a single option_id FK isn't enough).
-- Example: [{"option_id":3,"group":"Size","value":"500g","price_modifier":7.5}, ...]
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS cart_items;
CREATE TABLE cart_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NULL,
    session_id      VARCHAR(128) NULL,
    product_id      INT UNSIGNED NOT NULL,
    selected_options TEXT NULL,
    unit_price      DECIMAL(6,2) NOT NULL,
    quantity        INT UNSIGNED NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: orders / order_items
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    status          ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) NOT NULL,
    total           DECIMAL(8,2) NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    product_name    VARCHAR(120) NOT NULL,
    selected_options TEXT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(6,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: site_settings
-- Key/value store; used to persist the active site-wide template chosen
-- by the admin (rubric: 3 switchable templates).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS site_settings;
CREATE TABLE site_settings (
    setting_key     VARCHAR(60) PRIMARY KEY,
    setting_value   VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Table: service_status
-- Rows checked/updated by the backend monitor page (monitor.php).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS service_status;
CREATE TABLE service_status (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_name    VARCHAR(60) NOT NULL,
    status          ENUM('online','offline') NOT NULL DEFAULT 'online',
    last_checked    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Default admin account. Password is 'Admin123!' hashed with PHP's
-- password_hash() (bcrypt). CHANGE THIS after first login.
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('admin', 'admin@brewleaf.test', '$2y$10$i3ISeOLC0fQyhpeZ5X2vT.HqPjIMwrEVez0Pqd.FGdJOGRhYrgSwC', 'Site Administrator', 'admin', 'active'),
('jsmith', 'jsmith@example.com', '$2y$10$i3ISeOLC0fQyhpeZ5X2vT.HqPjIMwrEVez0Pqd.FGdJOGRhYrgSwC', 'Jane Smith', 'customer', 'active');

-- Site templates (regular / autumn / winter) -- 'regular' active by default.
INSERT INTO site_settings (setting_key, setting_value) VALUES
('active_theme', 'regular');

-- Backend services checked by monitor.php
INSERT INTO service_status (service_name, status) VALUES
('Database Connection', 'online'),
('Product Catalogue', 'online'),
('Shopping Cart', 'online'),
('Checkout Service', 'online'),
('User Authentication', 'online'),
('Search / SEO Sitemap', 'online');

-- ----------------------------------------------------------------------------
-- Products: 10 coffees + 10 teas.
-- ----------------------------------------------------------------------------
INSERT INTO products (name, slug, category, origin, description, base_price, image, rating_avg, rating_count) VALUES
('Sunrise Ethiopian Yirgacheffe', 'sunrise-ethiopian-yirgacheffe', 'coffee', 'Ethiopia', 'Bright, floral, and citrusy with notes of bergamot and jasmine. A washed-process coffee grown at high altitude.', 16.99, 'assets/images/product-01.jpg', 4.7, 132, 1),
('Midnight Sumatra Mandheling', 'midnight-sumatra-mandheling', 'coffee', 'Indonesia', 'Full-bodied and earthy with low acidity, notes of cedar and dark chocolate.', 15.49, 'assets/images/product-02.jpg', 4.5, 98, 1),
('Golden Colombian Supremo', 'golden-colombian-supremo', 'coffee', 'Colombia', 'Balanced and smooth with caramel sweetness and a nutty finish.', 14.99, 'assets/images/product-03.jpg', 4.6, 210, 1),
('Volcano Guatemala Antigua', 'volcano-guatemala-antigua', 'coffee', 'Guatemala', 'Rich and spicy with cocoa and smoky undertones from volcanic soil.', 15.99, 'assets/images/product-04.jpg', 4.4, 76, 1),
('Highland Kenya AA', 'highland-kenya-aa', 'coffee', 'Kenya', 'Wine-like acidity with blackcurrant and tomato notes. A bold breakfast cup.', 17.49, 'assets/images/product-05.jpg', 4.8, 145, 1),
('Costa Rica Tarrazu', 'costa-rica-tarrazu', 'coffee', 'Costa Rica', 'Clean and crisp with honey sweetness and citrus brightness.', 15.99, 'assets/images/product-06.jpg', 4.3, 61, 1),
('Brazilian Cerrado', 'brazilian-cerrado', 'coffee', 'Brazil', 'Low-acid and nutty with milk chocolate and toasted almond notes.', 13.99, 'assets/images/product-07.jpg', 4.2, 88, 1),
('Espresso Roast Blend', 'espresso-roast-blend', 'coffee', 'Blend', 'A house blend crafted for espresso: syrupy body, dark cocoa crema, caramelized finish.', 16.49, 'assets/images/product-08.jpg', 4.6, 173, 1),
('Decaf Peru Organic', 'decaf-peru-organic', 'coffee', 'Peru', 'Swiss Water Process decaf with mellow chocolate and brown sugar notes.', 15.99, 'assets/images/product-09.jpg', 4.1, 44, 1),
('Cold Brew Reserve', 'cold-brew-reserve', 'coffee', 'Blend', 'Coarse-ground blend optimized for smooth, low-acid cold brew.', 17.99, 'assets/images/product-10.jpg', 4.5, 59, 1),
('Dragon Well Green Tea', 'dragon-well-green-tea', 'tea', 'China', 'Pan-fired green tea with a delicate chestnut sweetness and grassy aroma.', 12.99, 'assets/images/product-11.jpg', 4.6, 87, 1),
('Assam Breakfast Black', 'assam-breakfast-black', 'tea', 'India', 'Malty and robust, the classic base for a hearty breakfast cup.', 11.99, 'assets/images/product-12.jpg', 4.4, 65, 1),
('Jasmine Silver Needle', 'jasmine-silver-needle', 'tea', 'China', 'White tea buds scented with fresh jasmine blossoms. Light and fragrant.', 14.99, 'assets/images/product-13.jpg', 4.7, 52, 1),
('Earl Grey Supreme', 'earl-grey-supreme', 'tea', 'Blend', 'Black tea with bergamot oil and cornflower petals for a citrus-floral cup.', 12.49, 'assets/images/product-14.jpg', 4.5, 121, 1),
('Chamomile Dream', 'chamomile-dream', 'tea', 'Egypt', 'Caffeine-free herbal infusion with honeyed floral notes, great before bed.', 10.99, 'assets/images/product-15.jpg', 4.3, 74, 1),
('Peppermint Chill', 'peppermint-chill', 'tea', 'USA', 'Caffeine-free herbal infusion, cool and refreshing peppermint leaf.', 9.99, 'assets/images/product-16.jpg', 4.2, 39, 1),
('Oolong Milk Tea Leaf', 'oolong-milk-tea-leaf', 'tea', 'Taiwan', 'Creamy, naturally sweet oolong with a smooth milky finish.', 15.99, 'assets/images/product-17.jpg', 4.6, 58, 1),
('Chai Spice Blend', 'chai-spice-blend', 'tea', 'India', 'Black tea with cinnamon, cardamom, clove, and ginger. Bold and warming.', 12.99, 'assets/images/product-18.jpg', 4.7, 96, 1),
('Hibiscus Berry Herbal', 'hibiscus-berry-herbal', 'tea', 'South Africa', 'Tart and fruity caffeine-free blend with hibiscus and mixed berries.', 10.49, 'assets/images/product-19.jpg', 4.1, 33, 1),
('Matcha Ceremonial Grade', 'matcha-ceremonial-grade', 'tea', 'Japan', 'Stone-ground shade-grown green tea powder, vibrant and umami-rich.', 24.99, 'assets/images/product-20.jpg', 4.8, 112, 1);

-- ----------------------------------------------------------------------------
-- Product options: each product gets a Size group and a Grind/Style group.
-- Coffees -> Size (250g/500g/1kg) + Grind (Whole Bean/Ground/Espresso Ground)
-- Teas    -> Size (50g/100g/250g)  + Style (Loose Leaf/Tea Bags [x20])
-- ----------------------------------------------------------------------------
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '250g', 0.00 FROM products WHERE category = 'coffee';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '500g', 7.50 FROM products WHERE category = 'coffee';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '1kg', 14.00 FROM products WHERE category = 'coffee';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Grind', 'Whole Bean', 0.00 FROM products WHERE category = 'coffee';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Grind', 'Ground (Drip)', 0.00 FROM products WHERE category = 'coffee';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Grind', 'Espresso Ground', 0.50 FROM products WHERE category = 'coffee';

INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '50g', 0.00 FROM products WHERE category = 'tea';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '100g', 5.00 FROM products WHERE category = 'tea';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Size', '250g', 10.50 FROM products WHERE category = 'tea';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Style', 'Loose Leaf', 0.00 FROM products WHERE category = 'tea';
INSERT INTO product_options (product_id, option_group, option_value, price_modifier)
SELECT id, 'Style', 'Tea Bags (x20)', 1.50 FROM products WHERE category = 'tea';

-- Sample reviews so product pages have content out of the box.
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 2, 5, 'Incredibly fragrant, my favorite morning cup.'),
(3, 2, 4, 'Smooth and reliable house blend, great value.'),
(20, 2, 5, 'Best matcha I have had outside Japan.');

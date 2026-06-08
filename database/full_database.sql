-- =========================================================
-- DATABASE MỚI SẠCH CHO DỰ ÁN WEBBANHANG
-- Website bán điện thoại / laptop / phụ kiện
-- Tạo mới toàn bộ database my_store
-- Lưu ý: file này sẽ XOÁ database my_store cũ rồi tạo lại từ đầu.
-- =========================================================

DROP DATABASE IF EXISTS my_store;
CREATE DATABASE my_store
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE my_store;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. TÀI KHOẢN / PHÂN QUYỀN
-- =========================================================

CREATE TABLE account (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    email VARCHAR(150) NULL UNIQUE,
    full_name VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    points INT UNSIGNED NOT NULL DEFAULT 0,
    member_tier ENUM('bac','vang','kim_cuong') NOT NULL DEFAULT 'bac',
    total_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
    cultivation_level INT NOT NULL DEFAULT 1,
    cultivation_exp INT NOT NULL DEFAULT 0,
    social_provider ENUM('google','facebook','local') NULL DEFAULT NULL,
    social_id VARCHAR(150) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at DATETIME NULL,
    email_verify_token VARCHAR(100) NULL,
    reset_token VARCHAR(100) NULL,
    reset_expires_at DATETIME NULL,
    remember_token VARCHAR(100) NULL,
    remember_expires_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NOT NULL,
    receiver_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address_line TEXT NOT NULL,
    ward VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_addresses_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order','promotion','system','support') NOT NULL DEFAULT 'system',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. DANH MỤC / THƯƠNG HIỆU / SẢN PHẨM
-- =========================================================

CREATE TABLE category (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(80) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE brand (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    logo VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    old_price DECIMAL(15,2) NULL,
    image VARCHAR(255) NULL,
    category_id INT UNSIGNED NULL,
    brand_id INT UNSIGNED NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','hidden','out_of_stock') NOT NULL DEFAULT 'active',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id) REFERENCES category(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_product_brand
        FOREIGN KEY (brand_id) REFERENCES brand(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    title VARCHAR(255) NULL,
    type ENUM('youtube','upload','360') NOT NULL DEFAULT 'youtube',
    CONSTRAINT fk_product_videos_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    color VARCHAR(80) NULL,
    storage VARCHAR(80) NULL,
    ram VARCHAR(80) NULL,
    version_name VARCHAR(120) NULL,
    sku VARCHAR(120) NULL UNIQUE,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    CONSTRAINT fk_product_variants_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_specifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    spec_group VARCHAR(120) NULL,
    spec_name VARCHAR(150) NOT NULL,
    spec_value TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_product_specs_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_imei (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    imei VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('in_stock','sold','warranty','returned') NOT NULL DEFAULT 'in_stock',
    sold_at DATETIME NULL,
    CONSTRAINT fk_product_imei_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_product_imei_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. GIỎ HÀNG / VOUCHER / ĐƠN HÀNG / THANH TOÁN
-- =========================================================

CREATE TABLE cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    session_id VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_item (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_cart_item_cart
        FOREIGN KEY (cart_id) REFERENCES cart(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cart_item_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cart_item_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vouchers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    type ENUM('fixed','percent','free_shipping') NOT NULL DEFAULT 'fixed',
    value DECIMAL(15,2) NOT NULL DEFAULT 0,
    min_order_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(15,2) NULL,
    usage_limit INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NULL,
    address TEXT NOT NULL,
    note TEXT NULL,
    voucher_id INT UNSIGNED NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    shipping_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cod','bank_transfer','momo','zalopay','vnpay','card','installment') NOT NULL DEFAULT 'cod',
    payment_status ENUM('unpaid','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
    order_status ENUM('pending','confirmed','processing','shipping','completed','cancelled','returned') NOT NULL DEFAULT 'pending',
    delivery_time DATETIME NULL,
    vat_invoice TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_orders_voucher
        FOREIGN KEY (voucher_id) REFERENCES vouchers(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    variant_id INT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_order_details_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_order_details_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_order_details_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE voucher_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    order_id INT UNSIGNED NULL,
    used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_voucher_usage_voucher
        FOREIGN KEY (voucher_id) REFERENCES vouchers(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_voucher_usage_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_voucher_usage_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_status_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_status_logs_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_order_status_logs_account
        FOREIGN KEY (created_by) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    payment_method ENUM('cod','bank_transfer','momo','zalopay','vnpay','card','installment') NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    transaction_code VARCHAR(150) NULL,
    status ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. WISHLIST / REVIEW / Q&A
-- =========================================================

CREATE TABLE wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist_account_product (account_id, product_id),
    CONSTRAINT fk_wishlist_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    product_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    status ENUM('pending','approved','hidden') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_reviews_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    image VARCHAR(255) NOT NULL,
    CONSTRAINT fk_review_images_review
        FOREIGN KEY (review_id) REFERENCES reviews(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_likes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review_likes (review_id, account_id),
    CONSTRAINT fk_review_likes_review
        FOREIGN KEY (review_id) REFERENCES reviews(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_review_likes_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','resolved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_reports_review
        FOREIGN KEY (review_id) REFERENCES reviews(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_review_reports_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    question TEXT NOT NULL,
    status ENUM('pending','answered','hidden') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_questions_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_product_questions_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_answers_question
        FOREIGN KEY (question_id) REFERENCES product_questions(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_product_answers_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5. BẢO HÀNH / ĐỔI TRẢ / HỖ TRỢ
-- =========================================================

CREATE TABLE warranty (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    product_id INT UNSIGNED NULL,
    order_id INT UNSIGNED NULL,
    imei VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_warranty_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_warranty_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_warranty_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE warranty_claims (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    warranty_id INT UNSIGNED NOT NULL,
    issue_description TEXT NOT NULL,
    status ENUM('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_warranty_claims_warranty
        FOREIGN KEY (warranty_id) REFERENCES warranty(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE return_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','approved','rejected','refunded') NOT NULL DEFAULT 'pending',
    refund_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_return_requests_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_return_requests_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','processing','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_tickets_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_messages_ticket
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_support_messages_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faq (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6. ADMIN / CMS / BANNER / FLASH SALE
-- =========================================================

CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NULL,
    image VARCHAR(255) NULL,
    link VARCHAR(255) NULL,
    position ENUM('main','side1','side2','side3','home','flash') NOT NULL DEFAULT 'home',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NULL,
    image VARCHAR(255) NULL,
    status ENUM('draft','published','hidden') NOT NULL DEFAULT 'published',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_posts_account
        FOREIGN KEY (created_by) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flash_sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flash_sale_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flash_sale_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    sale_price DECIMAL(15,2) NOT NULL,
    sale_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    sold_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_flash_sale_items_flash_sale
        FOREIGN KEY (flash_sale_id) REFERENCES flash_sales(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_flash_sale_items_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventory_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    change_type ENUM('import','export','adjust','sale','return') NOT NULL,
    quantity INT NOT NULL,
    note TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_logs_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_inventory_logs_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_inventory_logs_account
        FOREIGN KEY (created_by) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trade_in_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    old_device_name VARCHAR(255) NOT NULL,
    condition_note TEXT NULL,
    expected_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('pending','priced','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trade_in_requests_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_ratings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_service_ratings_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_service_ratings_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_logs_account
        FOREIGN KEY (account_id) REFERENCES account(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- DỮ LIỆU MẪU
-- =========================================================

-- Mật khẩu mẫu: 123456
INSERT INTO account (username, password, role, email, full_name, points, is_active, email_verified_at)
VALUES
('admin', '$2y$10$jgiLy6xOsNRc9CdqxPu1uKEJ4EwG3jSgy9JJTslqVOhmIURJIt54u', 'admin', 'admin@example.com', 'Quản trị viên', 100, 1, NOW()),
('thuc', '$2y$10$wwhm5ovVp5dlzbaZNUCEEw3/uNCnSmOWL2pQ8H9X5qVSE8Pv6FQEq', 'user', 'buitrongthuc08@gmail.com', 'Bùi Trọng Thức', 0, 1, NOW());

INSERT INTO category (name, description, icon, sort_order)
VALUES
('Điện thoại', 'Điện thoại thông minh, smartphone', 'bi-phone', 1),
('Laptop', 'Laptop học tập, văn phòng, gaming', 'bi-laptop', 2),
('Âm thanh', 'Tai nghe, loa, mic thu âm', 'bi-headphones', 3),
('Đồng hồ', 'Đồng hồ thông minh, camera', 'bi-smartwatch', 4),
('Phụ kiện', 'Chuột, sạc, cáp, ốp lưng', 'bi-earbuds', 5),
('PC', 'PC, màn hình, máy in', 'bi-pc-display', 6),
('Tivi', 'Tivi, điện máy', 'bi-tv', 7);

INSERT INTO brand (name, description)
VALUES
('Apple', 'Thương hiệu Apple'),
('Samsung', 'Thương hiệu Samsung'),
('Xiaomi', 'Thương hiệu Xiaomi'),
('OPPO', 'Thương hiệu OPPO'),
('ASUS', 'Thương hiệu ASUS'),
('JBL', 'Thương hiệu JBL'),
('Logitech', 'Thương hiệu Logitech');

INSERT INTO product (name, description, price, old_price, image, category_id, brand_id, stock_quantity, status, is_featured, is_new, is_best_seller, discount_percent)
VALUES
('iPhone 17 Pro Max', 'Điện thoại cao cấp Apple, hiệu năng mạnh, camera đẹp.', 34990000, 38990000, 'iphone17.jpg', 1, 1, 20, 'active', 1, 1, 1, 10),
('Samsung Galaxy S Ultra', 'Flagship Samsung màn hình đẹp, camera zoom xa.', 28990000, 32990000, 'samsung.png', 1, 2, 18, 'active', 1, 1, 1, 12),
('Xiaomi Redmi Note Pro 5G', 'Điện thoại tầm trung cấu hình tốt, pin khỏe.', 7490000, 8990000, 'xiaomi.png', 1, 3, 35, 'active', 1, 0, 1, 15),
('OPPO Reno', 'Thiết kế mỏng nhẹ, chụp ảnh chân dung đẹp.', 9990000, 11990000, 'oppor.jpg', 1, 4, 25, 'active', 1, 0, 0, 12),
('MacBook Air M2 13 inch', 'Laptop mỏng nhẹ, pin tốt, phù hợp sinh viên.', 23990000, 26990000, 'MacBook Air M2 13 inch.jpg', 2, 1, 10, 'active', 1, 0, 1, 8),
('ASUS TUF Gaming F15', 'Laptop gaming hiệu năng cao, tản nhiệt tốt.', 22490000, 24990000, 'ASUS TUF Gaming F15.jpg', 2, 5, 12, 'active', 1, 1, 1, 10),
('Tai nghe Bluetooth JBL', 'Tai nghe không dây âm bass mạnh, pin lâu.', 1290000, 1690000, 'headphone.jpg', 3, 6, 40, 'active', 1, 0, 0, 20),
('Chuột Gaming RGB', 'Chuột gaming LED RGB, độ nhạy cao.', 273000, 390000, 'chuot-gaming-rgb.jpg', 5, 7, 60, 'active', 1, 0, 1, 30);

INSERT INTO product_images (product_id, image, alt_text, sort_order)
VALUES
(1, 'iphone17.jpg', 'iPhone 17 Pro Max', 1),
(2, 'samsung.png', 'Samsung Galaxy S Ultra', 1),
(3, 'xiaomi.png', 'Xiaomi Redmi Note Pro 5G', 1),
(4, 'oppor.jpg', 'OPPO Reno', 1),
(5, 'MacBook Air M2 13 inch.jpg', 'MacBook Air M2', 1),
(6, 'ASUS TUF Gaming F15.jpg', 'ASUS TUF Gaming F15', 1),
(7, 'headphone.jpg', 'Tai nghe Bluetooth JBL', 1),
(8, 'chuot-gaming-rgb.jpg', 'Chuột Gaming RGB', 1);

INSERT INTO product_variants (product_id, color, storage, ram, version_name, sku, price, stock_quantity, image)
VALUES
(1, 'Titan tự nhiên', '256GB', '8GB', '256GB', 'IP17PM-256-TN', 34990000, 8, 'iphone17.jpg'),
(1, 'Đen', '512GB', '8GB', '512GB', 'IP17PM-512-BK', 39990000, 5, 'iphone17.jpg'),
(2, 'Đen', '256GB', '12GB', '256GB', 'SSU-256-BK', 28990000, 10, 'samsung.png'),
(3, 'Xanh', '128GB', '8GB', '128GB', 'XM-RNP-128-BL', 7490000, 15, 'xiaomi.png');

INSERT INTO product_specifications (product_id, spec_group, spec_name, spec_value, sort_order)
VALUES
(1, 'Màn hình', 'Kích thước', '6.7 inch', 1),
(1, 'Hiệu năng', 'Chip', 'Apple A-series', 2),
(2, 'Màn hình', 'Tần số quét', '120Hz', 1),
(3, 'Pin', 'Dung lượng', '5000mAh', 1),
(6, 'Hiệu năng', 'GPU', 'NVIDIA RTX', 1);

INSERT INTO vouchers (code, name, description, type, value, min_order_value, max_discount, usage_limit, is_active, starts_at, expires_at)
VALUES
('WEEKEND50K', 'Voucher cuối tuần 50K', 'Giảm 50.000đ cho đơn cuối tuần', 'fixed', 50000, 1000000, NULL, 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('WEEKEND10', 'Voucher cuối tuần 10%', 'Giảm 10% tối đa 300.000đ', 'percent', 10, 3000000, 300000, 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('STUDENT500K', 'Ưu đãi sinh viên', 'Giảm 500.000đ cho laptop/điện thoại', 'fixed', 500000, 10000000, NULL, 50, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));

INSERT INTO banners (title, subtitle, image, link, position, is_active, sort_order)
VALUES
('iPhone 15 Series Giảm sốc cuối tuần', 'Trợ giá lên đời, thu cũ đổi mới, trả góp 0%', 'iphone17.jpg', '/Product/list', 'main', 1, 1),
('MacBook Pro', 'Nâng cấp M-series', 'laptop.jpg', '/Product/list?category=2', 'side1', 1, 2),
('Galaxy A Series', 'Giá sốc hôm nay', 'samsung.png', '/Product/list?category=1', 'side2', 1, 3),
('Laptop Online', 'Giảm đến 5 triệu', 'ASUS TUF Gaming F15.jpg', '/Product/list?category=2', 'side3', 1, 4);

INSERT INTO flash_sales (name, starts_at, ends_at, is_active)
VALUES ('Flash Sale cuối tuần', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1);

INSERT INTO flash_sale_items (flash_sale_id, product_id, sale_price, sale_quantity, sold_quantity)
VALUES
(1, 1, 29990000, 10, 4),
(1, 2, 24990000, 10, 5),
(1, 6, 19990000, 8, 3),
(1, 8, 273000, 30, 12);

INSERT INTO faq (question, answer, sort_order, is_active)
VALUES
('Làm sao kiểm tra đơn hàng?', 'Vào mục Đơn hàng để xem lịch sử và trạng thái đơn hàng.', 1, 1),
('Sản phẩm có bảo hành không?', 'Điện thoại và laptop được bảo hành theo chính sách từng sản phẩm.', 2, 1),
('Có hỗ trợ trả góp không?', 'Website có mô phỏng hình thức trả góp 0% cho đơn hàng phù hợp.', 3, 1);

INSERT INTO blog_posts (title, slug, excerpt, content, image, status, created_by)
VALUES
('Kinh nghiệm chọn điện thoại cho sinh viên', 'kinh-nghiem-chon-dien-thoai-sinh-vien', 'Gợi ý chọn điện thoại phù hợp nhu cầu học tập và giải trí.', 'Nội dung bài viết demo.', 'iphone17.jpg', 'published', 1),
('Laptop học IT nên chọn cấu hình nào?', 'laptop-hoc-it-nen-chon-cau-hinh-nao', 'Một số tiêu chí chọn laptop cho sinh viên công nghệ thông tin.', 'Nội dung bài viết demo.', 'laptop.jpg', 'published', 1);

INSERT INTO notifications (account_id, title, message, type, is_read)
VALUES
(2, 'Chào mừng đến với Wedbanhang', 'Bạn có thể xem sản phẩm, thêm giỏ hàng và đặt hàng.', 'system', 0),
(2, 'Voucher cuối tuần', 'Nhập mã WEEKEND50K để nhận ưu đãi.', 'promotion', 0);

-- =========================================================
-- KIỂM TRA SAU KHI IMPORT
-- =========================================================

SHOW COLUMNS FROM account LIKE 'role';
SELECT id, username, email, full_name, role FROM account ORDER BY id;
SELECT COUNT(*) AS total_products FROM product;


-- =========================================================
-- HỆ THỐNG HẠNG TIÊU DÙNG + LEVEL TU TIÊN
-- =========================================================

ALTER TABLE account
    MODIFY COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user';

CREATE TABLE IF NOT EXISTS membership_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tier_key ENUM('bac','vang','kim_cuong') NOT NULL UNIQUE,
    tier_name VARCHAR(100) NOT NULL,
    min_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
    point_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    benefits TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO membership_tiers (tier_key, tier_name, min_spent, point_rate, benefits)
VALUES
('bac','Bạc',0,1,'Tích điểm 1%; Voucher sinh nhật 50.000đ; Hỗ trợ đổi trả trong 3 ngày; Nhận thông báo khuyến mãi sớm'),
('vang','Vàng',20000000,2,'Tích điểm 2%; Voucher hằng tháng 100.000đ; Miễn phí vận chuyển cho đơn từ 2.000.000đ; Ưu tiên xử lý đơn hàng; Giảm thêm 3% phụ kiện'),
('kim_cuong','Kim cương',50000000,5,'Tích điểm 5%; Voucher VIP 300.000đ; Miễn phí vận chuyển mọi đơn; Hỗ trợ ưu tiên cấp cao; Ưu đãi bảo hành/đổi trả nâng cao; Quà sinh nhật VIP')
ON DUPLICATE KEY UPDATE
    tier_name = VALUES(tier_name),
    min_spent = VALUES(min_spent),
    point_rate = VALUES(point_rate),
    benefits = VALUES(benefits);

CREATE TABLE IF NOT EXISTS cultivation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NOT NULL,
    exp_change INT NOT NULL DEFAULT 0,
    reason VARCHAR(255) NOT NULL,
    source_type VARCHAR(50) NULL,
    source_id INT NULL,
    level_after INT NOT NULL DEFAULT 1,
    exp_after INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SHOW COLUMNS FROM account LIKE 'role';
SHOW COLUMNS FROM account LIKE 'member_tier';


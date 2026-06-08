USE my_store;

CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    subtitle VARCHAR(255) NULL,
    image VARCHAR(255) NULL,
    position VARCHAR(50) DEFAULT 'home_main',
    link VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE banners
    ADD COLUMN IF NOT EXISTS subtitle VARCHAR(255) NULL AFTER title;

ALTER TABLE banners
    ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0 AFTER link;

INSERT INTO banners (title, subtitle, image, position, link, sort_order, is_active)
SELECT 'iPhone Series - Giảm sốc cuối tuần', 'Trợ giá lên đời, thu cũ đổi mới, trả góp 0%', '', 'home_main', '/Product/list', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'iPhone Series - Giảm sốc cuối tuần' AND position = 'home_main');

INSERT INTO banners (title, subtitle, image, position, link, sort_order, is_active)
SELECT 'MacBook Pro M-Series', 'Nâng cấp hiệu năng, ưu đãi sinh viên', '', 'home_mini', '/Product/list?category_id=2', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'MacBook Pro M-Series' AND position = 'home_mini');

INSERT INTO banners (title, subtitle, image, position, link, sort_order, is_active)
SELECT 'Phụ kiện gaming', 'Chuột, tai nghe, bàn phím sale mạnh', '', 'home_mini', '/Product/list?category_id=5', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Phụ kiện gaming' AND position = 'home_mini');

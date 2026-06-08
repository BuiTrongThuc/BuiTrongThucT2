USE my_store;

CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    image VARCHAR(255) NULL,
    position VARCHAR(50) DEFAULT 'home_main',
    link VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'iPhone 15 Series<br>Giảm sốc cuối tuần', '', 'home_main', '/Product/list', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'iPhone 15 Series<br>Giảm sốc cuối tuần' AND position='home_main');

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'Galaxy S24 Ultra<br>Ưu đãi AI cực mạnh', '', 'home_main', '/Product/list', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Galaxy S24 Ultra<br>Ưu đãi AI cực mạnh' AND position='home_main');

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'Redmi Note Series<br>Rẻ mạnh đáng mua', '', 'home_main', '/Product/list', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Redmi Note Series<br>Rẻ mạnh đáng mua' AND position='home_main');

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'Nâng cấp M-series', '', 'home_mini', '/Product/list?category=2', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Nâng cấp M-series' AND position='home_mini');

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'Giá sốc hôm nay', '', 'home_mini', '/Product/list', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Giá sốc hôm nay' AND position='home_mini');

INSERT INTO banners (title, image, position, link, is_active)
SELECT 'Giảm đến 5 triệu', '', 'home_mini', '/Product/list', 1
WHERE NOT EXISTS (SELECT 1 FROM banners WHERE title = 'Giảm đến 5 triệu' AND position='home_mini');

INSERT INTO vouchers (code, name, type, value, min_order_value, usage_limit, is_active)
VALUES
('WEEKEND50K', 'Voucher cuối tuần giảm 50K', 'fixed', 50000, 1000000, 300, 1),
('WEEKEND10', 'Voucher cuối tuần giảm 10%', 'percent', 10, 2000000, 200, 1),
('STUDENT500K', 'Ưu đãi sinh viên giảm 500K', 'fixed', 500000, 10000000, 100, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    type = VALUES(type),
    value = VALUES(value),
    min_order_value = VALUES(min_order_value),
    usage_limit = VALUES(usage_limit),
    is_active = 1;
bannersbanners
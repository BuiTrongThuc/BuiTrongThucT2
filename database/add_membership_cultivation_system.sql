
USE my_store;

-- Thêm cột member_tier nếu thiếu
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE account ADD COLUMN member_tier ENUM(''bac'',''vang'',''kim_cuong'') NOT NULL DEFAULT ''bac''',
        'SELECT ''member_tier already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'account'
      AND COLUMN_NAME = 'member_tier'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm cột total_spent nếu thiếu
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE account ADD COLUMN total_spent DECIMAL(15,2) NOT NULL DEFAULT 0',
        'SELECT ''total_spent already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'account'
      AND COLUMN_NAME = 'total_spent'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm cột cultivation_level nếu thiếu
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE account ADD COLUMN cultivation_level INT NOT NULL DEFAULT 1',
        'SELECT ''cultivation_level already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'account'
      AND COLUMN_NAME = 'cultivation_level'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm cột cultivation_exp nếu thiếu
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE account ADD COLUMN cultivation_exp INT NOT NULL DEFAULT 0',
        'SELECT ''cultivation_exp already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'account'
      AND COLUMN_NAME = 'cultivation_exp'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Chuẩn hoá role trước khi đổi ENUM
UPDATE account
SET role = LOWER(TRIM(role));

UPDATE account
SET role = 'user'
WHERE role IS NULL OR role NOT IN ('user','admin');

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

-- Không đặt FOREIGN KEY ở đây để tránh lỗi khác kiểu INT/UNSIGNED giữa các bản database cũ.
CREATE TABLE IF NOT EXISTS cultivation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    exp_change INT NOT NULL DEFAULT 0,
    reason VARCHAR(255) NOT NULL,
    source_type VARCHAR(50) NULL,
    source_id INT NULL,
    level_after INT NOT NULL DEFAULT 1,
    exp_after INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cultivation_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SHOW COLUMNS FROM account LIKE 'role';
SHOW COLUMNS FROM account LIKE 'member_tier';
SHOW COLUMNS FROM account LIKE 'cultivation_level';

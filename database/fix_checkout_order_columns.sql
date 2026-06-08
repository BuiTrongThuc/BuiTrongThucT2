-- FIX LỖI THANH TOÁN: Unknown column 'name' in 'field list'
-- Chạy file này trên database my_store hiện tại. Không xoá dữ liệu.
-- Mục tiêu: thêm các cột tương thích mà code thanh toán đang dùng.

USE my_store;

-- orders.name
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT ''Khách hàng'' AFTER order_code',
        'SELECT ''orders.name already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'name'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.phone
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '''' AFTER name',
        'SELECT ''orders.phone already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'phone'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.address
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN address TEXT NULL AFTER phone',
        'SELECT ''orders.address already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'address'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.payment_detail
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN payment_detail VARCHAR(255) NULL AFTER payment_method',
        'SELECT ''orders.payment_detail already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_detail'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.final_amount
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN final_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER total_amount',
        'SELECT ''orders.final_amount already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'final_amount'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.vat_company
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN vat_company VARCHAR(255) NULL AFTER vat_invoice',
        'SELECT ''orders.vat_company already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'vat_company'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.vat_tax_code
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE orders ADD COLUMN vat_tax_code VARCHAR(50) NULL AFTER vat_company',
        'SELECT ''orders.vat_tax_code already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'vat_tax_code'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- order_status_logs.status
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE order_status_logs ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT ''pending'' AFTER order_id',
        'SELECT ''order_status_logs.status already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_status_logs' AND COLUMN_NAME = 'status'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payments.method
SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE payments ADD COLUMN method VARCHAR(50) NULL AFTER order_id',
        'SELECT ''payments.method already exists'''
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'method'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SHOW COLUMNS FROM orders;
SHOW COLUMNS FROM payments;
SHOW COLUMNS FROM order_status_logs;

<?php
class Database
{
    private $host = '127.0.0.1';
    private $db_name = 'my_store';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection()
    {
        // Trên hosting như InfinityFree không có quyền CREATE DATABASE.
        // Local Laragon vẫn tự tạo my_store như cũ.
        if (strpos($this->db_name, 'if0_') !== 0) {
            $this->ensureDatabaseExists();
        }
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4', $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec('SET NAMES utf8mb4');
            $this->ensureTablesExist();
        } catch (PDOException $exception) {
            die('Connection error: ' . $exception->getMessage());
        }
        return $this->conn;
    }

    private function ensureDatabaseExists()
    {
        try {
            $pdo = new PDO('mysql:host=' . $this->host . ';charset=utf8mb4', $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $this->db_name . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $exception) {
            die('Database create error: ' . $exception->getMessage());
        }
    }

    private function hasColumn($table, $column)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column");
        $stmt->execute(array(':table' => $table, ':column' => $column));
        return (int)$stmt->fetchColumn() > 0;
    }

    private function addColumnIfMissing($table, $column, $definition)
    {
        if (!$this->hasColumn($table, $column)) {
            $this->conn->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        }
    }

    private function ensureTablesExist()
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS category (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS brand (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL UNIQUE,
            logo VARCHAR(255) NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS product (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT,
            price DECIMAL(12,2) NOT NULL DEFAULT 0,
            sale_price DECIMAL(12,2) NULL,
            image VARCHAR(255) DEFAULT NULL,
            category_id INT NULL,
            brand_id INT NULL,
            sku VARCHAR(80) NULL,
            short_description VARCHAR(255) NULL,
            stock_quantity INT NOT NULL DEFAULT 0,
            view_count INT NOT NULL DEFAULT 0,
            sold_count INT NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            is_new TINYINT(1) NOT NULL DEFAULT 0,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_flash_sale TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL,
            FOREIGN KEY (brand_id) REFERENCES brand(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (array(
            'sale_price' => 'sale_price DECIMAL(12,2) NULL',
            'brand_id' => 'brand_id INT NULL',
            'sku' => 'sku VARCHAR(80) NULL',
            'short_description' => 'short_description VARCHAR(255) NULL',
            'stock_quantity' => 'stock_quantity INT NOT NULL DEFAULT 0',
            'view_count' => 'view_count INT NOT NULL DEFAULT 0',
            'sold_count' => 'sold_count INT NOT NULL DEFAULT 0',
            'status' => "status VARCHAR(30) NOT NULL DEFAULT 'active'",
            'is_new' => 'is_new TINYINT(1) NOT NULL DEFAULT 0',
            'is_featured' => 'is_featured TINYINT(1) NOT NULL DEFAULT 0',
            'is_flash_sale' => 'is_flash_sale TINYINT(1) NOT NULL DEFAULT 0',
            'created_at' => 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP'
        ) as $c=>$d) { $this->addColumnIfMissing('product',$c,$d); }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS account (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') NOT NULL DEFAULT 'user',
            email VARCHAR(150) NULL UNIQUE,
            full_name VARCHAR(150) NULL,
            phone VARCHAR(30) NULL,
            address TEXT NULL,
            avatar VARCHAR(255) NULL,
            points INT NOT NULL DEFAULT 0,
            member_tier ENUM('bac','vang','kim_cuong') NOT NULL DEFAULT 'bac',
            total_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
            cultivation_level INT NOT NULL DEFAULT 1,
            cultivation_exp INT NOT NULL DEFAULT 0,
            social_provider VARCHAR(50) NULL,
            social_id VARCHAR(150) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            email_verified_at DATETIME NULL,
            email_verify_token VARCHAR(100) NULL,
            reset_token VARCHAR(100) NULL,
            reset_expires_at DATETIME NULL,
            remember_token VARCHAR(100) NULL,
            remember_expires_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (array(
            'role'=>"role ENUM('user','admin') NOT NULL DEFAULT 'user'", 'email'=>'email VARCHAR(150) NULL UNIQUE',
            'full_name'=>'full_name VARCHAR(150) NULL', 'phone'=>'phone VARCHAR(30) NULL', 'address'=>'address TEXT NULL',
            'avatar'=>'avatar VARCHAR(255) NULL', 'points'=>'points INT NOT NULL DEFAULT 0',
            'member_tier'=>"member_tier ENUM('bac','vang','kim_cuong') NOT NULL DEFAULT 'bac'",
            'total_spent'=>'total_spent DECIMAL(15,2) NOT NULL DEFAULT 0',
            'cultivation_level'=>'cultivation_level INT NOT NULL DEFAULT 1',
            'cultivation_exp'=>'cultivation_exp INT NOT NULL DEFAULT 0',
            'social_provider'=>'social_provider VARCHAR(50) NULL', 'social_id'=>'social_id VARCHAR(150) NULL',
            'is_active'=>'is_active TINYINT(1) NOT NULL DEFAULT 1', 'email_verified_at'=>'email_verified_at DATETIME NULL',
            'email_verify_token'=>'email_verify_token VARCHAR(100) NULL', 'reset_token'=>'reset_token VARCHAR(100) NULL',
            'reset_expires_at'=>'reset_expires_at DATETIME NULL', 'remember_token'=>'remember_token VARCHAR(100) NULL',
            'remember_expires_at'=>'remember_expires_at DATETIME NULL', 'created_at'=>'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ) as $c=>$d) { $this->addColumnIfMissing('account',$c,$d); }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS user_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_id INT NOT NULL,
            receiver_name VARCHAR(150) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            province VARCHAR(100) NULL,
            district VARCHAR(100) NULL,
            ward VARCHAR(100) NULL,
            address_detail TEXT NOT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(128) NOT NULL,
            account_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_cart_session (session_id),
            FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->addColumnIfMissing('cart','account_id','account_id INT NULL');

        $this->conn->exec("CREATE TABLE IF NOT EXISTS cart_item (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            variant_id INT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(12,2) NOT NULL,
            FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE,
            UNIQUE KEY uniq_cart_product_variant (cart_id, product_id, variant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->addColumnIfMissing('cart_item','variant_id','variant_id INT NULL');

        $this->conn->exec("CREATE TABLE IF NOT EXISTS vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            type ENUM('percent','fixed') NOT NULL DEFAULT 'fixed',
            value DECIMAL(12,2) NOT NULL DEFAULT 0,
            min_order_value DECIMAL(12,2) NOT NULL DEFAULT 0,
            usage_limit INT NULL,
            used_count INT NOT NULL DEFAULT 0,
            start_date DATETIME NULL,
            end_date DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_id INT NULL,
            order_code VARCHAR(50) NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            address TEXT NOT NULL,
            note TEXT NULL,
            delivery_time VARCHAR(100) NULL,
            payment_method VARCHAR(50) NULL,
            payment_detail VARCHAR(255) NULL,
            payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid',
            order_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            voucher_id INT NULL,
            discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            final_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            vat_invoice TINYINT(1) NOT NULL DEFAULT 0,
            vat_company VARCHAR(255) NULL,
            vat_tax_code VARCHAR(50) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL,
            FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (array(
            'account_id'=>'account_id INT NULL', 'order_code'=>'order_code VARCHAR(50) NULL UNIQUE',
            'name'=>'name VARCHAR(255) NOT NULL DEFAULT \'Khách hàng\'', 'phone'=>'phone VARCHAR(30) NOT NULL DEFAULT \'\'', 'address'=>'address TEXT NULL',
            'note'=>'note TEXT NULL',
            'delivery_time'=>'delivery_time VARCHAR(100) NULL', 'payment_method'=>'payment_method VARCHAR(50) NULL',
            'payment_detail'=>'payment_detail VARCHAR(255) NULL', 'payment_status'=>"payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid'",
            'order_status'=>"order_status VARCHAR(50) NOT NULL DEFAULT 'pending'", 'voucher_id'=>'voucher_id INT NULL',
            'discount_amount'=>'discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0', 'shipping_fee'=>'shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0',
            'total_amount'=>'total_amount DECIMAL(12,2) NOT NULL DEFAULT 0', 'final_amount'=>'final_amount DECIMAL(12,2) NOT NULL DEFAULT 0',
            'vat_invoice'=>'vat_invoice TINYINT(1) NOT NULL DEFAULT 0', 'vat_company'=>'vat_company VARCHAR(255) NULL', 'vat_tax_code'=>'vat_tax_code VARCHAR(50) NULL'
        ) as $c=>$d) { $this->addColumnIfMissing('orders',$c,$d); }

        $this->conn->exec("CREATE TABLE IF NOT EXISTS order_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            variant_id INT NULL,
            product_name VARCHAR(150) NULL,
            quantity INT NOT NULL,
            price DECIMAL(12,2) NOT NULL,
            imei VARCHAR(50) NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        foreach (array('variant_id'=>'variant_id INT NULL','product_name'=>'product_name VARCHAR(150) NULL','imei'=>'imei VARCHAR(50) NULL') as $c=>$d){$this->addColumnIfMissing('order_details',$c,$d);}

        // Cột tương thích cho database đã import từ file SQL mới.
        // Không xoá dữ liệu, chỉ thêm cột còn thiếu để code thanh toán cũ chạy được.
        foreach (array('status'=>"status VARCHAR(50) NOT NULL DEFAULT 'pending'", 'note'=>'note TEXT NULL') as $c=>$d) { $this->addColumnIfMissing('order_status_logs',$c,$d); }
        foreach (array('method'=>'method VARCHAR(50) NULL', 'status'=>"status VARCHAR(50) NOT NULL DEFAULT 'pending'", 'transaction_code'=>'transaction_code VARCHAR(100) NULL', 'paid_at'=>'paid_at DATETIME NULL') as $c=>$d) { $this->addColumnIfMissing('payments',$c,$d); }

        $extraSql = array(
            "CREATE TABLE IF NOT EXISTS order_status_logs (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, status VARCHAR(50) NOT NULL, note TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS payments (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, method VARCHAR(50) NOT NULL, amount DECIMAL(12,2) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'pending', transaction_code VARCHAR(100) NULL, paid_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_images (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, image VARCHAR(255) NOT NULL, sort_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_videos (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, title VARCHAR(150) NULL, video_url VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_variants (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, color VARCHAR(80) NULL, storage VARCHAR(80) NULL, ram VARCHAR(80) NULL, version VARCHAR(100) NULL, sku VARCHAR(100) NULL, price DECIMAL(12,2) NULL, stock_quantity INT NOT NULL DEFAULT 0, image VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_specifications (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, spec_group VARCHAR(100) NULL, spec_name VARCHAR(150) NOT NULL, spec_value TEXT NOT NULL, sort_order INT DEFAULT 0, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_imei (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, variant_id INT NULL, imei VARCHAR(50) NOT NULL UNIQUE, status VARCHAR(30) NOT NULL DEFAULT 'in_stock', order_id INT NULL, sold_at DATETIME NULL, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS wishlist (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NOT NULL, product_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_wishlist (account_id, product_id), FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS voucher_usage (id INT AUTO_INCREMENT PRIMARY KEY, voucher_id INT NOT NULL, account_id INT NULL, order_id INT NULL, used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NULL, title VARCHAR(150) NOT NULL, message TEXT NOT NULL, type VARCHAR(50) NOT NULL DEFAULT 'system', is_read TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS reviews (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, account_id INT NULL, rating INT NOT NULL, comment TEXT NULL, is_approved TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS review_images (id INT AUTO_INCREMENT PRIMARY KEY, review_id INT NOT NULL, image VARCHAR(255) NOT NULL, FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS review_likes (id INT AUTO_INCREMENT PRIMARY KEY, review_id INT NOT NULL, account_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_review_like (review_id, account_id), FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS review_reports (id INT AUTO_INCREMENT PRIMARY KEY, review_id INT NOT NULL, account_id INT NULL, reason TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_questions (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, account_id INT NULL, question TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS product_answers (id INT AUTO_INCREMENT PRIMARY KEY, question_id INT NOT NULL, account_id INT NULL, answer TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (question_id) REFERENCES product_questions(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS warranty (id INT AUTO_INCREMENT PRIMARY KEY, order_detail_id INT NULL, product_id INT NOT NULL, account_id INT NULL, imei VARCHAR(50) NULL, start_date DATE NULL, end_date DATE NULL, status VARCHAR(50) NOT NULL DEFAULT 'active', note TEXT NULL, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS warranty_claims (id INT AUTO_INCREMENT PRIMARY KEY, warranty_id INT NULL, account_id INT NULL, imei VARCHAR(50) NULL, issue_description TEXT NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (warranty_id) REFERENCES warranty(id) ON DELETE SET NULL, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS return_requests (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, account_id INT NULL, reason TEXT NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'pending', refund_amount DECIMAL(12,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS support_tickets (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NULL, subject VARCHAR(150) NOT NULL, message TEXT NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'open', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS support_messages (id INT AUTO_INCREMENT PRIMARY KEY, ticket_id INT NOT NULL, account_id INT NULL, message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS faq (id INT AUTO_INCREMENT PRIMARY KEY, question VARCHAR(255) NOT NULL, answer TEXT NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, sort_order INT DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS blog_posts (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NULL, content TEXT NOT NULL, thumbnail VARCHAR(255) NULL, status VARCHAR(30) NOT NULL DEFAULT 'published', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS banners (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(150) NOT NULL, image VARCHAR(255) NULL, position VARCHAR(50) DEFAULT 'home', link VARCHAR(255) NULL, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS inventory_logs (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, variant_id INT NULL, change_quantity INT NOT NULL, type VARCHAR(50) NOT NULL, note TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS flash_sales (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, is_active TINYINT(1) DEFAULT 1) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS flash_sale_items (id INT AUTO_INCREMENT PRIMARY KEY, flash_sale_id INT NOT NULL, product_id INT NOT NULL, sale_price DECIMAL(12,2) NOT NULL, quantity_limit INT DEFAULT 0, FOREIGN KEY (flash_sale_id) REFERENCES flash_sales(id) ON DELETE CASCADE, FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS trade_in_requests (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NULL, old_device_name VARCHAR(150) NOT NULL, imei VARCHAR(50) NULL, condition_note TEXT NULL, expected_price DECIMAL(12,2) DEFAULT 0, status VARCHAR(50) DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS service_ratings (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NULL, rating INT NOT NULL, comment TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS admin_logs (id INT AUTO_INCREMENT PRIMARY KEY, account_id INT NULL, action VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        foreach ($extraSql as $sql) { $this->conn->exec($sql); }

        $this->seedData();
    }

    private function seedData()
    {
        if ((int)$this->conn->query('SELECT COUNT(*) FROM category')->fetchColumn() === 0) {
            $stmt = $this->conn->prepare('INSERT INTO category (name, description) VALUES (?, ?)');
            foreach (array(
                array('Điện thoại', 'Điện thoại thông minh iPhone, Samsung, Xiaomi, Oppo'),
                array('Laptop', 'Máy tính xách tay học tập, gaming, văn phòng'),
                array('Phụ kiện', 'Ốp lưng, cáp sạc, củ sạc, pin dự phòng'),
                array('Âm thanh', 'Tai nghe, loa bluetooth, mic thu âm'),
                array('Đồng hồ', 'Đồng hồ thông minh và phụ kiện đeo')
            ) as $row) { $stmt->execute($row); }
        }
        if ((int)$this->conn->query('SELECT COUNT(*) FROM brand')->fetchColumn() === 0) {
            $stmt = $this->conn->prepare('INSERT INTO brand (name, description) VALUES (?, ?)');
            foreach (array('Apple','Samsung','Xiaomi','Oppo','Asus') as $brand) { $stmt->execute(array($brand, 'Thương hiệu ' . $brand)); }
        }
        if ((int)$this->conn->query('SELECT COUNT(*) FROM account')->fetchColumn() === 0) {
            $stmt = $this->conn->prepare('INSERT INTO account (username, password, role, email, full_name, is_active, email_verified_at, points) VALUES (?, ?, ?, ?, ?, 1, NOW(), 100)');
            $stmt->execute(array('admin', md5('123456'), 'admin', 'admin@example.com', 'Quản trị viên',));
        }
        if ((int)$this->conn->query('SELECT COUNT(*) FROM vouchers')->fetchColumn() === 0) {
            $stmt = $this->conn->prepare("INSERT INTO vouchers (code, name, type, value, min_order_value, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute(array('SALE10', 'Giảm 10%', 'percent', 10, 1000000, 200));
            $stmt->execute(array('FREESHIP', 'Hỗ trợ vận chuyển', 'fixed', 30000, 500000, 500));
        }
        if ((int)$this->conn->query('SELECT COUNT(*) FROM faq')->fetchColumn() === 0) {
            $stmt = $this->conn->prepare('INSERT INTO faq (question, answer, sort_order) VALUES (?, ?, ?)');
            $stmt->execute(array('Có được kiểm tra hàng khi nhận không?', 'Khách hàng được kiểm tra ngoại quan sản phẩm trước khi thanh toán.', 1));
            $stmt->execute(array('Bảo hành điện thoại theo gì?', 'Bảo hành theo IMEI/Serial và chính sách từng hãng.', 2));
        }

        // Ép kiểu role sang ENUM nếu database cũ còn dùng VARCHAR.
        try {
            $this->conn->exec("ALTER TABLE account MODIFY COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user'");
        } catch (Exception $e) {}

        $this->conn->exec("CREATE TABLE IF NOT EXISTS membership_tiers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tier_key ENUM('bac','vang','kim_cuong') NOT NULL UNIQUE,
            tier_name VARCHAR(100) NOT NULL,
            min_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
            point_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
            benefits TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->conn->exec("INSERT IGNORE INTO membership_tiers (tier_key, tier_name, min_spent, point_rate, benefits) VALUES
            ('bac','Bạc',0,1,'Tích điểm 1%; Voucher sinh nhật 50.000đ; Hỗ trợ đổi trả trong 3 ngày; Nhận thông báo khuyến mãi sớm'),
            ('vang','Vàng',20000000,2,'Tích điểm 2%; Voucher hằng tháng 100.000đ; Miễn phí vận chuyển cho đơn từ 2.000.000đ; Ưu tiên xử lý đơn hàng; Giảm thêm 3% phụ kiện'),
            ('kim_cuong','Kim cương',50000000,5,'Tích điểm 5%; Voucher VIP 300.000đ; Miễn phí vận chuyển mọi đơn; Hỗ trợ ưu tiên cấp cao; Ưu đãi bảo hành/đổi trả nâng cao; Quà sinh nhật VIP')
        ");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS cultivation_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_id INT UNSIGNED NULL,
            exp_change INT NOT NULL DEFAULT 0,
            reason VARCHAR(255) NOT NULL,
            source_type VARCHAR(50) NULL,
            source_id INT NULL,
            level_after INT NOT NULL DEFAULT 1,
            exp_after INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cultivation_account (account_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
?>

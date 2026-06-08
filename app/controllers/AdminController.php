<?php
require_once 'config/database.php';
require_once 'app/helpers/SessionHelper.php';

class AdminController
{
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }


    private function tableExists($table)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
            $stmt->execute(array(':table' => $table));
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function safeCount($table, $where = '')
    {
        try {
            if (!$this->tableExists($table)) return 0;
            return (int)$this->db->query('SELECT COUNT(*) FROM `' . $table . '` ' . $where)->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function safeRows($sql)
    {
        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            return array();
        }
    }

    private function renderModule($config)
    {
        SessionHelper::requireAdmin();
        $pageTitle = $config['title'];
        include 'app/views/admin/module.php';
    }

    public function productsCenter()
    {
        $config = array(
            'title' => 'Quản lý sản phẩm',
            'subtitle' => 'Quản trị sản phẩm, danh mục, thương hiệu, ảnh, video, biến thể, SEO và import hàng loạt.',
            'icon' => 'bi-box-seam',
            'color' => '#ef4444',
            'stats' => array(
                array('label' => 'Tổng sản phẩm', 'value' => $this->safeCount('product'), 'icon' => 'bi-box'),
                array('label' => 'Danh mục', 'value' => $this->safeCount('category'), 'icon' => 'bi-grid'),
                array('label' => 'Thương hiệu', 'value' => $this->safeCount('brand'), 'icon' => 'bi-award'),
                array('label' => 'Sắp hết hàng', 'value' => $this->safeCount('product', 'WHERE stock_quantity <= 5'), 'icon' => 'bi-exclamation-triangle')
            ),
            'actions' => array(
                array('label' => 'Thêm sản phẩm', 'url' => BASE_URL . '/Product/add', 'class' => 'btn-danger'),
                array('label' => 'Danh sách sản phẩm', 'url' => BASE_URL . '/Product/list', 'class' => 'btn-outline-danger'),
                array('label' => 'Danh mục', 'url' => BASE_URL . '/Category/list', 'class' => 'btn-outline-danger')
            ),
            'features' => array(
                'Thêm / sửa / xoá sản phẩm',
                'Quản lý danh mục & thương hiệu',
                'Upload ảnh và video sản phẩm',
                'Cấu hình biến thể màu / dung lượng / SKU',
                'Quản lý giá bán và giá khuyến mãi',
                'Ẩn / hiện sản phẩm',
                'SEO tiêu đề, mô tả, slug URL',
                'Nhập hàng loạt qua Excel/CSV'
            ),
            'rowsTitle' => 'Sản phẩm mới nhất',
            'rows' => $this->safeRows('SELECT id, name, price, stock_quantity, status FROM product ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'name' => 'Tên sản phẩm', 'price' => 'Giá', 'stock_quantity' => 'Kho', 'status' => 'Trạng thái')
        );
        $this->renderModule($config);
    }

    public function inventoryCenter()
    {
        $config = array(
            'title' => 'Quản lý kho hàng',
            'subtitle' => 'Theo dõi tồn kho theo SKU, nhập xuất kho, cảnh báo hàng sắp hết và báo cáo xuất nhập tồn.',
            'icon' => 'bi-building-gear',
            'color' => '#f97316',
            'stats' => array(
                array('label' => 'Sản phẩm trong kho', 'value' => $this->safeCount('product'), 'icon' => 'bi-boxes'),
                array('label' => 'Sắp hết hàng', 'value' => $this->safeCount('product', 'WHERE stock_quantity <= 5'), 'icon' => 'bi-exclamation-triangle'),
                array('label' => 'Hết hàng', 'value' => $this->safeCount('product', 'WHERE stock_quantity = 0'), 'icon' => 'bi-x-circle'),
                array('label' => 'Lịch sử kho', 'value' => $this->safeCount('inventory_logs'), 'icon' => 'bi-clock-history')
            ),
            'actions' => array(
                array('label' => 'Mở kho hàng', 'url' => BASE_URL . '/Admin/inventory', 'class' => 'btn-warning'),
                array('label' => 'Danh sách sản phẩm', 'url' => BASE_URL . '/Product/list', 'class' => 'btn-outline-warning')
            ),
            'features' => array(
                'Xem tồn kho theo SKU / biến thể',
                'Nhập / xuất kho và lịch sử giao dịch',
                'Cảnh báo hàng sắp hết',
                'Đặt ngưỡng tồn kho tối thiểu',
                'Quản lý nhiều kho / chi nhánh',
                'Kiểm kê & điều chỉnh số lượng',
                'Báo cáo xuất nhập tồn',
                'Quét mã vạch / QR code'
            ),
            'rowsTitle' => 'Sản phẩm tồn kho thấp',
            'rows' => $this->safeRows('SELECT id, name, stock_quantity, status FROM product ORDER BY stock_quantity ASC, id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'name' => 'Tên sản phẩm', 'stock_quantity' => 'Tồn kho', 'status' => 'Trạng thái')
        );
        $this->renderModule($config);
    }

    public function ordersCenter()
    {
        $config = array(
            'title' => 'Quản lý đơn hàng',
            'subtitle' => 'Xem, lọc, cập nhật trạng thái, xử lý đổi trả, hoàn tiền, in hoá đơn và xuất dữ liệu.',
            'icon' => 'bi-receipt-cutoff',
            'color' => '#2563eb',
            'stats' => array(
                array('label' => 'Tổng đơn', 'value' => $this->safeCount('orders'), 'icon' => 'bi-receipt'),
                array('label' => 'Đơn chờ', 'value' => $this->safeCount('orders', "WHERE order_status IN ('pending','processing','confirmed')"), 'icon' => 'bi-hourglass'),
                array('label' => 'Đã hoàn tất', 'value' => $this->safeCount('orders', "WHERE order_status IN ('completed','success')"), 'icon' => 'bi-check-circle'),
                array('label' => 'Đổi trả', 'value' => $this->safeCount('return_requests'), 'icon' => 'bi-arrow-counterclockwise')
            ),
            'actions' => array(
                array('label' => 'Danh sách đơn', 'url' => BASE_URL . '/Admin/orders', 'class' => 'btn-primary'),
                array('label' => 'Báo cáo đơn', 'url' => BASE_URL . '/Admin/dashboard', 'class' => 'btn-outline-primary')
            ),
            'features' => array(
                'Xem / lọc / tìm kiếm đơn hàng',
                'Cập nhật trạng thái đơn hàng',
                'In hoá đơn & phiếu giao hàng',
                'Xử lý đổi trả / hoàn tiền',
                'Giao đơn cho nhân viên xử lý',
                'Tích hợp đơn vị vận chuyển',
                'Xuất dữ liệu đơn hàng Excel'
            ),
            'rowsTitle' => 'Đơn hàng mới nhất',
            'rows' => $this->safeRows('SELECT id, order_code, COALESCE(customer_name,name) AS customer, phone, order_status, payment_status, COALESCE(final_amount,total_amount) AS amount FROM orders ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'order_code' => 'Mã đơn', 'customer' => 'Khách hàng', 'phone' => 'SĐT', 'order_status' => 'Đơn', 'payment_status' => 'TT', 'amount' => 'Tổng tiền')
        );
        $this->renderModule($config);
    }

    public function customersCenter()
    {
        $config = array(
            'title' => 'Quản lý khách hàng',
            'subtitle' => 'Hồ sơ khách hàng, lịch sử mua hàng, nhóm VIP/thường/mới, điểm thưởng và khoá/mở tài khoản.',
            'icon' => 'bi-people',
            'color' => '#7c3aed',
            'stats' => array(
                array('label' => 'Tài khoản', 'value' => $this->safeCount('account'), 'icon' => 'bi-person'),
                array('label' => 'Admin', 'value' => $this->safeCount('account', "WHERE role='admin'"), 'icon' => 'bi-shield'),
                array('label' => 'User', 'value' => $this->safeCount('account', "WHERE role='user'"), 'icon' => 'bi-person-check'),
                array('label' => 'Bị khoá', 'value' => $this->safeCount('account', "WHERE is_active=0"), 'icon' => 'bi-lock')
            ),
            'actions' => array(
                array('label' => 'Quản lý người dùng', 'url' => BASE_URL . '/User/admin', 'class' => 'btn-primary'),
                array('label' => 'Thông báo', 'url' => BASE_URL . '/Advanced/notifications', 'class' => 'btn-outline-primary')
            ),
            'features' => array(
                'Xem danh sách & hồ sơ khách hàng',
                'Phân nhóm VIP / thường / mới',
                'Lịch sử mua hàng từng khách',
                'Cấp / thu hồi điểm thưởng',
                'Khoá / mở khoá tài khoản',
                'Gửi email / thông báo trực tiếp',
                'Xuất danh sách khách hàng để Marketing'
            ),
            'rowsTitle' => 'Khách hàng mới nhất',
            'rows' => $this->safeRows('SELECT id, username, email, full_name, role, is_active, member_tier, total_spent FROM account ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'username' => 'Username', 'email' => 'Email', 'full_name' => 'Họ tên', 'role' => 'Quyền', 'is_active' => 'Active', 'member_tier' => 'Hạng', 'total_spent' => 'Tiêu dùng')
        );
        $this->renderModule($config);
    }

    public function marketingCenter()
    {
        $config = array(
            'title' => 'Khuyến mãi & Marketing',
            'subtitle' => 'Coupon, flash sale, tích điểm đổi quà, banner, email marketing, push notification và giới thiệu bạn bè.',
            'icon' => 'bi-megaphone',
            'color' => '#db2777',
            'stats' => array(
                array('label' => 'Voucher', 'value' => $this->safeCount('vouchers'), 'icon' => 'bi-ticket'),
                array('label' => 'Banner', 'value' => $this->safeCount('banners'), 'icon' => 'bi-images'),
                array('label' => 'Thông báo', 'value' => $this->safeCount('notifications'), 'icon' => 'bi-bell'),
                array('label' => 'Flash sale', 'value' => $this->safeCount('flash_sales'), 'icon' => 'bi-lightning')
            ),
            'actions' => array(
                array('label' => 'Voucher', 'url' => BASE_URL . '/Admin/vouchers', 'class' => 'btn-danger'),
                array('label' => 'Banner', 'url' => BASE_URL . '/Admin/banners', 'class' => 'btn-outline-danger')
            ),
            'features' => array(
                'Tạo / quản lý mã giảm giá',
                'Flash sale, đếm ngược thời gian',
                'Chương trình tích điểm đổi quà',
                'Banner quảng cáo trang chủ',
                'Gửi email marketing hàng loạt',
                'Push notification',
                'Quản lý giới thiệu bạn bè'
            ),
            'rowsTitle' => 'Voucher mới nhất',
            'rows' => $this->safeRows('SELECT id, code, name, type, value, is_active FROM vouchers ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'code' => 'Mã', 'name' => 'Tên', 'type' => 'Loại', 'value' => 'Giá trị', 'is_active' => 'Active')
        );
        $this->renderModule($config);
    }

    public function reportsCenter()
    {
        $config = array(
            'title' => 'Báo cáo & Thống kê',
            'subtitle' => 'Doanh thu, sản phẩm bán chạy, conversion, tồn kho, lợi nhuận, lượt truy cập và hành vi khách hàng.',
            'icon' => 'bi-graph-up-arrow',
            'color' => '#16a34a',
            'stats' => array(
                array('label' => 'Doanh thu', 'value' => number_format((float)($this->safeRows('SELECT COALESCE(SUM(COALESCE(final_amount,total_amount)),0) AS revenue FROM orders')[0]->revenue ?? 0), 0, ',', '.') . 'đ', 'icon' => 'bi-cash'),
                array('label' => 'Đơn hàng', 'value' => $this->safeCount('orders'), 'icon' => 'bi-receipt'),
                array('label' => 'Sản phẩm', 'value' => $this->safeCount('product'), 'icon' => 'bi-box'),
                array('label' => 'Khách hàng', 'value' => $this->safeCount('account'), 'icon' => 'bi-people')
            ),
            'actions' => array(
                array('label' => 'Dashboard', 'url' => BASE_URL . '/Admin/dashboard', 'class' => 'btn-success'),
                array('label' => 'Xuất Excel/PDF', 'url' => '#', 'class' => 'btn-outline-success')
            ),
            'features' => array(
                'Doanh thu theo ngày / tháng / năm',
                'Sản phẩm bán chạy nhất',
                'Tỷ lệ chuyển đổi',
                'Báo cáo tồn kho & lợi nhuận',
                'Thống kê lượt truy cập',
                'Phân tích hành vi khách hàng',
                'Xuất báo cáo PDF / Excel'
            ),
            'rowsTitle' => 'Sản phẩm bán chạy',
            'rows' => $this->safeRows('SELECT id, name, sold_count, stock_quantity, price FROM product ORDER BY sold_count DESC, id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'name' => 'Sản phẩm', 'sold_count' => 'Đã bán', 'stock_quantity' => 'Kho', 'price' => 'Giá')
        );
        $this->renderModule($config);
    }

    public function staffCenter()
    {
        $config = array(
            'title' => 'Nhân sự & Phân quyền',
            'subtitle' => 'Tài khoản nhân viên, RBAC, audit log, giới hạn IP, 2FA, ca làm việc và reset mật khẩu.',
            'icon' => 'bi-shield-lock',
            'color' => '#111827',
            'stats' => array(
                array('label' => 'Admin', 'value' => $this->safeCount('account', "WHERE role='admin'"), 'icon' => 'bi-shield-check'),
                array('label' => 'User', 'value' => $this->safeCount('account', "WHERE role='user'"), 'icon' => 'bi-person'),
                array('label' => 'Audit log', 'value' => $this->safeCount('admin_logs'), 'icon' => 'bi-journal-text'),
                array('label' => 'Khoá', 'value' => $this->safeCount('account', "WHERE is_active=0"), 'icon' => 'bi-lock')
            ),
            'actions' => array(
                array('label' => 'Quản lý người dùng', 'url' => BASE_URL . '/User/admin', 'class' => 'btn-dark'),
                array('label' => 'Admin Center', 'url' => BASE_URL . '/Admin/center', 'class' => 'btn-outline-dark')
            ),
            'features' => array(
                'Tạo tài khoản nhân viên',
                'Phân quyền theo vai trò RBAC',
                'Nhật ký hoạt động audit log',
                'Giới hạn truy cập theo IP',
                'Xác thực 2 bước 2FA',
                'Quản lý ca làm việc',
                'Reset mật khẩu nhân viên'
            ),
            'rowsTitle' => 'Tài khoản hệ thống',
            'rows' => $this->safeRows('SELECT id, username, email, full_name, role, is_active, created_at FROM account ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'username' => 'Username', 'email' => 'Email', 'full_name' => 'Họ tên', 'role' => 'Vai trò', 'is_active' => 'Active', 'created_at' => 'Ngày tạo')
        );
        $this->renderModule($config);
    }

    public function settingsCenter()
    {
        $config = array(
            'title' => 'Cài đặt hệ thống',
            'subtitle' => 'Thông tin cửa hàng, thanh toán, vận chuyển, phí ship, CMS, backup, API, webhook, ngôn ngữ và tiền tệ.',
            'icon' => 'bi-sliders',
            'color' => '#0891b2',
            'stats' => array(
                array('label' => 'FAQ', 'value' => $this->safeCount('faq'), 'icon' => 'bi-question-circle'),
                array('label' => 'Blog', 'value' => $this->safeCount('blog_posts'), 'icon' => 'bi-newspaper'),
                array('label' => 'Banner', 'value' => $this->safeCount('banners'), 'icon' => 'bi-images'),
                array('label' => 'Phương thức TT', 'value' => 7, 'icon' => 'bi-credit-card')
            ),
            'actions' => array(
                array('label' => 'CMS nội dung', 'url' => BASE_URL . '/Admin/content', 'class' => 'btn-info'),
                array('label' => 'Banner', 'url' => BASE_URL . '/Admin/banners', 'class' => 'btn-outline-info')
            ),
            'features' => array(
                'Cấu hình thông tin cửa hàng',
                'Cài đặt phương thức thanh toán',
                'Cài đặt vận chuyển & phí',
                'Quản lý trang CMS About / Policy',
                'Sao lưu & phục hồi dữ liệu',
                'Tích hợp API / webhook',
                'Cài đặt ngôn ngữ & tiền tệ'
            ),
            'rowsTitle' => 'Nội dung hệ thống',
            'rows' => $this->safeRows('SELECT id, question, sort_order, is_active FROM faq ORDER BY sort_order ASC, id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'question' => 'FAQ', 'sort_order' => 'Thứ tự', 'is_active' => 'Active')
        );
        $this->renderModule($config);
    }

    public function supportCenter()
    {
        $config = array(
            'title' => 'Hỗ trợ & Nội dung',
            'subtitle' => 'Ticket hỗ trợ, chatbot, bình luận, FAQ, blog, duyệt review, popup, email tự động và live chat.',
            'icon' => 'bi-chat-dots',
            'color' => '#0f766e',
            'stats' => array(
                array('label' => 'Ticket', 'value' => $this->safeCount('support_tickets'), 'icon' => 'bi-ticket-detailed'),
                array('label' => 'FAQ', 'value' => $this->safeCount('faq'), 'icon' => 'bi-question-circle'),
                array('label' => 'Blog', 'value' => $this->safeCount('blog_posts'), 'icon' => 'bi-newspaper'),
                array('label' => 'Đánh giá', 'value' => $this->safeCount('reviews'), 'icon' => 'bi-star')
            ),
            'actions' => array(
                array('label' => 'CMS / FAQ / Blog', 'url' => BASE_URL . '/Admin/content', 'class' => 'btn-success'),
                array('label' => 'Trang hỗ trợ', 'url' => BASE_URL . '/Advanced/support', 'class' => 'btn-outline-success')
            ),
            'features' => array(
                'Quản lý ticket hỗ trợ khách hàng',
                'Cấu hình chatbot tự động',
                'Trả lời đánh giá / bình luận',
                'Quản lý FAQ & hướng dẫn',
                'Đăng bài blog / tin tức',
                'Duyệt / ẩn đánh giá người dùng',
                'Quản lý popup & thông báo web',
                'Cấu hình email tự động',
                'Quản lý live chat'
            ),
            'rowsTitle' => 'FAQ mới nhất',
            'rows' => $this->safeRows('SELECT id, question, sort_order, is_active FROM faq ORDER BY id DESC LIMIT 8'),
            'columns' => array('id' => 'ID', 'question' => 'Câu hỏi', 'sort_order' => 'Thứ tự', 'is_active' => 'Active')
        );
        $this->renderModule($config);
    }



    private function columnExists($table, $column)
    {
        static $cache = array();
        $key = $table . '.' . $column;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column");
            $stmt->execute(array(':table' => $table, ':column' => $column));
            $cache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Exception $e) {
            $cache[$key] = false;
        }

        return $cache[$key];
    }

    private function insertByExistingColumns($table, $data)
    {
        $columns = array();
        $params = array();
        $values = array();

        foreach ($data as $column => $value) {
            if ($this->columnExists($table, $column)) {
                $columns[] = '`' . $column . '`';
                $params[] = ':' . $column;
                $values[':' . $column] = $value;
            }
        }

        if (empty($columns)) {
            return false;
        }

        $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function center()
    {
        SessionHelper::requireAdmin();

        $stats = array(
            'orders' => 0,
            'revenue' => 0,
            'users' => 0,
            'products' => 0,
            'low_stock' => 0,
            'pending_orders' => 0
        );

        try { $stats['orders'] = (int)$this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(); } catch (Exception $e) {}
        try { $stats['revenue'] = (float)$this->db->query('SELECT COALESCE(SUM(COALESCE(final_amount,total_amount,0)),0) FROM orders')->fetchColumn(); } catch (Exception $e) {}
        try { $stats['users'] = (int)$this->db->query('SELECT COUNT(*) FROM account')->fetchColumn(); } catch (Exception $e) {}
        try { $stats['products'] = (int)$this->db->query('SELECT COUNT(*) FROM product')->fetchColumn(); } catch (Exception $e) {}
        try { $stats['low_stock'] = (int)$this->db->query('SELECT COUNT(*) FROM product WHERE stock_quantity <= 5')->fetchColumn(); } catch (Exception $e) {}
        try { $stats['pending_orders'] = (int)$this->db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('pending','processing','confirmed')")->fetchColumn(); } catch (Exception $e) {}

        $adminModules = array(
            array(
                'title' => 'Quản lý sản phẩm',
                'icon' => 'bi-box-seam',
                'color' => 'admin-red',
                'desc' => 'Thêm, sửa, xoá sản phẩm; danh mục, thương hiệu, ảnh, video, biến thể, giá khuyến mãi và SEO.',
                'links' => array(
                    array('label' => 'Danh sách sản phẩm', 'url' => BASE_URL . '/Product/list'),
                    array('label' => 'Thêm sản phẩm', 'url' => BASE_URL . '/Product/add'),
                    array('label' => 'Danh mục', 'url' => BASE_URL . '/Category/list'),
                    array('label' => 'Banner sản phẩm', 'url' => BASE_URL . '/Admin/banners')
                ),
                'features' => array('Upload ảnh/video', 'Màu & dung lượng', 'Giá khuyến mãi', 'Hiển thị / ẩn', 'SEO slug', 'Import Excel/CSV')
            ),
            array(
                'title' => 'Quản lý kho hàng',
                'icon' => 'bi-building-gear',
                'color' => 'admin-orange',
                'desc' => 'Theo dõi tồn kho, nhập xuất kho, cảnh báo hàng sắp hết, kiểm kê và báo cáo xuất nhập tồn.',
                'links' => array(
                    array('label' => 'Kho hàng', 'url' => BASE_URL . '/Admin/inventory'),
                    array('label' => 'Sản phẩm sắp hết', 'url' => BASE_URL . '/Admin/inventory')
                ),
                'features' => array('Tồn theo SKU', 'Lịch sử kho', 'Ngưỡng tồn tối thiểu', 'Nhiều chi nhánh', 'QR/barcode')
            ),
            array(
                'title' => 'Quản lý đơn hàng',
                'icon' => 'bi-receipt-cutoff',
                'color' => 'admin-blue',
                'desc' => 'Xem, lọc, cập nhật trạng thái đơn, xử lý đổi trả, hoàn tiền, in hoá đơn và phiếu giao hàng.',
                'links' => array(
                    array('label' => 'Danh sách đơn', 'url' => BASE_URL . '/Admin/orders'),
                    array('label' => 'Báo cáo đơn', 'url' => BASE_URL . '/Admin/dashboard')
                ),
                'features' => array('Lọc đơn', 'Cập nhật trạng thái', 'In hoá đơn', 'Đổi trả/hoàn tiền', 'Xuất Excel')
            ),
            array(
                'title' => 'Quản lý khách hàng',
                'icon' => 'bi-people',
                'color' => 'admin-purple',
                'desc' => 'Xem hồ sơ khách hàng, lịch sử mua, hạng thành viên, điểm thưởng, khoá/mở khoá tài khoản.',
                'links' => array(
                    array('label' => 'Người dùng', 'url' => BASE_URL . '/User/admin'),
                    array('label' => 'Hồ sơ cá nhân', 'url' => BASE_URL . '/User/profile')
                ),
                'features' => array('VIP / thường / mới', 'Lịch sử mua', 'Điểm thưởng', 'Thông báo trực tiếp', 'Marketing list')
            ),
            array(
                'title' => 'Khuyến mãi & Marketing',
                'icon' => 'bi-megaphone',
                'color' => 'admin-pink',
                'desc' => 'Quản lý coupon, flash sale, banner, tích điểm đổi quà, push notification và email marketing.',
                'links' => array(
                    array('label' => 'Voucher', 'url' => BASE_URL . '/Admin/vouchers'),
                    array('label' => 'Banner', 'url' => BASE_URL . '/Admin/banners')
                ),
                'features' => array('Coupon', 'Flash sale', 'Tích điểm', 'Banner trang chủ', 'Giới thiệu bạn bè')
            ),
            array(
                'title' => 'Báo cáo & Thống kê',
                'icon' => 'bi-graph-up-arrow',
                'color' => 'admin-green',
                'desc' => 'Theo dõi doanh thu, sản phẩm bán chạy, tồn kho, lợi nhuận, hành vi khách hàng và xuất báo cáo.',
                'links' => array(
                    array('label' => 'Dashboard', 'url' => BASE_URL . '/Admin/dashboard'),
                    array('label' => 'Kho hàng', 'url' => BASE_URL . '/Admin/inventory')
                ),
                'features' => array('Ngày / tháng / năm', 'Best seller', 'Conversion', 'Lợi nhuận', 'PDF / Excel')
            ),
            array(
                'title' => 'Nhân sự & Phân quyền',
                'icon' => 'bi-shield-lock',
                'color' => 'admin-dark',
                'desc' => 'Tài khoản nhân viên, phân quyền Admin/User, nhật ký hoạt động, reset mật khẩu và bảo mật truy cập.',
                'links' => array(
                    array('label' => 'Quản lý user', 'url' => BASE_URL . '/User/admin')
                ),
                'features' => array('RBAC', 'Audit log', 'Giới hạn IP', '2FA', 'Ca làm việc', 'Reset mật khẩu')
            ),
            array(
                'title' => 'Cài đặt hệ thống',
                'icon' => 'bi-sliders',
                'color' => 'admin-cyan',
                'desc' => 'Thông tin cửa hàng, thanh toán, vận chuyển, phí ship, CMS, sao lưu dữ liệu và tích hợp API.',
                'links' => array(
                    array('label' => 'CMS nội dung', 'url' => BASE_URL . '/Admin/content'),
                    array('label' => 'FAQ', 'url' => BASE_URL . '/Admin/content')
                ),
                'features' => array('Thông tin shop', 'Thanh toán', 'Vận chuyển', 'Backup', 'API/webhook', 'Ngôn ngữ')
            ),
            array(
                'title' => 'Hỗ trợ & Nội dung',
                'icon' => 'bi-chat-dots',
                'color' => 'admin-teal',
                'desc' => 'Ticket hỗ trợ, chatbot, bình luận, FAQ, blog, duyệt đánh giá, popup và live chat.',
                'links' => array(
                    array('label' => 'CMS / FAQ / Blog', 'url' => BASE_URL . '/Admin/content'),
                    array('label' => 'Trang hỗ trợ', 'url' => BASE_URL . '/Advanced/support')
                ),
                'features' => array('Ticket', 'Chatbot', 'FAQ', 'Blog', 'Duyệt review', 'Live chat')
            )
        );

        $pageTitle = 'Trung tâm quản trị Admin';
        include 'app/views/admin/center.php';
    }

    public function dashboard()
    {
        SessionHelper::requireAdmin();
        $stats = array(
            'orders' => (int)$this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'revenue' => (float)$this->db->query('SELECT COALESCE(SUM(final_amount),0) FROM orders')->fetchColumn(),
            'users' => (int)$this->db->query('SELECT COUNT(*) FROM account')->fetchColumn(),
            'products' => (int)$this->db->query('SELECT COUNT(*) FROM product')->fetchColumn()
        );
        $recentOrders = $this->db->query('SELECT * FROM orders ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Báo cáo quản trị';
        include 'app/views/admin/dashboard.php';
    }

    public function orders()
    {
        SessionHelper::requireAdmin();
        $orders = $this->db->query('SELECT o.*, a.username FROM orders o LEFT JOIN account a ON a.id=o.account_id ORDER BY o.id DESC')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Quản lý đơn hàng';
        include 'app/views/admin/orders.php';
    }

    public function updateOrder($id)
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['order_status'] ?? 'pending';
            $payment = $_POST['payment_status'] ?? 'unpaid';
            $note = trim($_POST['note'] ?? '');
            // Lấy trạng thái cũ để ghi log tương thích database cũ/mới.
            $oldStatus = null;
            try {
                $oldStmt = $this->db->prepare('SELECT order_status FROM orders WHERE id=:id LIMIT 1');
                $oldStmt->execute(array(':id'=>(int)$id));
                $oldStatus = $oldStmt->fetchColumn();
            } catch (Exception $e) {}

            $stmt = $this->db->prepare('UPDATE orders SET order_status=:st, payment_status=:pay WHERE id=:id');
            $stmt->execute(array(':st'=>$status, ':pay'=>$payment, ':id'=>(int)$id));

            // Bảng order_status_logs có bản dùng status, có bản dùng old_status/new_status.
            // Insert theo cột đang tồn tại để tránh lỗi Field new_status doesn't have a default value.
            $this->insertByExistingColumns('order_status_logs', array(
                'order_id' => (int)$id,
                'status' => $status,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'note' => $note,
                'created_by' => !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
            ));

            $_SESSION['flash_success'] = 'Đã cập nhật đơn hàng.';
        }
        header('Location: ' . BASE_URL . '/Admin/orders'); exit();
    }

    public function vouchers()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare('INSERT INTO vouchers (code,name,type,value,min_order_value,usage_limit,is_active) VALUES (:code,:name,:type,:value,:minv,:limit,1)');
            $stmt->execute(array(':code'=>strtoupper(trim($_POST['code'] ?? '')), ':name'=>trim($_POST['name'] ?? ''), ':type'=>$_POST['type'] ?? 'fixed', ':value'=>(float)($_POST['value'] ?? 0), ':minv'=>(float)($_POST['min_order_value'] ?? 0), ':limit'=>(int)($_POST['usage_limit'] ?? 100)));
            $_SESSION['flash_success'] = 'Đã thêm voucher.'; header('Location: ' . BASE_URL . '/Admin/vouchers'); exit();
        }
        $vouchers = $this->db->query('SELECT * FROM vouchers ORDER BY id DESC')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Quản lý voucher';
        include 'app/views/admin/vouchers.php';
    }

    public function inventory()
    {
        SessionHelper::requireAdmin();
        $products = $this->db->query('SELECT p.*, c.name AS category_name FROM product p LEFT JOIN category c ON c.id=p.category_id ORDER BY p.id DESC')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Quản lý kho hàng';
        include 'app/views/admin/inventory.php';
    }

    public function updateStock($id)
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stock = max(0, (int)($_POST['stock_quantity'] ?? 0));
            $stmt = $this->db->prepare('UPDATE product SET stock_quantity=:stock WHERE id=:id');
            $stmt->execute(array(':stock'=>$stock, ':id'=>(int)$id));
            $log = $this->db->prepare('INSERT INTO inventory_logs (product_id, change_quantity, type, note) VALUES (:pid,:qty,:type,:note)');
            $log->execute(array(':pid'=>(int)$id, ':qty'=>$stock, ':type'=>'manual_update', ':note'=>'Cập nhật tồn kho thủ công'));
            $_SESSION['flash_success'] = 'Đã cập nhật kho.';
        }
        header('Location: ' . BASE_URL . '/Admin/inventory'); exit();
    }

    public function content()
    {
        SessionHelper::requireAdmin();
        $faqs = $this->db->query('SELECT * FROM faq ORDER BY sort_order ASC, id DESC')->fetchAll(PDO::FETCH_OBJ);
        $posts = $this->db->query('SELECT * FROM blog_posts ORDER BY id DESC')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'CMS nội dung';
        include 'app/views/admin/content.php';
    }



    public function banners()
    {
        SessionHelper::requireAdmin();
        $this->ensureBannerTable();
        $banners = $this->db->query("SELECT * FROM banners ORDER BY FIELD(position,'home_main','home_mini','home_side'), id ASC")->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Quản lý banner';
        include 'app/views/admin/banners.php';
    }

    public function updateBanner($id)
    {
        SessionHelper::requireAdmin();
        $this->ensureBannerTable();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/Admin/banners');
            exit();
        }

        $id = (int)$id;
        $stmt = $this->db->prepare('SELECT * FROM banners WHERE id=:id LIMIT 1');
        $stmt->execute(array(':id' => $id));
        $banner = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$banner) {
            $_SESSION['flash_error'] = 'Không tìm thấy banner.';
            header('Location: ' . BASE_URL . '/Admin/banners');
            exit();
        }

        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $position = $_POST['position'] ?? 'home_main';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $image = $banner->image;

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploaded = $this->uploadBannerImage();
            if ($uploaded !== '') {
                $image = $uploaded;
            }
        }

        if ($title === '') {
            $_SESSION['flash_error'] = 'Tiêu đề banner không được để trống.';
            header('Location: ' . BASE_URL . '/Admin/banners');
            exit();
        }

        $stmt = $this->db->prepare('UPDATE banners SET title=:title, image=:image, position=:position, link=:link, is_active=:active WHERE id=:id');
        $stmt->execute(array(
            ':title' => $title,
            ':image' => $image,
            ':position' => $position,
            ':link' => $link,
            ':active' => $isActive,
            ':id' => $id
        ));

        $_SESSION['flash_success'] = 'Đã cập nhật banner.';
        header('Location: ' . BASE_URL . '/Admin/banners');
        exit();
    }

    public function addBanner()
    {
        SessionHelper::requireAdmin();
        $this->ensureBannerTable();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? 'Banner mới');
            $position = $_POST['position'] ?? 'home_main';
            $link = trim($_POST['link'] ?? '');
            $image = '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $image = $this->uploadBannerImage();
            }

            $stmt = $this->db->prepare('INSERT INTO banners (title, image, position, link, is_active) VALUES (:title,:image,:position,:link,1)');
            $stmt->execute(array(':title'=>$title, ':image'=>$image, ':position'=>$position, ':link'=>$link));
            $_SESSION['flash_success'] = 'Đã thêm banner mới.';
        }

        header('Location: ' . BASE_URL . '/Admin/banners');
        exit();
    }

    private function uploadBannerImage()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Tải ảnh banner thất bại.';
            return '';
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg','jpeg','png','webp','gif'))) {
            $_SESSION['flash_error'] = 'Banner chỉ chấp nhận JPG, PNG, WEBP hoặc GIF.';
            return '';
        }

        if ((int)$_FILES['image']['size'] > 8 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Ảnh banner không được vượt quá 8MB.';
            return '';
        }

        $dir = __DIR__ . '/../../public/uploads/banners/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = time() . '_' . uniqid('banner_', true) . '.' . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
            $_SESSION['flash_error'] = 'Không thể lưu ảnh banner.';
            return '';
        }

        return 'banners/' . $filename;
    }

    private function ensureBannerTable()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS banners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            image VARCHAR(255) NULL,
            position VARCHAR(50) DEFAULT 'home_main',
            link VARCHAR(255) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function addFaq()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare('INSERT INTO faq (question, answer, sort_order) VALUES (:q,:a,:s)');
            $stmt->execute(array(':q'=>trim($_POST['question'] ?? ''), ':a'=>trim($_POST['answer'] ?? ''), ':s'=>(int)($_POST['sort_order'] ?? 0)));
            $_SESSION['flash_success'] = 'Đã thêm FAQ.';
        }
        header('Location: ' . BASE_URL . '/Admin/content'); exit();
    }
}
?>

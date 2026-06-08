<?php
require_once 'config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/helpers/SessionHelper.php';

class AdvancedController
{
    private $db;
    private $productModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function orders()
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE account_id = :uid ORDER BY id DESC');
        $stmt->execute(array(':uid' => (int)$_SESSION['user_id']));
        $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Lịch sử mua hàng';
        include 'app/views/advanced/orders.php';
    }

    public function orderDetail($id)
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id AND (account_id = :uid OR :is_admin = 1)');
        $stmt->execute(array(':id'=>(int)$id, ':uid'=>(int)$_SESSION['user_id'], ':is_admin'=>SessionHelper::isAdmin()?1:0));
        $order = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$order) { die('Không tìm thấy đơn hàng.'); }
        $stmt = $this->db->prepare('SELECT od.*, p.name, p.image FROM order_details od LEFT JOIN product p ON p.id=od.product_id WHERE od.order_id = :id');
        $stmt->execute(array(':id'=>(int)$id));
        $items = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt = $this->db->prepare('SELECT * FROM order_status_logs WHERE order_id=:id ORDER BY id DESC');
        $stmt->execute(array(':id'=>(int)$id));
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Chi tiết đơn hàng';
        include 'app/views/advanced/order_detail.php';
    }

    public function wishlist()
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('SELECT w.*, p.name, p.price, p.image FROM wishlist w JOIN product p ON p.id=w.product_id WHERE w.account_id=:uid ORDER BY w.id DESC');
        $stmt->execute(array(':uid'=>(int)$_SESSION['user_id']));
        $items = $stmt->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Sản phẩm yêu thích';
        include 'app/views/advanced/wishlist.php';
    }

    public function addWishlist($productId)
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('INSERT IGNORE INTO wishlist (account_id, product_id) VALUES (:uid, :pid)');
        $stmt->execute(array(':uid'=>(int)$_SESSION['user_id'], ':pid'=>(int)$productId));
        $_SESSION['flash_success'] = 'Đã thêm vào danh sách yêu thích.';
        header('Location: ' . BASE_URL . '/Advanced/wishlist'); exit();
    }

    public function removeWishlist($id)
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('DELETE FROM wishlist WHERE id=:id AND account_id=:uid');
        $stmt->execute(array(':id'=>(int)$id, ':uid'=>(int)$_SESSION['user_id']));
        $_SESSION['flash_success'] = 'Đã xoá khỏi wishlist.';
        header('Location: ' . BASE_URL . '/Advanced/wishlist'); exit();
    }

    public function compare()
    {
        $ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));
        if (empty($ids) && !empty($_SESSION['compare'])) { $ids = $_SESSION['compare']; }
        $products = array();
        if (!empty($ids)) {
            $place = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare('SELECT * FROM product WHERE id IN (' . $place . ')');
            $stmt->execute($ids);
            $products = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        $pageTitle = 'So sánh sản phẩm';
        include 'app/views/advanced/compare.php';
    }

    public function addCompare($productId)
    {
        if (empty($_SESSION['compare'])) $_SESSION['compare'] = array();
        if (!in_array((int)$productId, $_SESSION['compare'])) $_SESSION['compare'][] = (int)$productId;
        $_SESSION['compare'] = array_slice($_SESSION['compare'], -4);
        $_SESSION['flash_success'] = 'Đã thêm sản phẩm vào danh sách so sánh.';
        header('Location: ' . BASE_URL . '/Advanced/compare'); exit();
    }

    public function notifications()
    {
        SessionHelper::requireLogin();
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE account_id IS NULL OR account_id=:uid ORDER BY id DESC');
        $stmt->execute(array(':uid'=>(int)$_SESSION['user_id']));
        $notifications = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->db->prepare('UPDATE notifications SET is_read=1 WHERE account_id=:uid')->execute(array(':uid'=>(int)$_SESSION['user_id']));
        $pageTitle = 'Thông báo';
        include 'app/views/advanced/notifications.php';
    }

    public function vouchers()
    {
        $stmt = $this->db->query("SELECT * FROM vouchers WHERE is_active=1 ORDER BY id DESC");
        $vouchers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Voucher cá nhân';
        include 'app/views/advanced/vouchers.php';
    }

    public function review($productId)
    {
        SessionHelper::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $comment = trim($_POST['comment'] ?? '');
            $stmt = $this->db->prepare('INSERT INTO reviews (product_id, account_id, rating, comment) VALUES (:pid,:uid,:rating,:comment)');
            $stmt->execute(array(':pid'=>(int)$productId, ':uid'=>(int)$_SESSION['user_id'], ':rating'=>$rating, ':comment'=>$comment));
            $_SESSION['flash_success'] = 'Đã gửi đánh giá sản phẩm.';
        }
        header('Location: ' . BASE_URL . '/Product/show/' . (int)$productId); exit();
    }

    public function question($productId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $question = trim($_POST['question'] ?? '');
            if ($question !== '') {
                $stmt = $this->db->prepare('INSERT INTO product_questions (product_id, account_id, question) VALUES (:pid,:uid,:q)');
                $stmt->execute(array(':pid'=>(int)$productId, ':uid'=>$_SESSION['user_id'] ?? null, ':q'=>$question));
                $_SESSION['flash_success'] = 'Đã gửi câu hỏi sản phẩm.';
            }
        }
        header('Location: ' . BASE_URL . '/Product/show/' . (int)$productId); exit();
    }

    public function warranty()
    {
        $imei = trim($_GET['imei'] ?? '');
        $warranty = null;
        if ($imei !== '') {
            $stmt = $this->db->prepare('SELECT w.*, p.name AS product_name FROM warranty w JOIN product p ON p.id=w.product_id WHERE w.imei=:imei LIMIT 1');
            $stmt->execute(array(':imei'=>$imei));
            $warranty = $stmt->fetch(PDO::FETCH_OBJ);
        }
        $pageTitle = 'Tra cứu bảo hành';
        include 'app/views/advanced/warranty.php';
    }

    public function returnRequest($orderId)
    {
        SessionHelper::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = trim($_POST['reason'] ?? '');
            if ($reason !== '') {
                $stmt = $this->db->prepare('INSERT INTO return_requests (order_id, account_id, reason) VALUES (:oid,:uid,:reason)');
                $stmt->execute(array(':oid'=>(int)$orderId, ':uid'=>(int)$_SESSION['user_id'], ':reason'=>$reason));
                $_SESSION['flash_success'] = 'Đã gửi yêu cầu đổi trả/hoàn tiền.';
            }
            header('Location: ' . BASE_URL . '/Advanced/orderDetail/' . (int)$orderId); exit();
        }
        $pageTitle = 'Yêu cầu đổi trả';
        include 'app/views/advanced/return_request.php';
    }

    public function support()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            if ($subject !== '' && $message !== '') {
                $stmt = $this->db->prepare('INSERT INTO support_tickets (account_id, subject, message) VALUES (:uid,:subject,:message)');
                $stmt->execute(array(':uid'=>$_SESSION['user_id'] ?? null, ':subject'=>$subject, ':message'=>$message));
                $_SESSION['flash_success'] = 'Đã gửi ticket hỗ trợ.';
                header('Location: ' . BASE_URL . '/Advanced/support'); exit();
            }
        }
        $tickets = array();
        if (!empty($_SESSION['user_id'])) {
            $stmt = $this->db->prepare('SELECT * FROM support_tickets WHERE account_id=:uid ORDER BY id DESC');
            $stmt->execute(array(':uid'=>(int)$_SESSION['user_id']));
            $tickets = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        $pageTitle = 'Hỗ trợ khách hàng';
        include 'app/views/advanced/support.php';
    }

    public function faq()
    {
        $faqs = $this->db->query('SELECT * FROM faq WHERE is_active=1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'FAQ';
        include 'app/views/advanced/faq.php';
    }

    public function blog()
    {
        $posts = $this->db->query("SELECT * FROM blog_posts WHERE status='published' ORDER BY id DESC")->fetchAll(PDO::FETCH_OBJ);
        $pageTitle = 'Tin công nghệ';
        include 'app/views/advanced/blog.php';
    }

    public function tradeIn()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['old_device_name'] ?? '');
            $imei = trim($_POST['imei'] ?? '');
            $note = trim($_POST['condition_note'] ?? '');
            if ($name !== '') {
                $stmt = $this->db->prepare('INSERT INTO trade_in_requests (account_id, old_device_name, imei, condition_note) VALUES (:uid,:name,:imei,:note)');
                $stmt->execute(array(':uid'=>$_SESSION['user_id'] ?? null, ':name'=>$name, ':imei'=>$imei, ':note'=>$note));
                $_SESSION['flash_success'] = 'Đã gửi yêu cầu thu cũ đổi mới.';
                header('Location: ' . BASE_URL . '/Advanced/tradeIn'); exit();
            }
        }
        $pageTitle = 'Thu cũ đổi mới';
        include 'app/views/advanced/trade_in.php';
    }
}
?>

<?php
require_once 'config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/helpers/MembershipHelper.php';

class ProductController
{
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index()
    {
        $this->list();
    }

    public function list()
    {
        $searchQuery = trim($_GET['search'] ?? '');

        // Lọc danh mục khi bấm menu bên trái.
        // Hỗ trợ cả ?category=ID và ?category_id=ID để tránh lỗi link cũ/link mới.
        $categoryParam = $_GET['category'] ?? ($_GET['category_id'] ?? null);
        $categoryId = null;
        if ($categoryParam !== null && filter_var($categoryParam, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $categoryId = (int)$categoryParam;
        }

        $products = $this->productModel->getProducts($searchQuery, $categoryId);
        $categories = $this->categoryModel->getCategories();
        $selectedCategoryId = $categoryId;

        // Banner và voucher cuối tuần lấy từ database để Admin có thể chỉnh trực tiếp.
        $mainBanners = array();
        $miniBanners = array();
        $weekendVouchers = array();

        try {
            $stmt = $this->db->query("SELECT * FROM banners WHERE is_active=1 AND position='home_main' ORDER BY id ASC LIMIT 3");
            $mainBanners = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = $this->db->query("SELECT * FROM banners WHERE is_active=1 AND position='home_mini' ORDER BY id ASC LIMIT 3");
            $miniBanners = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = $this->db->query("SELECT * FROM vouchers WHERE is_active=1 ORDER BY id DESC LIMIT 3");
            $weekendVouchers = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            // Nếu database cũ chưa có bảng banners/vouchers thì trang sản phẩm vẫn chạy bằng banner mặc định.
        }

        $pageTitle = $searchQuery !== '' ? 'Tìm kiếm sản phẩm' : 'Danh sách sản phẩm';
        include 'app/views/product/list.php';
    }

    public function add()
    {
        SessionHelper::requireAdmin();

        $errors = array();
        $categories = $this->categoryModel->getCategories();

        $old = array(
            'name' => '',
            'description' => '',
            'price' => '',
            'category_id' => ''
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array(
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price' => trim($_POST['price'] ?? ''),
                'category_id' => trim($_POST['category_id'] ?? '')
            );

            $image = $this->uploadImage($errors);

            if (empty($errors)) {
                $result = $this->productModel->addProduct(
                    $old['name'],
                    $old['description'],
                    $old['price'],
                    $old['category_id'],
                    $image
                );

                if ($result === true) {
                    $_SESSION['flash_success'] = 'Thêm sản phẩm thành công.';
                    header('Location: ' . BASE_URL . '/Product/list');
                    exit();
                }

                $errors = is_array($result) ? $result : array('Không thể thêm sản phẩm.');
            }
        }

        $pageTitle = 'Thêm sản phẩm';
        include 'app/views/product/add.php';
    }

    // Alias cho router cũ nếu còn gọi /Product/save
    public function save()
    {
        $this->add();
    }

    public function edit($id)
    {
        SessionHelper::requireAdmin();

        $product = $this->productModel->getProductById((int)$id);

        if (!$product) {
            die('Không thấy sản phẩm.');
        }

        $categories = $this->categoryModel->getCategories();
        $errors = array();

        $old = array(
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array(
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price' => trim($_POST['price'] ?? ''),
                'category_id' => trim($_POST['category_id'] ?? '')
            );

            $image = $product->image;

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newImage = $this->uploadImage($errors);

                if ($newImage !== '') {
                    $image = $newImage;
                }
            }

            if (empty($errors)) {
                $result = $this->productModel->updateProduct(
                    (int)$id,
                    $old['name'],
                    $old['description'],
                    $old['price'],
                    $old['category_id'],
                    $image
                );

                if ($result === true) {
                    $_SESSION['flash_success'] = 'Cập nhật sản phẩm thành công.';
                    header('Location: ' . BASE_URL . '/Product/list');
                    exit();
                }

                $errors = is_array($result) ? $result : array('Không thể cập nhật sản phẩm.');
            }
        }

        $pageTitle = 'Sửa sản phẩm';
        include 'app/views/product/edit.php';
    }

    // Alias cho router cũ nếu còn gọi /Product/update
    public function update()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            die('Thiếu ID sản phẩm.');
        }

        $this->edit((int)$id);
    }

    public function delete($id)
    {
        SessionHelper::requireAdmin();

        $this->productModel->deleteProduct((int)$id);
        $_SESSION['flash_success'] = 'Xóa sản phẩm thành công.';
        header('Location: ' . BASE_URL . '/Product/list');
        exit();
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById((int)$id);

        if (!$product) {
            die('Không thấy sản phẩm.');
        }

        // Hệ thống level tu tiên: xem chi tiết sản phẩm sẽ tăng năng lượng.
        // Mỗi sản phẩm chỉ cộng 1 lần trong cùng phiên đăng nhập để tránh spam F5.
        if (!empty($_SESSION['user_id'])) {
            $viewKey = 'viewed_product_' . (int)$id;
            if (empty($_SESSION[$viewKey])) {
                $progress = MembershipHelper::addEnergy($this->db, (int)$_SESSION['user_id'], 5, 'Xem sản phẩm: ' . $product->name, 'product_view', (int)$id);
                $_SESSION[$viewKey] = 1;
                if ($progress) {
                    $_SESSION['cultivation_level'] = $progress['level'];
                    $_SESSION['cultivation_notice'] = '+5 linh khí khi xem sản phẩm. Cảnh giới hiện tại: ' . $progress['realm'];
                }
            }
        }

        $pageTitle = 'Chi tiết sản phẩm';
        include 'app/views/product/show.php';
    }

    private function uploadImage(&$errors)
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Tải ảnh lên thất bại.';
            return '';
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
            $errors[] = 'Ảnh chỉ chấp nhận JPG, PNG, GIF, WEBP.';
            return '';
        }

        if ((int)$_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Ảnh không được vượt quá 5MB.';
            return '';
        }

        $dir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $name = time() . '_' . uniqid('product_', true) . '.' . $ext;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $name)) {
            $errors[] = 'Không thể lưu ảnh.';
            return '';
        }

        return $name;
    }

    // =========================
    // CHỨC NĂNG GIỎ HÀNG
    // =========================

    public function addToCart($id)
    {
        $product = $this->productModel->getProductById((int)$id);

        if (!$product) {
            die('Không tìm thấy sản phẩm.');
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = array(
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image
            );
        }

        $_SESSION['flash_success'] = 'Đã thêm sản phẩm vào giỏ hàng.';
        header('Location: ' . BASE_URL . '/Product/cart');
        exit();
    }

    public function cart()
    {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        $pageTitle = 'Giỏ hàng';
        include 'app/views/product/cart.php';
    }

    public function checkout()
    {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['flash_error'] = 'Giỏ hàng đang trống.';
            header('Location: ' . BASE_URL . '/Product/cart');
            exit();
        }

        $pageTitle = 'Thanh toán';
        include 'app/views/product/checkout.php';
    }

    public function processCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/Product/checkout');
            exit();
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin đặt hàng.';
            header('Location: ' . BASE_URL . '/Product/checkout');
            exit();
        }

        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['flash_error'] = 'Giỏ hàng trống.';
            header('Location: ' . BASE_URL . '/Product/cart');
            exit();
        }

        $this->db->beginTransaction();

        try {
            $query = "INSERT INTO orders (name, phone, address) 
                      VALUES (:name, :phone, :address)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->execute();

            $order_id = $this->db->lastInsertId();

            $cart = $_SESSION['cart'];

            foreach ($cart as $product_id => $item) {
                $query = "INSERT INTO order_details 
                          (order_id, product_id, quantity, price) 
                          VALUES 
                          (:order_id, :product_id, :quantity, :price)";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
            }

            unset($_SESSION['cart']);

            $this->db->commit();

            $_SESSION['flash_success'] = 'Đặt hàng thành công.';
            header('Location: ' . BASE_URL . '/Product/orderConfirmation');
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            echo 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage();
        }
    }

    public function orderConfirmation()
    {
        $pageTitle = 'Xác nhận đơn hàng';
        include 'app/views/product/orderConfirmation.php';
    }
}
?>
<?php
require_once 'config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CartModel.php';
require_once 'app/helpers/MembershipHelper.php';

class CartController
{
    private $db;
    private $productModel;
    private $cartModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->cartModel = new CartModel($this->db);
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
            throw new Exception('Không có cột phù hợp để ghi dữ liệu vào bảng ' . $table);
        }

        $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return (int)$this->db->lastInsertId();
    }

    private function getEnumValues($table, $column)
    {
        try {
            $stmt = $this->db->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1");
            $stmt->execute(array(':table' => $table, ':column' => $column));
            $type = (string)$stmt->fetchColumn();

            if (stripos($type, 'enum(') !== 0) {
                return array();
            }

            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $type, $matches);
            return array_map('stripslashes', $matches[1] ?? array());
        } catch (Exception $e) {
            return array();
        }
    }

    private function pickEnumValue($table, $column, $preferred, $fallbacks = array())
    {
        $values = $this->getEnumValues($table, $column);

        if (empty($values)) {
            return $preferred;
        }

        if (in_array($preferred, $values, true)) {
            return $preferred;
        }

        foreach ($fallbacks as $fallback) {
            if (in_array($fallback, $values, true)) {
                return $fallback;
            }
        }

        return $values[0];
    }

    private function getUserPrivilege($accountId, $subtotal)
    {
        $default = array(
            'tier' => 'bac',
            'level' => 1,
            'tier_discount_rate' => 0.005,
            'level_discount_rate' => 0,
            'shipping_discount_rate' => 0,
            'max_stack_discount_rate' => 0.10,
            'total_discount_rate' => 0.005,
            'benefits' => array('Hạng Bạc: giảm thêm 0.5% khi mua hàng')
        );

        if (empty($accountId)) {
            return array(
                'tier' => 'guest',
                'level' => 0,
                'tier_discount_rate' => 0,
                'level_discount_rate' => 0,
                'shipping_discount_rate' => 0,
                'max_stack_discount_rate' => 0,
                'total_discount_rate' => 0,
                'benefits' => array()
            );
        }

        try {
            $stmt = $this->db->prepare('SELECT member_tier, cultivation_level, total_spent FROM account WHERE id = :id LIMIT 1');
            $stmt->execute(array(':id' => (int)$accountId));
            $user = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            return $default;
        }

        if (!$user) {
            return $default;
        }

        $tier = $user->member_tier ?? 'bac';
        $level = max(1, (int)($user->cultivation_level ?? 1));
        $subtotal = max(0, (float)$subtotal);

        $tierDiscount = 0.005;
        $shippingDiscount = 0;
        $maxStack = 0.10;
        $benefits = array('Hạng Bạc: giảm thêm 0.5% khi mua hàng');

        if ($tier === 'vang') {
            $tierDiscount = 0.02;
            $benefits[] = 'Hạng Vàng: giảm thêm 2% khi mua hàng';
            $benefits[] = 'Freeship 50% cho đơn từ 2.000.000đ';
            if ($subtotal >= 2000000) {
                $shippingDiscount = max($shippingDiscount, 0.50);
            }
        }

        if ($tier === 'kim_cuong') {
            $tierDiscount = 0.04;
            $maxStack = 0.15;
            $shippingDiscount = 1;
            $benefits[] = 'Kim cương: giảm thêm 4% khi mua hàng';
            $benefits[] = 'Miễn phí vận chuyển mọi đơn';
            $benefits[] = 'Được cộng dồn nhiều ưu đãi hơn, trần giảm tối đa 15%';
        }

        $levelDiscount = 0;
        if ($level >= 4 && $level <= 6) {
            $levelDiscount = 0.01;
            $benefits[] = 'Trúc Cơ: giảm thêm 1% theo cảnh giới';
        } elseif ($level >= 7 && $level <= 9) {
            $levelDiscount = 0.02;
            $benefits[] = 'Kim Đan: giảm thêm 2% theo cảnh giới';
        } elseif ($level >= 10 && $level <= 12) {
            $levelDiscount = 0.03;
            $benefits[] = 'Nguyên Anh: giảm thêm 3% theo cảnh giới';
        } elseif ($level >= 13) {
            $levelDiscount = 0.05;
            $maxStack = max($maxStack, 0.18);
            $benefits[] = 'Hóa Thần trở lên: giảm thêm 5% theo cảnh giới';
            $benefits[] = 'Mở trần cộng dồn ưu đãi tối đa 18%';
        }

        if ($level >= 8) {
            $shippingDiscount = max($shippingDiscount, 0.50);
            $benefits[] = 'Level 8+: giảm 50% phí vận chuyển';
        }

        if ($level >= 12) {
            $shippingDiscount = 1;
            $benefits[] = 'Level 12+: miễn phí vận chuyển';
        }

        $totalDiscount = min($maxStack, $tierDiscount + $levelDiscount);

        return array(
            'tier' => $tier,
            'level' => $level,
            'tier_discount_rate' => $tierDiscount,
            'level_discount_rate' => $levelDiscount,
            'shipping_discount_rate' => $shippingDiscount,
            'max_stack_discount_rate' => $maxStack,
            'total_discount_rate' => $totalDiscount,
            'benefits' => $benefits
        );
    }

    private function formatPrivilegeNote($privilege, $privilegeDiscount, $shippingDiscount)
    {
        if (empty($privilege) || ((float)$privilegeDiscount <= 0 && (float)$shippingDiscount <= 0)) {
            return '';
        }

        $lines = array();
        $lines[] = 'Ưu đãi thành viên/tu tiên:';
        $lines[] = '- Hạng: ' . strtoupper((string)$privilege['tier']);
        $lines[] = '- Level: ' . (int)$privilege['level'];
        $lines[] = '- Giảm thêm: ' . round(((float)$privilege['total_discount_rate']) * 100, 2) . '%';
        if ((float)$privilegeDiscount > 0) {
            $lines[] = '- Tiền giảm: ' . number_format((float)$privilegeDiscount, 0, ',', '.') . 'đ';
        }
        if ((float)$shippingDiscount > 0) {
            $lines[] = '- Giảm phí vận chuyển: ' . number_format((float)$shippingDiscount, 0, ',', '.') . 'đ';
        }

        if (!empty($privilege['benefits'])) {
            $lines[] = '- Quyền lợi: ' . implode('; ', $privilege['benefits']);
        }

        return implode("\\n", $lines);
    }

    public function index()
    {
        $cartId = $this->cartModel->getOrCreateCartId(session_id());
        $items = $this->cartModel->getCartItems($cartId);
        $pageTitle = 'Giỏ hàng';

        include 'app/views/cart/index.php';
    }

    public function add($productId)
    {
        $product = $this->productModel->getProductById((int)$productId);

        if (!$product) {
            die('Không thấy sản phẩm.');
        }

        $cartId = $this->cartModel->getOrCreateCartId(session_id());
        $this->cartModel->addItem($cartId, (int)$productId, (float)$product->price, 1);

        $_SESSION['flash_success'] = 'Đã thêm sản phẩm vào giỏ hàng.';
        header('Location: ' . BASE_URL . '/Cart/index');
        exit();
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/Cart/index');
            exit();
        }

        $items = $_POST['items'] ?? array();

        foreach ($items as $itemId => $qty) {
            $qty = max(1, (int)$qty);
            $this->cartModel->updateItemQuantity((int)$itemId, $qty);
        }

        $_SESSION['flash_success'] = 'Đã cập nhật giỏ hàng.';
        header('Location: ' . BASE_URL . '/Cart/index');
        exit();
    }

    public function remove($itemId)
    {
        $this->cartModel->removeItem((int)$itemId);

        $_SESSION['flash_success'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
        header('Location: ' . BASE_URL . '/Cart/index');
        exit();
    }

    public function clear()
    {
        $cartId = $this->cartModel->getOrCreateCartId(session_id());
        $this->cartModel->clearCart($cartId);

        $_SESSION['flash_success'] = 'Đã xóa toàn bộ giỏ hàng.';
        header('Location: ' . BASE_URL . '/Cart/index');
        exit();
    }

    public function checkout()
    {
        $cartId = $this->cartModel->getOrCreateCartId(session_id());
        $items = $this->cartModel->getCartItems($cartId);

        if (empty($items)) {
            $_SESSION['flash_error'] = 'Giỏ hàng đang trống.';
            header('Location: ' . BASE_URL . '/Cart/index');
            exit();
        }

        $pageTitle = 'Thanh toán';

        include 'app/views/cart/checkout.php';
    }

    public function processCheckout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/Cart/checkout');
            exit();
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $delivery_time_raw = trim($_POST['delivery_time'] ?? '');
        $delivery_time = null;
        if ($delivery_time_raw !== '') {
            $delivery_time_normalized = str_replace('T', ' ', $delivery_time_raw);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $delivery_time_normalized)) {
                $delivery_time_normalized .= ':00';
            }
            $timestamp = strtotime($delivery_time_normalized);
            if ($timestamp !== false) {
                $delivery_time = date('Y-m-d H:i:s', $timestamp);
            }
        }
        $voucher_code = strtoupper(trim($_POST['voucher_code'] ?? ''));
        // Chỉ Admin được chỉnh phí vận chuyển. User thường dù sửa HTML/POST cũng không thay đổi được.
        if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $shipping_fee = max(0, (float)($_POST['shipping_fee'] ?? 30000));
        } else {
            $shipping_fee = 30000;
        }
        $vat_invoice = !empty($_POST['vat_invoice']) ? 1 : 0;
        $vat_company = trim($_POST['vat_company'] ?? '');
        $vat_tax_code = trim($_POST['vat_tax_code'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? 'COD');

        $wallet_phone = trim($_POST['wallet_phone'] ?? '');
        $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $card_name = trim($_POST['card_name'] ?? '');
        $card_expiry = trim($_POST['card_expiry'] ?? '');
        $card_cvv = trim($_POST['card_cvv'] ?? '');

        $allowedMethods = array('COD','BANK_TRANSFER','MOMO','VNPAY','ZALOPAY','CARD','INSTALLMENT');
        if (!in_array($payment_method, $allowedMethods)) $payment_method = 'COD';

        if ($name === '' || $phone === '' || $address === '') {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin đặt hàng.';
            header('Location: ' . BASE_URL . '/Cart/checkout'); exit();
        }

        $payment_detail = 'Thanh toán khi nhận hàng';
        if ($payment_method === 'MOMO' || $payment_method === 'ZALOPAY') {
            if ($wallet_phone === '') { $_SESSION['flash_error'] = 'Vui lòng nhập số điện thoại ví điện tử.'; header('Location: ' . BASE_URL . '/Cart/checkout'); exit(); }
            $payment_detail = 'Ví điện tử: ' . $wallet_phone;
        }
        if ($payment_method === 'VNPAY' || $payment_method === 'BANK_TRANSFER') $payment_detail = 'Thanh toán bằng QR/chuyển khoản';
        if ($payment_method === 'INSTALLMENT') $payment_detail = 'Đăng ký trả góp 0% - chờ duyệt';
        if ($payment_method === 'CARD') {
            if ($card_number === '' || $card_name === '' || $card_expiry === '' || $card_cvv === '') { $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin thẻ.'; header('Location: ' . BASE_URL . '/Cart/checkout'); exit(); }
            if (strlen($card_number) < 12 || strlen($card_number) > 19) { $_SESSION['flash_error'] = 'Số thẻ không hợp lệ.'; header('Location: ' . BASE_URL . '/Cart/checkout'); exit(); }
            $payment_detail = 'Thẻ **** ' . substr($card_number, -4) . ' - ' . strtoupper($card_name);
        }

        $cartId = $this->cartModel->getOrCreateCartId(session_id());
        $items = $this->cartModel->getCartItems($cartId);
        if (empty($items)) { $_SESSION['flash_error'] = 'Giỏ hàng trống.'; header('Location: ' . BASE_URL . '/Cart/index'); exit(); }

        $total_amount = 0;
        foreach ($items as $item) $total_amount += (float)$item->price * (int)$item->quantity;

        $voucher = null; $discount_amount = 0;
        if ($voucher_code !== '') {
            $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE code=:code AND is_active=1 LIMIT 1");
            $stmt->execute(array(':code'=>$voucher_code));
            $voucher = $stmt->fetch(PDO::FETCH_OBJ);
            if ($voucher && $total_amount >= (float)$voucher->min_order_value) {
                $discount_amount = $voucher->type === 'percent' ? ($total_amount * (float)$voucher->value / 100) : (float)$voucher->value;
                $discount_amount = min($discount_amount, $total_amount);
            }
        }
        $account_id = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        // Ưu đãi theo hạng tiêu dùng + level tu tiên.
        // Tính sau voucher để user càng lên cấp càng được giảm nhiều hơn.
        $privilege = $this->getUserPrivilege($account_id, $total_amount);
        $privilege_discount = round(max(0, $total_amount - $discount_amount) * (float)$privilege['total_discount_rate']);
        $shipping_discount = round($shipping_fee * (float)$privilege['shipping_discount_rate']);
        $shipping_fee = max(0, $shipping_fee - $shipping_discount);
        $total_discount_amount = min($total_amount, $discount_amount + $privilege_discount);
        $final_amount = max(0, $total_amount - $total_discount_amount + $shipping_fee);

        $privilegeNote = $this->formatPrivilegeNote($privilege, $privilege_discount, $shipping_discount);
        if ($privilegeNote !== '') {
            $note = trim($note . "\n\n" . $privilegeNote);
        }

        $order_code = 'DH' . date('YmdHis') . rand(100,999);

        $this->db->beginTransaction();
        try {
            $rawOrderPaymentStatus = ($payment_method === 'COD' || $payment_method === 'INSTALLMENT') ? 'unpaid' : 'pending';
            $payment_status = $this->pickEnumValue('orders', 'payment_status', $rawOrderPaymentStatus, array('pending', 'unpaid', 'paid', 'success'));
            $methodForDb = strtolower($payment_method);
            if ($methodForDb === 'bank_transfer') {
                $methodForDb = 'bank_transfer';
            }

            // Database các bản trước đang lệch tên cột: có bản dùng name, có bản dùng customer_name.
            // Ghi theo cột nào đang tồn tại để tránh lỗi Unknown column / doesn't have default value.
            $orderData = array(
                'account_id' => $account_id,
                'order_code' => $order_code,
                'name' => $name,
                'customer_name' => $name,
                'phone' => $phone,
                'email' => null,
                'address' => $address,
                'note' => $note,
                'delivery_time' => $delivery_time,
                'payment_method' => $methodForDb,
                'payment_detail' => $payment_detail,
                'payment_status' => $payment_status,
                'order_status' => 'pending',
                'voucher_id' => $voucher ? $voucher->id : null,
                'discount_amount' => $total_discount_amount,
                'shipping_fee' => $shipping_fee,
                'subtotal' => $total_amount,
                'total_amount' => $total_amount,
                'final_amount' => $final_amount,
                'vat_invoice' => $vat_invoice,
                'vat_company' => $vat_company,
                'vat_tax_code' => $vat_tax_code
            );

            $orderId = $this->insertByExistingColumns('orders', $orderData);

            foreach ($items as $item) {
                $lineTotal = (float)$item->price * (int)$item->quantity;
                $this->insertByExistingColumns('order_details', array(
                    'order_id' => $orderId,
                    'product_id' => (int)$item->product_id,
                    'product_name' => $item->name,
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->price,
                    'total' => $lineTotal
                ));

                if ($this->columnExists('product', 'sold_count') && $this->columnExists('product', 'stock_quantity')) {
                    $this->db->prepare('UPDATE product SET sold_count = sold_count + :qty, stock_quantity = GREATEST(stock_quantity - :qty, 0) WHERE id=:pid')->execute(array(':qty'=>(int)$item->quantity, ':pid'=>(int)$item->product_id));
                } elseif ($this->columnExists('product', 'stock_quantity')) {
                    $this->db->prepare('UPDATE product SET stock_quantity = GREATEST(stock_quantity - :qty, 0) WHERE id=:pid')->execute(array(':qty'=>(int)$item->quantity, ':pid'=>(int)$item->product_id));
                }
            }

            $this->insertByExistingColumns('order_status_logs', array(
                'order_id' => $orderId,
                'status' => 'pending',
                'old_status' => null,
                'new_status' => 'pending',
                'note' => 'Khách vừa đặt hàng'
            ));

            // Bảng orders.payment_status có thể dùng unpaid/paid/failed/refunded.
            // Nhưng bảng payments.status thường dùng pending/success/failed/refunded.
            // Vì vậy không được ghi 'unpaid' vào payments.status, nếu không MySQL sẽ báo Data truncated.
            $paymentRowStatus = $payment_status;
            if ($paymentRowStatus === 'unpaid') {
                $paymentRowStatus = 'pending';
            }
            if ($paymentRowStatus === 'paid') {
                $paymentRowStatus = 'success';
            }
            $paymentRowStatus = $this->pickEnumValue('payments', 'status', $paymentRowStatus, array('pending', 'success', 'unpaid', 'paid', 'failed', 'refunded'));

            $this->insertByExistingColumns('payments', array(
                'order_id' => $orderId,
                'method' => $methodForDb,
                'payment_method' => $methodForDb,
                'amount' => $final_amount,
                'status' => $paymentRowStatus
            ));
            if ($voucher) {
                $this->db->prepare('UPDATE vouchers SET used_count=used_count+1 WHERE id=:id')->execute(array(':id'=>$voucher->id));
                $this->db->prepare('INSERT INTO voucher_usage (voucher_id, account_id, order_id) VALUES (:v,:u,:o)')->execute(array(':v'=>$voucher->id, ':u'=>$account_id, ':o'=>$orderId));
            }
            if ($account_id) {
                $this->db->prepare('INSERT INTO notifications (account_id, title, message, type) VALUES (:u,:t,:m,:type)')->execute(array(':u'=>$account_id, ':t'=>'Đặt hàng thành công', ':m'=>'Đơn hàng ' . $order_code . ' đã được tạo.', ':type'=>'order'));

                // Hệ thống hạng tiêu dùng + level tu tiên.
                // Mua hàng sẽ tăng tổng chi tiêu để xét hạng Bạc/Vàng/Kim cương.
                // Đồng thời cộng linh khí để nâng cấp cảnh giới.
                MembershipHelper::addSpending($this->db, $account_id, $final_amount);
                $energyGain = 50;
                foreach ($items as $item) {
                    $energyGain += ((int)$item->quantity * 25);
                }
                $energyGain += min(300, (int)floor($final_amount / 100000));
                $progress = MembershipHelper::addEnergy($this->db, $account_id, $energyGain, 'Mua hàng: ' . $order_code, 'order', $orderId);
                if ($progress) {
                    $_SESSION['cultivation_level'] = $progress['level'];
                    $_SESSION['cultivation_notice'] = '+' . $energyGain . ' linh khí khi mua hàng. Cảnh giới hiện tại: ' . $progress['realm'];
                }
            }
            $this->cartModel->clearCart($cartId);
            $this->db->commit();
            $_SESSION['last_order_id'] = $orderId;
            $_SESSION['flash_success'] = 'Đặt hàng thành công. Mã đơn: ' . $order_code;
            header('Location: ' . BASE_URL . '/Cart/orderConfirmation'); exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            echo 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage();
        }
    }
    public function orderConfirmation()
    {
        $pageTitle = 'Xác nhận đơn hàng';

        include 'app/views/cart/orderConfirmation.php';
    }
}
?>
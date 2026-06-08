<?php
require_once 'app/controllers/api/ApiBaseController.php';

class OrderApiController extends ApiBaseController
{
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

        $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return (int)$this->db->lastInsertId();
    }

    private function getCartItems($userId)
    {
        $stmt = $this->db->prepare('SELECT c.id AS cart_id, ci.product_id, ci.quantity, ci.price, p.name
                                    FROM cart c
                                    JOIN cart_item ci ON ci.cart_id = c.id
                                    JOIN product p ON p.id = ci.product_id
                                    WHERE c.account_id = :account_id');
        $stmt->execute(array(':account_id' => (int)$userId));
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function index()
    {
        $user = ApiAuth::requireUser();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->list($user);
        } elseif ($method === 'POST') {
            $this->create($user);
        } else {
            ApiResponse::error('Method Not Allowed', 405);
        }
    }

    private function list($user)
    {
        if ($user->role === 'admin') {
            $stmt = $this->db->query('SELECT * FROM orders ORDER BY id DESC LIMIT 100');
        } else {
            $stmt = $this->db->prepare('SELECT * FROM orders WHERE account_id = :account_id ORDER BY id DESC');
            $stmt->execute(array(':account_id' => (int)$user->id));
        }

        ApiResponse::success($stmt->fetchAll(PDO::FETCH_OBJ), 'Lấy danh sách đơn hàng thành công');
    }

    public function detail($id)
    {
        $user = ApiAuth::requireUser();

        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $order = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$order) {
            ApiResponse::error('Không tìm thấy đơn hàng', 404);
        }

        if ($user->role !== 'admin' && (int)$order->account_id !== (int)$user->id) {
            ApiResponse::error('Forbidden: không được xem đơn hàng của người khác', 403);
        }

        $stmt = $this->db->prepare('SELECT * FROM order_details WHERE order_id = :id');
        $stmt->execute(array(':id' => (int)$id));
        $details = $stmt->fetchAll(PDO::FETCH_OBJ);

        ApiResponse::success(array('order' => $order, 'details' => $details), 'Lấy chi tiết đơn hàng thành công');
    }

    private function create($user)
    {
        $items = $this->getCartItems($user->id);
        if (empty($items)) {
            ApiResponse::error('Không thể đặt hàng vì giỏ hàng rỗng', 422);
        }

        $data = $this->input();
        $name = trim($data['name'] ?? ($user->full_name ?: $user->username));
        $phone = trim($data['phone'] ?? ($user->phone ?? ''));
        $address = trim($data['address'] ?? ($user->address ?? ''));
        $paymentMethod = strtolower(trim($data['payment_method'] ?? 'cod'));

        if ($name === '' || $phone === '' || $address === '') {
            ApiResponse::error('Vui lòng nhập tên, số điện thoại và địa chỉ giao hàng', 422);
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)$item->price * (int)$item->quantity;
        }

        $shippingFee = (float)($data['shipping_fee'] ?? 30000);
        $finalAmount = $subtotal + $shippingFee;
        $orderCode = 'API' . date('YmdHis') . mt_rand(100, 999);

        try {
            $this->db->beginTransaction();

            $orderId = $this->insertByExistingColumns('orders', array(
                'account_id' => (int)$user->id,
                'order_code' => $orderCode,
                'name' => $name,
                'customer_name' => $name,
                'phone' => $phone,
                'email' => $user->email ?? null,
                'address' => $address,
                'note' => trim($data['note'] ?? ''),
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_fee' => $shippingFee,
                'total_amount' => $subtotal,
                'final_amount' => $finalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_detail' => $paymentMethod === 'cod' ? 'Thanh toán khi nhận hàng' : 'Thanh toán mô phỏng qua API'
            ));

            foreach ($items as $item) {
                $this->insertByExistingColumns('order_details', array(
                    'order_id' => $orderId,
                    'product_id' => (int)$item->product_id,
                    'product_name' => $item->name,
                    'price' => (float)$item->price,
                    'quantity' => (int)$item->quantity,
                    'total' => (float)$item->price * (int)$item->quantity
                ));
            }

            $this->insertByExistingColumns('order_status_logs', array(
                'order_id' => $orderId,
                'status' => 'pending',
                'old_status' => null,
                'new_status' => 'pending',
                'note' => 'Đơn hàng tạo qua API',
                'created_by' => (int)$user->id
            ));

            $stmt = $this->db->prepare('DELETE ci FROM cart_item ci JOIN cart c ON c.id = ci.cart_id WHERE c.account_id = :account_id');
            $stmt->execute(array(':account_id' => (int)$user->id));

            $this->db->commit();
            ApiResponse::success(array('order_id' => $orderId, 'order_code' => $orderCode), 'Tạo đơn hàng từ giỏ hàng thành công', 201);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            ApiResponse::error('Tạo đơn hàng thất bại: ' . $e->getMessage(), 500);
        }
    }

    public function cancel($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();

        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $order = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$order) {
            ApiResponse::error('Không tìm thấy đơn hàng', 404);
        }

        if ($user->role !== 'admin' && (int)$order->account_id !== (int)$user->id) {
            ApiResponse::error('Forbidden: không được hủy đơn hàng của người khác', 403);
        }

        if (!in_array($order->order_status, array('pending', 'confirmed'), true)) {
            ApiResponse::error('Chỉ được hủy đơn ở trạng thái pending/confirmed', 409);
        }

        $stmt = $this->db->prepare("UPDATE orders SET order_status='cancelled' WHERE id=:id");
        $stmt->execute(array(':id' => (int)$id));

        ApiResponse::success(null, 'Hủy đơn hàng thành công');
    }

    public function status($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $admin = ApiAuth::requireAdmin();
        $data = $this->input();
        $status = trim($data['order_status'] ?? '');

        $allowed = array('pending','confirmed','processing','shipping','completed','cancelled','returned');
        if (!in_array($status, $allowed, true)) {
            ApiResponse::error('Trạng thái đơn hàng không hợp lệ', 422);
        }

        $stmt = $this->db->prepare('SELECT order_status FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $oldStatus = $stmt->fetchColumn();

        if (!$oldStatus) {
            ApiResponse::error('Không tìm thấy đơn hàng', 404);
        }

        $stmt = $this->db->prepare('UPDATE orders SET order_status=:status WHERE id=:id');
        $stmt->execute(array(':status' => $status, ':id' => (int)$id));

        $this->insertByExistingColumns('order_status_logs', array(
            'order_id' => (int)$id,
            'status' => $status,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'note' => trim($data['note'] ?? 'Admin cập nhật qua API'),
            'created_by' => (int)$admin->id
        ));

        ApiResponse::success(null, 'Admin cập nhật trạng thái đơn hàng thành công');
    }
}
?>

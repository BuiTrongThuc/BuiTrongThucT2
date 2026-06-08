<?php
require_once 'app/controllers/api/ApiBaseController.php';

class PaymentApiController extends ApiBaseController
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

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $data = $this->input();
        $orderId = (int)($data['order_id'] ?? 0);
        $method = strtolower(trim($data['payment_method'] ?? 'cod'));

        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id=:id LIMIT 1');
        $stmt->execute(array(':id' => $orderId));
        $order = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$order) {
            ApiResponse::error('Không tìm thấy đơn hàng', 404);
        }

        if ($user->role !== 'admin' && (int)$order->account_id !== (int)$user->id) {
            ApiResponse::error('Forbidden: không được thanh toán đơn hàng của người khác', 403);
        }

        if ($order->payment_status === 'paid') {
            ApiResponse::error('Không cho thanh toán lại đơn hàng đã thanh toán', 409);
        }

        $amount = isset($order->final_amount) ? (float)$order->final_amount : (float)$order->total_amount;
        $paymentStatus = ($method === 'cod') ? 'pending' : 'success';
        $orderPaymentStatus = ($method === 'cod') ? 'unpaid' : 'paid';

        $paymentId = $this->insertByExistingColumns('payments', array(
            'order_id' => $orderId,
            'method' => $method,
            'payment_method' => $method,
            'amount' => $amount,
            'transaction_code' => $method === 'cod' ? null : ('TXN' . time() . mt_rand(100, 999)),
            'status' => $paymentStatus,
            'paid_at' => $method === 'cod' ? null : date('Y-m-d H:i:s')
        ));

        $stmt = $this->db->prepare('UPDATE orders SET payment_method=:method, payment_status=:status WHERE id=:id');
        $stmt->execute(array(':method' => $method, ':status' => $orderPaymentStatus, ':id' => $orderId));

        ApiResponse::success(array('payment_id' => $paymentId, 'payment_status' => $paymentStatus), 'Tạo thanh toán cho đơn hàng thành công');
    }

    public function update($orderId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        ApiAuth::requireAdmin();
        $data = $this->input();
        $status = trim($data['payment_status'] ?? 'paid');

        $allowed = array('unpaid', 'paid', 'failed', 'refunded');
        if (!in_array($status, $allowed, true)) {
            ApiResponse::error('Trạng thái thanh toán không hợp lệ', 422);
        }

        $stmt = $this->db->prepare('UPDATE orders SET payment_status=:status WHERE id=:id');
        $stmt->execute(array(':status' => $status, ':id' => (int)$orderId));

        ApiResponse::success(null, 'Cập nhật trạng thái thanh toán thành công');
    }
}
?>

<?php
require_once 'app/controllers/api/ApiBaseController.php';

class CartApiController extends ApiBaseController
{
    private function getCartId($userId)
    {
        $stmt = $this->db->prepare('SELECT id FROM cart WHERE account_id = :account_id LIMIT 1');
        $stmt->execute(array(':account_id' => (int)$userId));
        $cart = $stmt->fetch(PDO::FETCH_OBJ);

        if ($cart) return (int)$cart->id;

        $stmt = $this->db->prepare('INSERT INTO cart (account_id, session_id) VALUES (:account_id, :session_id)');
        $stmt->execute(array(':account_id' => (int)$userId, ':session_id' => 'api_' . $userId));
        return (int)$this->db->lastInsertId();
    }

    private function items($cartId)
    {
        $stmt = $this->db->prepare('SELECT ci.id, ci.product_id, ci.quantity, ci.price, (ci.quantity * ci.price) AS total, p.name, p.image
                                    FROM cart_item ci
                                    JOIN product p ON p.id = ci.product_id
                                    WHERE ci.cart_id = :cart_id
                                    ORDER BY ci.id DESC');
        $stmt->execute(array(':cart_id' => (int)$cartId));
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function index()
    {
        $user = ApiAuth::requireUser();
        $cartId = $this->getCartId($user->id);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $items = $this->items($cartId);
            ApiResponse::success(array('items' => $items, 'total' => $this->calculateTotal($items)), 'Lấy giỏ hàng thành công');
        } elseif ($method === 'POST') {
            $this->add($cartId);
        } elseif ($method === 'DELETE') {
            $this->clear($cartId);
        } else {
            ApiResponse::error('Method Not Allowed', 405);
        }
    }

    private function add($cartId)
    {
        $data = $this->input();
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 1);

        if ($quantity <= 0) {
            ApiResponse::error('Số lượng sản phẩm phải lớn hơn 0', 422);
        }

        $stmt = $this->db->prepare('SELECT id, price FROM product WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => $productId));
        $product = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$product) {
            ApiResponse::error('Sản phẩm không tồn tại', 404);
        }

        $stmt = $this->db->prepare('SELECT id FROM cart_item WHERE cart_id=:cart_id AND product_id=:product_id LIMIT 1');
        $stmt->execute(array(':cart_id' => $cartId, ':product_id' => $productId));
        $exists = $stmt->fetch(PDO::FETCH_OBJ);

        if ($exists) {
            $stmt = $this->db->prepare('UPDATE cart_item SET quantity = quantity + :qty WHERE id = :id');
            $stmt->execute(array(':qty' => $quantity, ':id' => (int)$exists->id));
        } else {
            $stmt = $this->db->prepare('INSERT INTO cart_item (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, :price)');
            $stmt->execute(array(':cart_id' => $cartId, ':product_id' => $productId, ':quantity' => $quantity, ':price' => (float)$product->price));
        }

        ApiResponse::success(null, 'Thêm sản phẩm vào giỏ hàng thành công', 201);
    }

    public function update($productId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $cartId = $this->getCartId($user->id);
        $data = $this->input();
        $quantity = (int)($data['quantity'] ?? 0);

        if ($quantity <= 0) {
            ApiResponse::error('Số lượng sản phẩm phải lớn hơn 0', 422);
        }

        $stmt = $this->db->prepare('UPDATE cart_item SET quantity=:quantity WHERE cart_id=:cart_id AND product_id=:product_id');
        $stmt->execute(array(':quantity' => $quantity, ':cart_id' => $cartId, ':product_id' => (int)$productId));

        ApiResponse::success(null, 'Cập nhật số lượng giỏ hàng thành công');
    }

    public function delete($productId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $cartId = $this->getCartId($user->id);

        $stmt = $this->db->prepare('DELETE FROM cart_item WHERE cart_id=:cart_id AND product_id=:product_id');
        $stmt->execute(array(':cart_id' => $cartId, ':product_id' => (int)$productId));

        ApiResponse::success(null, 'Xóa sản phẩm khỏi giỏ hàng thành công');
    }

    public function clear($cartId = null)
    {
        $user = ApiAuth::requireUser();
        $cartId = $cartId ?: $this->getCartId($user->id);

        $stmt = $this->db->prepare('DELETE FROM cart_item WHERE cart_id=:cart_id');
        $stmt->execute(array(':cart_id' => $cartId));

        ApiResponse::success(null, 'Xóa toàn bộ giỏ hàng thành công');
    }

    public function total()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $cartId = $this->getCartId($user->id);
        $items = $this->items($cartId);

        ApiResponse::success(array('total' => $this->calculateTotal($items)), 'Tính tổng tiền giỏ hàng thành công');
    }

    private function calculateTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item->price * (int)$item->quantity;
        }
        return $total;
    }
}
?>

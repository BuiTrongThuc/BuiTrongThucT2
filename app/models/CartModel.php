<?php
class CartModel
{
    private $conn;

    public function __construct($db) { $this->conn = $db; }

    public function getOrCreateCartId($sessionId)
    {
        $stmt = $this->conn->prepare('SELECT id FROM cart WHERE session_id = :session_id');
        $stmt->bindValue(':session_id', $sessionId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if ($row) return (int)$row->id;

        $stmt = $this->conn->prepare('INSERT INTO cart (session_id) VALUES (:session_id)');
        $stmt->bindValue(':session_id', $sessionId);
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function getCartItems($cartId)
    {
        $stmt = $this->conn->prepare('SELECT ci.id, ci.product_id, ci.quantity, ci.price, p.name, p.image
                                      FROM cart_item ci
                                      JOIN product p ON p.id = ci.product_id
                                      WHERE ci.cart_id = :cart_id
                                      ORDER BY ci.id DESC');
        $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function addItem($cartId, $productId, $price, $quantity = 1)
    {
        $stmt = $this->conn->prepare('SELECT id, quantity FROM cart_item WHERE cart_id = :cart_id AND product_id = :product_id');
        $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_OBJ);

        if ($existing) {
            $stmt = $this->conn->prepare('UPDATE cart_item SET quantity = quantity + :qty WHERE id = :id');
            $stmt->bindValue(':qty', $quantity, PDO::PARAM_INT);
            $stmt->bindValue(':id', $existing->id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        $stmt = $this->conn->prepare('INSERT INTO cart_item (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, :price)');
        $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price);
        return $stmt->execute();
    }

    public function updateItemQuantity($itemId, $quantity)
    {
        $stmt = $this->conn->prepare('UPDATE cart_item SET quantity = :quantity WHERE id = :id');
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function removeItem($itemId)
    {
        $stmt = $this->conn->prepare('DELETE FROM cart_item WHERE id = :id');
        $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function clearCart($cartId)
    {
        $stmt = $this->conn->prepare('DELETE FROM cart_item WHERE cart_id = :cart_id');
        $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
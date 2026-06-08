<?php
class ProductModel
{
    private $conn;
    private $table_name = 'product';

    public function __construct($db) { $this->conn = $db; }

    public function getProducts($search = '', $categoryId = null)
    {
        $query = 'SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name AS category_name
                  FROM ' . $this->table_name . ' p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE 1 = 1';

        if ($search !== '') {
            $query .= ' AND (p.name LIKE :search OR p.description LIKE :search)';
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query .= ' AND p.category_id = :category_id';
        }

        $query .= ' ORDER BY p.id DESC';

        $stmt = $this->conn->prepare($query);

        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }

        if ($categoryId !== null && $categoryId !== '') {
            $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM ' . $this->table_name . ' WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $errors = $this->validate($name, $price);
        if (!empty($errors)) return $errors;

        $query = 'INSERT INTO ' . $this->table_name . ' (name, description, price, category_id, image)
                  VALUES (:name, :description, :price, :category_id, :image)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($name)));
        $stmt->bindValue(':description', htmlspecialchars(strip_tags($description)));
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id ?: null, $category_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':image', $image ?: null);
        return $stmt->execute();
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image = null)
    {
        $errors = $this->validate($name, $price);
        if (!empty($errors)) return $errors;

        if ($image !== null && $image !== '') {
            $query = 'UPDATE ' . $this->table_name . ' SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id';
        } else {
            $query = 'UPDATE ' . $this->table_name . ' SET name=:name, description=:description, price=:price, category_id=:category_id WHERE id=:id';
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($name)));
        $stmt->bindValue(':description', htmlspecialchars(strip_tags($description)));
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id ?: null, $category_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        if ($image !== null && $image !== '') $stmt->bindValue(':image', $image);
        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM ' . $this->table_name . ' WHERE id=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function validate($name, $price)
    {
        $errors = array();
        if (trim($name) === '') $errors['name'] = 'Tên sản phẩm không được để trống';
        elseif (strlen(trim($name)) < 10 || strlen(trim($name)) > 100) $errors['name'] = 'Tên sản phẩm phải từ 10 đến 100 ký tự';
        if ($price === '' || !is_numeric($price) || (float)$price <= 0) $errors['price'] = 'Giá sản phẩm phải lớn hơn 0';
        return $errors;
    }
}
?>

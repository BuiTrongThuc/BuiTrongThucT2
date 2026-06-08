<?php
class CategoryModel
{
    private $conn;
    private $table_name = 'category';

    public function __construct($db) { $this->conn = $db; }

    public function getCategories()
    {
        $stmt = $this->conn->prepare('SELECT id, name, description FROM ' . $this->table_name . ' ORDER BY id ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare('SELECT id, name, description FROM ' . $this->table_name . ' WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addCategory($name, $description)
    {
        $errors = $this->validate($name);
        if (!empty($errors)) return $errors;

        $stmt = $this->conn->prepare('INSERT INTO ' . $this->table_name . ' (name, description) VALUES (:name, :description)');
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($name)));
        $stmt->bindValue(':description', htmlspecialchars(strip_tags($description)));
        return $stmt->execute();
    }

    public function updateCategory($id, $name, $description)
    {
        $errors = $this->validate($name, $id);
        if (!empty($errors)) return $errors;

        $stmt = $this->conn->prepare('UPDATE ' . $this->table_name . ' SET name=:name, description=:description WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($name)));
        $stmt->bindValue(':description', htmlspecialchars(strip_tags($description)));
        return $stmt->execute();
    }

    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM ' . $this->table_name . ' WHERE id=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function validate($name, $ignoreId = null)
    {
        $errors = array();
        $name = trim($name);
        if ($name === '') {
            $errors['name'] = 'Tên danh mục không được để trống';
        } elseif (strlen($name) < 3 || strlen($name) > 100) {
            $errors['name'] = 'Tên danh mục phải từ 3 đến 100 ký tự';
        }

        $stmt = $this->conn->prepare('SELECT id FROM ' . $this->table_name . ' WHERE name = :name LIMIT 1');
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        $exists = $stmt->fetch(PDO::FETCH_OBJ);
        if ($exists && ($ignoreId === null || (int)$exists->id !== (int)$ignoreId)) {
            $errors['name'] = 'Tên danh mục đã tồn tại';
        }

        return $errors;
    }
}
?>

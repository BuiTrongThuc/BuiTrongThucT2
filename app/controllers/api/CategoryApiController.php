<?php
require_once 'app/controllers/api/ApiBaseController.php';

class CategoryApiController extends ApiBaseController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $stmt = $this->db->query('SELECT * FROM category ORDER BY id ASC');
            ApiResponse::success($stmt->fetchAll(PDO::FETCH_OBJ), 'Lấy danh sách danh mục thành công');
        } elseif ($method === 'POST') {
            $this->create();
        } elseif ($method === 'OPTIONS') {
            ApiResponse::success(null, 'OK');
        } else {
            ApiResponse::error('Method Not Allowed', 405);
        }
    }

    public function detail($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $stmt = $this->db->prepare('SELECT * FROM category WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $category = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$category) {
            ApiResponse::error('Không tìm thấy danh mục', 404);
        }

        ApiResponse::success($category, 'Lấy chi tiết danh mục thành công');
    }

    private function validateCategory($data)
    {
        $errors = array();
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            $errors['name'] = 'Tên danh mục không được rỗng';
        }

        return $errors;
    }

    private function create()
    {
        ApiAuth::requireAdmin();
        $data = $this->input();
        $errors = $this->validateCategory($data);

        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu danh mục không hợp lệ', 422, $errors);
        }

        $stmt = $this->db->prepare('INSERT INTO category (name, description) VALUES (:name, :description)');
        $stmt->execute(array(
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? '')
        ));

        ApiResponse::success(array('id' => (int)$this->db->lastInsertId()), 'Thêm danh mục thành công', 201);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        ApiAuth::requireAdmin();
        $data = $this->input();
        $errors = $this->validateCategory($data);

        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu danh mục không hợp lệ', 422, $errors);
        }

        $stmt = $this->db->prepare('UPDATE category SET name=:name, description=:description WHERE id=:id');
        $stmt->execute(array(
            ':id' => (int)$id,
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? '')
        ));

        ApiResponse::success(array('id' => (int)$id), 'Cập nhật danh mục thành công');
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        ApiAuth::requireAdmin();

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM product WHERE category_id = :id');
        $stmt->execute(array(':id' => (int)$id));
        if ((int)$stmt->fetchColumn() > 0) {
            ApiResponse::error('Không thể xóa danh mục vì vẫn còn sản phẩm thuộc danh mục này', 409);
        }

        $stmt = $this->db->prepare('DELETE FROM category WHERE id = :id');
        $stmt->execute(array(':id' => (int)$id));

        ApiResponse::success(null, 'Xóa danh mục thành công');
    }
}
?>

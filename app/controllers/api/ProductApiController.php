<?php
require_once 'app/controllers/api/ApiBaseController.php';

class ProductApiController extends ApiBaseController
{
    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->list();
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

        $stmt = $this->db->prepare($this->safeProductSelect() . ' WHERE p.id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $product = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$product) {
            ApiResponse::error('Không tìm thấy sản phẩm', 404);
        }

        ApiResponse::success($product, 'Lấy chi tiết sản phẩm thành công');
    }

    private function list()
    {
        $where = array('1=1');
        $params = array();

        if (!empty($_GET['search'])) {
            $where[] = '(p.name LIKE :search OR p.description LIKE :search)';
            $params[':search'] = '%' . trim($_GET['search']) . '%';
        }

        if (!empty($_GET['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = (int)$_GET['category_id'];
        }

        if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = (float)$_GET['min_price'];
        }

        if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = (float)$_GET['max_price'];
        }

        $sort = $_GET['sort'] ?? 'newest';
        $orderBy = 'p.id DESC';
        if ($sort === 'price_asc') {
            $orderBy = 'p.price ASC';
        } elseif ($sort === 'price_desc') {
            $orderBy = 'p.price DESC';
        } elseif ($sort === 'name_asc') {
            $orderBy = 'p.name ASC';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 12)));
        $offset = ($page - 1) * $limit;

        $countSql = 'SELECT COUNT(*) FROM product p WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        $sql = $this->safeProductSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        ApiResponse::success(array(
            'items' => $stmt->fetchAll(PDO::FETCH_OBJ),
            'pagination' => array(
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int)ceil($total / $limit)
            )
        ), 'Lấy danh sách sản phẩm thành công');
    }

    private function validateProduct($data, $isUpdate = false)
    {
        $errors = array();

        $name = trim($data['name'] ?? '');
        $price = $data['price'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Tên sản phẩm không được rỗng';
        }

        if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
            $errors['price'] = 'Giá phải là số và lớn hơn 0';
        }

        if (!empty($data['category_id'])) {
            $stmt = $this->db->prepare('SELECT id FROM category WHERE id = :id LIMIT 1');
            $stmt->execute(array(':id' => (int)$data['category_id']));
            if (!$stmt->fetch()) {
                $errors['category_id'] = 'Danh mục sản phẩm không hợp lệ';
            }
        }

        if (!empty($data['image']) && !$this->validateImageName($data['image'])) {
            $errors['image'] = 'Hình ảnh phải có định dạng jpg, jpeg, png, gif hoặc webp';
        }

        if (!empty($_FILES['image_file']['name']) && !$this->validateImageName($_FILES['image_file']['name'])) {
            $errors['image_file'] = 'File upload phải là ảnh jpg, jpeg, png, gif hoặc webp';
        }

        return $errors;
    }

    private function uploadImageIfAny()
    {
        if (empty($_FILES['image_file']['name'])) {
            return null;
        }

        $dir = 'public/uploads/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $filename = 'api_product_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $target = $dir . $filename;

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
            ApiResponse::error('Upload hình ảnh thất bại', 400);
        }

        return $filename;
    }

    private function create()
    {
        ApiAuth::requireAdmin();
        $data = !empty($_POST) ? $_POST : $this->input();
        $errors = $this->validateProduct($data);

        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu sản phẩm không hợp lệ', 422, $errors);
        }

        $image = $this->uploadImageIfAny();
        if ($image === null) {
            $image = trim($data['image'] ?? '');
        }

        $stmt = $this->db->prepare('INSERT INTO product (name, description, price, category_id, image) VALUES (:name, :description, :price, :category_id, :image)');
        $stmt->execute(array(
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? ''),
            ':price' => (float)$data['price'],
            ':category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            ':image' => $image ?: null
        ));

        $id = (int)$this->db->lastInsertId();
        ApiResponse::success(array('id' => $id), 'Thêm sản phẩm thành công', 201);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        ApiAuth::requireAdmin();

        $stmt = $this->db->prepare('SELECT * FROM product WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $old = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$old) {
            ApiResponse::error('Không tìm thấy sản phẩm', 404);
        }

        $data = $this->input();
        $errors = $this->validateProduct($data, true);
        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu sản phẩm không hợp lệ', 422, $errors);
        }

        $image = trim($data['image'] ?? ($old->image ?? ''));

        $stmt = $this->db->prepare('UPDATE product SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id');
        $stmt->execute(array(
            ':id' => (int)$id,
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? ''),
            ':price' => (float)$data['price'],
            ':category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            ':image' => $image ?: null
        ));

        ApiResponse::success(array('id' => (int)$id), 'Cập nhật sản phẩm thành công');
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        ApiAuth::requireAdmin();

        $stmt = $this->db->prepare('DELETE FROM product WHERE id = :id');
        $stmt->execute(array(':id' => (int)$id));

        ApiResponse::success(null, 'Xóa sản phẩm thành công');
    }
}
?>

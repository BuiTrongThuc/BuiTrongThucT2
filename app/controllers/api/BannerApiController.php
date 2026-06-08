<?php
require_once 'app/controllers/api/ApiBaseController.php';

class BannerApiController extends ApiBaseController
{
    public function index($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            if ($id !== null && $id !== '') {
                $this->detail($id);
            } else {
                $this->list();
            }
        } elseif ($method === 'POST') {
            if ($id !== null && $id !== '') {
                $this->update($id);
            } else {
                $this->create();
            }
        } elseif ($method === 'PUT') {
            $this->update($id);
        } elseif ($method === 'DELETE') {
            $this->delete($id);
        } else {
            ApiResponse::error('Method Not Allowed', 405);
        }
    }

    private function ensureBannerTable()
    {
        if (!$this->tableExists('banners')) {
            $this->db->exec("CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(150) NOT NULL,
                subtitle VARCHAR(255) NULL,
                image VARCHAR(255) NULL,
                position VARCHAR(50) DEFAULT 'home_main',
                link VARCHAR(255) NULL,
                sort_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        if (!$this->columnExists('banners', 'subtitle')) {
            $this->db->exec("ALTER TABLE banners ADD COLUMN subtitle VARCHAR(255) NULL AFTER title");
        }
        if (!$this->columnExists('banners', 'sort_order')) {
            $this->db->exec("ALTER TABLE banners ADD COLUMN sort_order INT DEFAULT 0 AFTER link");
        }
    }

    private function list()
    {
        $this->ensureBannerTable();

        $position = trim($_GET['position'] ?? '');
        $adminMode = isset($_GET['admin']) && $_GET['admin'] == '1';

        $sql = 'SELECT * FROM banners WHERE 1=1';
        $params = array();

        if (!$adminMode) {
            $sql .= ' AND is_active = 1';
        }

        if ($position !== '') {
            $sql .= ' AND position = :position';
            $params[':position'] = $position;
        }

        $sql .= ' ORDER BY sort_order ASC, id ASC';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($position !== '') {
                ApiResponse::success($rows, 'Lấy banner theo vị trí thành công');
            }

            $main = array();
            $mini = array();
            foreach ($rows as $row) {
                if (($row->position ?? '') === 'home_main') {
                    $main[] = $row;
                } elseif (($row->position ?? '') === 'home_mini') {
                    $mini[] = $row;
                }
            }

            ApiResponse::success(array(
                'main' => $main,
                'mini' => $mini,
                'all' => $rows
            ), 'Lấy danh sách banner thành công');
        } catch (Exception $e) {
            ApiResponse::error('Lấy banner thất bại: ' . $e->getMessage(), 500);
        }
    }

    private function detail($id)
    {
        $this->ensureBannerTable();

        $stmt = $this->db->prepare('SELECT * FROM banners WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $banner = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$banner) {
            ApiResponse::error('Không tìm thấy banner', 404);
        }

        ApiResponse::success($banner, 'Lấy chi tiết banner thành công');
    }

    private function validateBanner($data)
    {
        $errors = array();
        $title = trim($data['title'] ?? '');

        if ($title === '') {
            $errors['title'] = 'Tiêu đề banner không được rỗng';
        }

        $position = trim($data['position'] ?? 'home_main');
        if (!in_array($position, array('home_main', 'home_mini'), true)) {
            $errors['position'] = 'Vị trí banner không hợp lệ';
        }

        if (!empty($data['image']) && !$this->validateImageName($data['image'])) {
            $errors['image'] = 'Ảnh banner phải là jpg, jpeg, png, gif hoặc webp';
        }

        if (!empty($_FILES['image_file']['name']) && !$this->validateImageName($_FILES['image_file']['name'])) {
            $errors['image_file'] = 'File upload phải là ảnh jpg, jpeg, png, gif hoặc webp';
        }

        return $errors;
    }

    private function uploadBannerImageIfAny()
    {
        if (empty($_FILES['image_file']['name'])) {
            return null;
        }

        $dir = 'public/uploads/banners/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $filename = 'banner_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $target = $dir . $filename;

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
            ApiResponse::error('Upload ảnh banner thất bại', 400);
        }

        return $filename;
    }

    private function create()
    {
        ApiAuth::requireAdmin();
        $this->ensureBannerTable();

        $data = $this->input();
        $errors = $this->validateBanner($data);
        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu banner không hợp lệ', 422, $errors);
        }

        $uploaded = $this->uploadBannerImageIfAny();
        $image = $uploaded !== null ? $uploaded : trim($data['image'] ?? '');

        $stmt = $this->db->prepare('INSERT INTO banners (title, subtitle, image, position, link, sort_order, is_active)
                                    VALUES (:title, :subtitle, :image, :position, :link, :sort_order, :is_active)');
        $stmt->execute(array(
            ':title' => trim($data['title']),
            ':subtitle' => trim($data['subtitle'] ?? ''),
            ':image' => $image ?: null,
            ':position' => trim($data['position'] ?? 'home_main'),
            ':link' => trim($data['link'] ?? '/Product/list'),
            ':sort_order' => (int)($data['sort_order'] ?? 0),
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ));

        ApiResponse::success(array('id' => (int)$this->db->lastInsertId()), 'Thêm banner thành công', 201);
    }

    private function update($id)
    {
        ApiAuth::requireAdmin();
        $this->ensureBannerTable();

        if (!$id) {
            ApiResponse::error('Thiếu ID banner cần cập nhật', 400);
        }

        $stmt = $this->db->prepare('SELECT * FROM banners WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$id));
        $old = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$old) {
            ApiResponse::error('Không tìm thấy banner', 404);
        }

        $data = $this->input();
        $errors = $this->validateBanner($data);
        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu banner không hợp lệ', 422, $errors);
        }

        $uploaded = $this->uploadBannerImageIfAny();
        $image = $uploaded !== null ? $uploaded : trim($data['image'] ?? ($old->image ?? ''));

        $stmt = $this->db->prepare('UPDATE banners
                                    SET title = :title,
                                        subtitle = :subtitle,
                                        image = :image,
                                        position = :position,
                                        link = :link,
                                        sort_order = :sort_order,
                                        is_active = :is_active
                                    WHERE id = :id');
        $stmt->execute(array(
            ':id' => (int)$id,
            ':title' => trim($data['title']),
            ':subtitle' => trim($data['subtitle'] ?? ''),
            ':image' => $image ?: null,
            ':position' => trim($data['position'] ?? 'home_main'),
            ':link' => trim($data['link'] ?? '/Product/list'),
            ':sort_order' => (int)($data['sort_order'] ?? 0),
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ));

        ApiResponse::success(array('id' => (int)$id), 'Cập nhật banner thành công');
    }

    private function delete($id)
    {
        ApiAuth::requireAdmin();
        $this->ensureBannerTable();

        if (!$id) {
            ApiResponse::error('Thiếu ID banner cần xóa', 400);
        }

        $stmt = $this->db->prepare('DELETE FROM banners WHERE id = :id');
        $stmt->execute(array(':id' => (int)$id));

        ApiResponse::success(null, 'Xóa banner thành công');
    }
}
?>

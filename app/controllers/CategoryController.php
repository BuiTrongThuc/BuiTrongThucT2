<?php
require_once 'config/database.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/helpers/SessionHelper.php';

class CategoryController
{
    private $db;
    private $categoryModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index() { $this->list(); }

    public function list()
    {
        SessionHelper::requireAdmin();

        $categories = $this->categoryModel->getCategories();
        $pageTitle = 'Quản lý danh mục';
        include 'app/views/category/list.php';
    }

    public function add()
    {
        SessionHelper::requireAdmin();

        $errors = array();
        $old = array('name' => '', 'description' => '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array(
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? '')
            );
            $result = $this->categoryModel->addCategory($old['name'], $old['description']);
            if ($result === true) {
                $_SESSION['flash_success'] = 'Thêm danh mục thành công.';
                header('Location: ' . BASE_URL . '/Category/list'); exit();
            }
            $errors = is_array($result) ? $result : array('Không thể thêm danh mục.');
        }
        $pageTitle = 'Thêm danh mục';
        include 'app/views/category/add.php';
    }

    public function edit($id)
    {
        SessionHelper::requireAdmin();

        $category = $this->categoryModel->getCategoryById((int)$id);
        if (!$category) die('Không thấy danh mục.');
        $errors = array();
        $old = array('name' => $category->name, 'description' => $category->description);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array(
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? '')
            );
            $result = $this->categoryModel->updateCategory((int)$id, $old['name'], $old['description']);
            if ($result === true) {
                $_SESSION['flash_success'] = 'Cập nhật danh mục thành công.';
                header('Location: ' . BASE_URL . '/Category/list'); exit();
            }
            $errors = is_array($result) ? $result : array('Không thể cập nhật danh mục.');
        }
        $pageTitle = 'Sửa danh mục';
        include 'app/views/category/edit.php';
    }

    public function delete($id)
    {
        SessionHelper::requireAdmin();

        $this->categoryModel->deleteCategory((int)$id);
        $_SESSION['flash_success'] = 'Xóa danh mục thành công.';
        header('Location: ' . BASE_URL . '/Category/list'); exit();
    }
}
?>
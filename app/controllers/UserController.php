<?php
require_once 'config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/helpers/MembershipHelper.php';

class UserController
{
    private $db;
    private $accountModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    public function profile()
    {
        SessionHelper::requireLogin();
        $user = $this->accountModel->getById((int)$_SESSION['user_id']);
        $tierDefinitions = MembershipHelper::getTierDefinitions();
        $cultivationProgress = MembershipHelper::getProgress($user);
        $pageTitle = 'Hồ sơ cá nhân';
        include 'app/views/user/profile.php';
    }

    public function updateProfile()
    {
        SessionHelper::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/User/profile');
            exit();
        }

        $id = (int)$_SESSION['user_id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email không hợp lệ.';
            header('Location: ' . BASE_URL . '/User/profile');
            exit();
        }

        $emailOwner = $email !== '' ? $this->accountModel->getByEmail($email) : null;
        if ($emailOwner && (int)$emailOwner->id !== $id) {
            $_SESSION['flash_error'] = 'Email này đã được tài khoản khác sử dụng.';
            header('Location: ' . BASE_URL . '/User/profile');
            exit();
        }

        $current = $this->accountModel->getById($id);
        $avatar = null;
        $errors = array();

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $avatar = $this->uploadAvatar($errors);
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '/User/profile');
            exit();
        }

        $this->accountModel->updateProfile($id, $fullName, $email, $phone, $address, $avatar);
        $_SESSION['full_name'] = $fullName;
        if ($avatar !== null) {
            $_SESSION['avatar'] = $avatar;
        } elseif ($current && !empty($current->avatar)) {
            $_SESSION['avatar'] = $current->avatar;
        }

        $_SESSION['flash_success'] = 'Cập nhật hồ sơ thành công.';
        header('Location: ' . BASE_URL . '/User/profile');
        exit();
    }

    public function changePassword()
    {
        SessionHelper::requireLogin();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $user = $this->accountModel->getById((int)$_SESSION['user_id']);

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $error = 'Vui lòng nhập đầy đủ thông tin.';
            } elseif (!$user || !$this->accountModel->verifyPassword($currentPassword, $user->password)) {
                $error = 'Mật khẩu hiện tại không đúng.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            } else {
                $this->accountModel->changePassword($user->id, $newPassword);
                $_SESSION['flash_success'] = 'Đổi mật khẩu thành công.';
                header('Location: ' . BASE_URL . '/User/profile');
                exit();
            }
        }

        $pageTitle = 'Đổi mật khẩu';
        include 'app/views/user/change_password.php';
    }

    public function admin()
    {
        SessionHelper::requireAdmin();
        $users = $this->accountModel->getAll();
        $pageTitle = 'Quản lý người dùng';
        include 'app/views/user/admin.php';
    }

    public function updateRole($id)
    {
        SessionHelper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/User/admin');
            exit();
        }

        $id = (int)$id;
        $role = $_POST['role'] ?? 'user';
        $role = $role === 'admin' ? 'admin' : 'user';

        if ($id === (int)$_SESSION['user_id'] && $role !== 'admin') {
            $_SESSION['flash_error'] = 'Không thể tự hạ quyền tài khoản đang đăng nhập.';
        } else {
            $this->accountModel->updateRole($id, $role);
            $_SESSION['flash_success'] = 'Đã cập nhật vai trò tài khoản.';
        }

        header('Location: ' . BASE_URL . '/User/admin');
        exit();
    }

    public function lock($id)
    {
        SessionHelper::requireAdmin();
        if ((int)$id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Admin không thể tự khóa tài khoản đang đăng nhập.';
        } else {
            $this->accountModel->toggleActive((int)$id, 0);
            $_SESSION['flash_success'] = 'Đã khóa tài khoản người dùng.';
        }
        header('Location: ' . BASE_URL . '/User/admin');
        exit();
    }

    public function unlock($id)
    {
        SessionHelper::requireAdmin();
        $this->accountModel->toggleActive((int)$id, 1);
        $_SESSION['flash_success'] = 'Đã mở khóa tài khoản người dùng.';
        header('Location: ' . BASE_URL . '/User/admin');
        exit();
    }

    public function makeAdmin($id)
    {
        SessionHelper::requireAdmin();
        $this->accountModel->updateRole((int)$id, 'admin');
        $_SESSION['flash_success'] = 'Đã cấp quyền Admin.';
        header('Location: ' . BASE_URL . '/User/admin');
        exit();
    }

    public function makeUser($id)
    {
        SessionHelper::requireAdmin();
        if ((int)$id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Không thể tự hạ quyền tài khoản đang đăng nhập.';
        } else {
            $this->accountModel->updateRole((int)$id, 'user');
            $_SESSION['flash_success'] = 'Đã chuyển tài khoản về quyền User.';
        }
        header('Location: ' . BASE_URL . '/User/admin');
        exit();
    }

    private function uploadAvatar(&$errors)
    {
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Tải ảnh đại diện thất bại.';
            return null;
        }

        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
            $errors[] = 'Ảnh đại diện chỉ chấp nhận JPG, PNG, GIF, WEBP.';
            return null;
        }

        if ((int)$_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ảnh đại diện không được vượt quá 2MB.';
            return null;
        }

        $dir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $name = time() . '_' . uniqid('avatar_', true) . '.' . $ext;
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $name)) {
            $errors[] = 'Không thể lưu ảnh đại diện.';
            return null;
        }

        return $name;
    }
}
?>

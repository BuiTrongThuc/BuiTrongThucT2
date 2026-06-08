<?php
require_once 'config/database.php';
require_once 'app/models/AccountModel.php';

class AuthController
{
    private $db;
    private $accountModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    private function loginAccount($account)
    {
        $_SESSION['user_id'] = $account->id;
        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;
        $_SESSION['full_name'] = $account->full_name ?? '';
        $_SESSION['avatar'] = $account->avatar ?? '';
        $_SESSION['member_tier'] = $account->member_tier ?? 'bac';
        $_SESSION['cultivation_level'] = $account->cultivation_level ?? 1;
    }

    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember = isset($_POST['remember_me']);

            $account = $this->accountModel->getByUsername($username);

            if ($account && (int)$account->is_active !== 1) {
                $error = 'Tài khoản đã bị khóa. Vui lòng liên hệ Admin.';
            } elseif ($account && $this->accountModel->verifyPassword($password, $account->password)) {
                if (strpos($account->password, '$2y$') !== 0 && strpos($account->password, '$argon2') !== 0) {
                    $this->accountModel->upgradePasswordHash($account->id, $password);
                }

                $this->loginAccount($account);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $this->accountModel->setRememberToken($account->id, $token);
                    setcookie('remember_login', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }

                $_SESSION['flash_success'] = 'Đăng nhập thành công. Xin chào ' . $account->username . '.';
                header('Location: ' . BASE_URL . '/Product/list');
                exit();
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        }

        $pageTitle = 'Đăng nhập';
        include 'app/views/auth/login.php';
    }

    public function register()
    {
        $error = '';
        $verifyLink = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($username === '' || $email === '' || $password === '' || $confirmPassword === '') {
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập, email và mật khẩu.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không hợp lệ.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
            } elseif ($this->accountModel->getByUsername($username)) {
                $error = 'Tên đăng nhập đã tồn tại.';
            } elseif ($this->accountModel->getByEmail($email)) {
                $error = 'Email đã được sử dụng.';
            } else {
                $token = $this->accountModel->create($username, $password, $email, $fullName);
                $verifyLink = BASE_URL . '/Auth/verifyEmail/' . $token;

                $_SESSION['flash_success'] = 'Đăng ký thành công. Hệ thống đã tạo link xác thực email demo bên dưới.';
            }
        }

        $pageTitle = 'Đăng ký';
        include 'app/views/auth/register.php';
    }

    public function verifyEmail($token = '')
    {
        $account = $this->accountModel->getByVerifyToken($token);
        if (!$account) {
            $_SESSION['flash_error'] = 'Link xác thực không hợp lệ hoặc tài khoản đã được xác thực.';
        } else {
            $this->accountModel->markEmailVerified($account->id);
            $_SESSION['flash_success'] = 'Xác thực email thành công. Bạn có thể đăng nhập.';
        }
        header('Location: ' . BASE_URL . '/Auth/login');
        exit();
    }

    public function forgotPassword()
    {
        $error = '';
        $resetLink = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $account = $this->accountModel->getByEmail($email);

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Vui lòng nhập email hợp lệ.';
            } elseif (!$account) {
                $error = 'Không tìm thấy tài khoản theo email này.';
            } elseif ((int)$account->is_active !== 1) {
                $error = 'Tài khoản đang bị khóa, không thể đặt lại mật khẩu.';
            } else {
                $token = $this->accountModel->setResetToken($account->id);
                $resetLink = BASE_URL . '/Auth/resetPassword/' . $token;
                $_SESSION['flash_success'] = 'Hệ thống đã tạo link đặt lại mật khẩu demo bên dưới.';
            }
        }

        $pageTitle = 'Quên mật khẩu';
        include 'app/views/auth/forgot_password.php';
    }

    public function resetPassword($token = '')
    {
        $error = '';
        $account = $this->accountModel->getByResetToken($token);

        if (!$account) {
            $_SESSION['flash_error'] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            header('Location: ' . BASE_URL . '/Auth/forgotPassword');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($password === '' || $confirmPassword === '') {
                $error = 'Vui lòng nhập đầy đủ mật khẩu mới.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
            } else {
                $this->accountModel->changePassword($account->id, $password);
                $this->accountModel->clearResetToken($account->id);
                $_SESSION['flash_success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.';
                header('Location: ' . BASE_URL . '/Auth/login');
                exit();
            }
        }

        $pageTitle = 'Đặt lại mật khẩu';
        include 'app/views/auth/reset_password.php';
    }

    public function logout()
    {
        if (!empty($_SESSION['user_id'])) {
            $this->accountModel->clearRememberToken((int)$_SESSION['user_id']);
        }

        setcookie('remember_login', '', time() - 3600, '/', '', false, true);
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['full_name'], $_SESSION['avatar']);

        $_SESSION['flash_success'] = 'Đã đăng xuất.';
        header('Location: ' . BASE_URL . '/Product/list');
        exit();
    }
}
?>

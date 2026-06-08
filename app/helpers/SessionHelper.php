<?php
class SessionHelper
{
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để sử dụng chức năng này.';
            header('Location: ' . BASE_URL . '/Auth/login');
            exit();
        }
    }

    public static function requireAdmin()
    {
        if (!self::isLoggedIn()) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập tài khoản Admin để vào trang quản trị.';
            header('Location: ' . BASE_URL . '/Auth/login');
            exit();
        }

        if (!self::isAdmin()) {
            $_SESSION['flash_error'] = 'Bạn không có quyền thực hiện chức năng quản trị này.';
            header('Location: ' . BASE_URL . '/Product/list');
            exit();
        }
    }
}
?>

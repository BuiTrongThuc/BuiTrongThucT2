<?php
require_once 'app/helpers/ApiResponse.php';
require_once 'app/helpers/JwtHelper.php';
require_once 'config/database.php';

class ApiAuth
{
    public static function getBearerToken()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : array();

        $authHeader = '';
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if ($authHeader === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if ($authHeader === '' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    public static function user()
    {
        $token = self::getBearerToken();
        if (!$token) {
            ApiResponse::error('Unauthorized: thiếu Authorization Bearer token', 401);
        }

        try {
            $payload = JwtHelper::decodeToken($token);
        } catch (Exception $e) {
            ApiResponse::error('Unauthorized: ' . $e->getMessage(), 401);
        }

        $db = (new Database())->getConnection();
        $stmt = $db->prepare('SELECT id, username, email, full_name, phone, address, role, is_active FROM account WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int)$payload['sub']));
        $account = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$account || (int)$account->is_active !== 1) {
            ApiResponse::error('Unauthorized: tài khoản không tồn tại hoặc đã bị khóa', 401);
        }

        return $account;
    }

    public static function requireUser()
    {
        return self::user();
    }

    public static function requireAdmin()
    {
        $user = self::user();
        if ($user->role !== 'admin') {
            ApiResponse::error('Forbidden: chỉ Admin được thực hiện chức năng này', 403);
        }
        return $user;
    }
}
?>

<?php
require_once 'app/controllers/api/ApiBaseController.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/JwtHelper.php';

class AuthApiController extends ApiBaseController
{
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $data = $this->input();
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $password = trim($data['password'] ?? '');

        $errors = array();
        if ($username === '') $errors['username'] = 'Tên đăng nhập không được rỗng';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
        if (strlen($password) < 6) $errors['password'] = 'Mật khẩu phải từ 6 ký tự';

        if (!empty($errors)) {
            ApiResponse::error('Dữ liệu đăng ký không hợp lệ', 422, $errors);
        }

        $model = new AccountModel($this->db);
        if ($model->getByUsername($username)) {
            ApiResponse::error('Tên đăng nhập đã tồn tại', 409);
        }
        if ($model->getByEmail($email)) {
            ApiResponse::error('Email đã tồn tại', 409);
        }

        $model->create($username, $password, $email, $fullName);
        ApiResponse::success(null, 'Đăng ký tài khoản thành công', 201);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $data = $this->input();
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        $model = new AccountModel($this->db);
        $account = $model->getByUsername($username);

        if (!$account || !$model->verifyPassword($password, $account->password)) {
            $this->increaseFailedLogin($username);
            ApiResponse::error('Tên đăng nhập hoặc mật khẩu không đúng', 401);
        }

        if ((int)$account->is_active !== 1) {
            ApiResponse::error('Tài khoản đã bị khóa', 403);
        }

        if (strpos($account->password, '$2y$') !== 0 && strpos($account->password, '$argon2') !== 0) {
            $model->upgradePasswordHash($account->id, $password);
        }

        $this->resetFailedLogin($account->id);

        // Đồng bộ API login với hệ thống MVC cũ.
        // JWT dùng cho Web API, còn các trang Admin MVC hiện tại vẫn kiểm tra bằng $_SESSION.
        // Vì vậy khi đăng nhập API thành công cần ghi session để Admin/center hoạt động.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = (int)$account->id;
        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;
        $_SESSION['full_name'] = $account->full_name ?? $account->username;
        $_SESSION['email'] = $account->email ?? '';

        $token = JwtHelper::createToken($account);

        ApiResponse::success(array(
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 7200,
            'user' => array(
                'id' => (int)$account->id,
                'username' => $account->username,
                'email' => $account->email,
                'full_name' => $account->full_name,
                'role' => $account->role
            )
        ), 'Đăng nhập API thành công');
    }

    public function me()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        ApiResponse::success($user, 'Lấy thông tin người dùng thành công');
    }

    public function profile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $data = $this->input();

        $stmt = $this->db->prepare('UPDATE account SET full_name=:full_name, email=:email, phone=:phone, address=:address WHERE id=:id');
        $stmt->execute(array(
            ':id' => (int)$user->id,
            ':full_name' => trim($data['full_name'] ?? ''),
            ':email' => trim($data['email'] ?? $user->email),
            ':phone' => trim($data['phone'] ?? ''),
            ':address' => trim($data['address'] ?? '')
        ));

        ApiResponse::success(null, 'Cập nhật hồ sơ thành công');
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $user = ApiAuth::requireUser();
        $data = $this->input();

        $oldPassword = trim($data['old_password'] ?? '');
        $newPassword = trim($data['new_password'] ?? '');

        if (strlen($newPassword) < 6) {
            ApiResponse::error('Mật khẩu mới phải từ 6 ký tự', 422);
        }

        $model = new AccountModel($this->db);
        $account = $model->getById($user->id);

        if (!$model->verifyPassword($oldPassword, $account->password)) {
            ApiResponse::error('Mật khẩu cũ không đúng', 400);
        }

        $model->changePassword($user->id, $newPassword);
        ApiResponse::success(null, 'Đổi mật khẩu thành công');
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $data = $this->input();
        $email = trim($data['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Email không hợp lệ', 422);
        }

        $token = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare('UPDATE account SET reset_token=:token, reset_expires_at=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email=:email');
        $stmt->execute(array(':token' => $token, ':email' => $email));

        ApiResponse::success(array('reset_token_demo' => $token), 'Đã tạo token quên mật khẩu ở mức mô phỏng');
    }

    public function refresh()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::error('Method Not Allowed', 405);
        }

        $token = ApiAuth::getBearerToken();
        if (!$token) {
            ApiResponse::error('Thiếu token để refresh', 401);
        }

        try {
            $newToken = JwtHelper::refreshToken($token);
            ApiResponse::success(array('token' => $newToken, 'token_type' => 'Bearer'), 'Refresh token thành công');
        } catch (Exception $e) {
            ApiResponse::error('Không thể refresh token: ' . $e->getMessage(), 401);
        }
    }

    private function ensureFailedLoginColumns()
    {
        try {
            if (!$this->columnExists('account', 'failed_login_count')) {
                $this->db->exec('ALTER TABLE account ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0');
            }
            if (!$this->columnExists('account', 'locked_until')) {
                $this->db->exec('ALTER TABLE account ADD COLUMN locked_until DATETIME NULL');
            }
        } catch (Exception $e) {}
    }

    private function increaseFailedLogin($username)
    {
        $this->ensureFailedLoginColumns();
        try {
            $stmt = $this->db->prepare('UPDATE account SET failed_login_count = failed_login_count + 1, locked_until = IF(failed_login_count + 1 >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), locked_until) WHERE username = :username');
            $stmt->execute(array(':username' => $username));
        } catch (Exception $e) {}
    }

    private function resetFailedLogin($id)
    {
        $this->ensureFailedLoginColumns();
        try {
            $stmt = $this->db->prepare('UPDATE account SET failed_login_count = 0, locked_until = NULL WHERE id = :id');
            $stmt->execute(array(':id' => (int)$id));
        } catch (Exception $e) {}
    }
}
?>

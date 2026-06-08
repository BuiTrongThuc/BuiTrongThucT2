<?php
class AccountModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByUsername($username)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE username = :username LIMIT 1');
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE email = :email LIMIT 1');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByRememberToken($token)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE remember_token = :token AND remember_expires_at IS NOT NULL AND remember_expires_at > NOW() LIMIT 1');
        $stmt->bindValue(':token', hash('sha256', $token));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByVerifyToken($token)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE email_verify_token = :token LIMIT 1');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getByResetToken($token)
    {
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE reset_token = :token AND reset_expires_at IS NOT NULL AND reset_expires_at > NOW() LIMIT 1');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getAll()
    {
        $stmt = $this->conn->query('SELECT id, username, email, full_name, phone, address, avatar, role, is_active, email_verified_at, created_at FROM account ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function create($username, $password, $email, $fullName = '')
    {
        $verifyToken = bin2hex(random_bytes(24));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('INSERT INTO account (username, password, email, full_name, role, is_active, email_verify_token) VALUES (:username, :password, :email, :full_name, "user", 1, :verify_token)');
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', $passwordHash);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':full_name', $fullName);
        $stmt->bindValue(':verify_token', $verifyToken);
        $stmt->execute();
        return $verifyToken;
    }

    public function verifyPassword($plainPassword, $storedPassword)
    {
        if (password_verify($plainPassword, $storedPassword)) {
            return true;
        }
        // Tương thích dữ liệu cũ đang dùng MD5 trong project gốc
        return md5($plainPassword) === $storedPassword;
    }

    public function upgradePasswordHash($id, $plainPassword)
    {
        $stmt = $this->conn->prepare('UPDATE account SET password = :password WHERE id = :id');
        $stmt->bindValue(':password', password_hash($plainPassword, PASSWORD_DEFAULT));
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function changePassword($id, $newPassword)
    {
        return $this->upgradePasswordHash($id, $newPassword);
    }

    public function updateProfile($id, $fullName, $email, $phone, $address, $avatar = null)
    {
        if ($avatar !== null) {
            $stmt = $this->conn->prepare('UPDATE account SET full_name = :full_name, email = :email, phone = :phone, address = :address, avatar = :avatar WHERE id = :id');
            $stmt->bindValue(':avatar', $avatar);
        } else {
            $stmt = $this->conn->prepare('UPDATE account SET full_name = :full_name, email = :email, phone = :phone, address = :address WHERE id = :id');
        }
        $stmt->bindValue(':full_name', $fullName);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':phone', $phone);
        $stmt->bindValue(':address', $address);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function setRememberToken($id, $token)
    {
        $stmt = $this->conn->prepare('UPDATE account SET remember_token = :token, remember_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = :id');
        $stmt->bindValue(':token', hash('sha256', $token));
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function clearRememberToken($id)
    {
        $stmt = $this->conn->prepare('UPDATE account SET remember_token = NULL, remember_expires_at = NULL WHERE id = :id');
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function setResetToken($id)
    {
        $token = bin2hex(random_bytes(24));
        $stmt = $this->conn->prepare('UPDATE account SET reset_token = :token, reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = :id');
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $token;
    }

    public function clearResetToken($id)
    {
        $stmt = $this->conn->prepare('UPDATE account SET reset_token = NULL, reset_expires_at = NULL WHERE id = :id');
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markEmailVerified($id)
    {
        $stmt = $this->conn->prepare('UPDATE account SET email_verified_at = NOW(), email_verify_token = NULL WHERE id = :id');
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleActive($id, $isActive)
    {
        $stmt = $this->conn->prepare('UPDATE account SET is_active = :is_active WHERE id = :id');
        $stmt->bindValue(':is_active', (int)$isActive, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateRole($id, $role)
    {
        $role = $role === 'admin' ? 'admin' : 'user';
        $stmt = $this->conn->prepare('UPDATE account SET role = :role WHERE id = :id');
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>

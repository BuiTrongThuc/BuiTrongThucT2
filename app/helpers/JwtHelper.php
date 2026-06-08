<?php
class JwtHelper
{
    private static $secret = 'WEBBANHANG_LAB56_SECRET_KEY_CHANGE_ME_2026';
    private static $issuer = 'webbanhang-lab56';
    private static $ttl = 7200; // 2 giờ

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function createToken($account)
    {
        $now = time();
        $payload = array(
            'iss' => self::$issuer,
            'iat' => $now,
            'exp' => $now + self::$ttl,
            'sub' => (int)$account->id,
            'username' => $account->username,
            'role' => $account->role,
            'full_name' => $account->full_name ?? ''
        );

        $header = array('typ' => 'JWT', 'alg' => 'HS256');
        $segments = array(
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload))
        );

        $signature = hash_hmac('sha256', implode('.', $segments), self::$secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decodeToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Token không đúng định dạng');
        }

        list($header64, $payload64, $signature64) = $parts;
        $expected = self::base64UrlEncode(hash_hmac('sha256', $header64 . '.' . $payload64, self::$secret, true));

        if (!hash_equals($expected, $signature64)) {
            throw new Exception('Token sai chữ ký');
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);
        if (!$payload) {
            throw new Exception('Token payload không hợp lệ');
        }

        if (!empty($payload['exp']) && time() > (int)$payload['exp']) {
            throw new Exception('Token đã hết hạn');
        }

        return $payload;
    }

    public static function refreshToken($token)
    {
        $payload = self::decodeToken($token);
        $account = (object)array(
            'id' => $payload['sub'],
            'username' => $payload['username'] ?? '',
            'role' => $payload['role'] ?? 'user',
            'full_name' => $payload['full_name'] ?? ''
        );
        return self::createToken($account);
    }
}
?>

<?php
class ApiResponse
{
    public static function json($data = null, $message = 'OK', $status = 200, $success = true)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        echo json_encode(array(
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data
        ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    public static function success($data = null, $message = 'OK', $status = 200)
    {
        self::json($data, $message, $status, true);
    }

    public static function error($message = 'Error', $status = 400, $errors = null)
    {
        self::json($errors, $message, $status, false);
    }
}
?>

<?php
require_once 'config/database.php';
require_once 'app/helpers/ApiResponse.php';
require_once 'app/helpers/ApiAuth.php';

class ApiBaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    protected function input()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && !empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            return $json;
        }

        parse_str($raw, $data);
        return is_array($data) ? $data : array();
    }

    protected function columnExists($table, $column)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column");
        $stmt->execute(array(':table' => $table, ':column' => $column));
        return (int)$stmt->fetchColumn() > 0;
    }

    protected function tableExists($table)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
        $stmt->execute(array(':table' => $table));
        return (int)$stmt->fetchColumn() > 0;
    }

    protected function safeProductSelect()
    {
        return "SELECT p.*, c.name AS category_name
                FROM product p
                LEFT JOIN category c ON c.id = p.category_id";
    }

    protected function validateImageName($filename)
    {
        if ($filename === null || $filename === '') {
            return true;
        }
        return (bool)preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename);
    }
}
?>

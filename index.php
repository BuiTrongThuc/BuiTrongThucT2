<?php
session_start();

define('BASE_URL', '');

// Tự đăng nhập bằng Remember Me nếu session hết hạn nhưng cookie còn hiệu lực
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_login'])) {
    require_once 'config/database.php';
    require_once 'app/models/AccountModel.php';

    $db = (new Database())->getConnection();
    $accountModel = new AccountModel($db);
    $account = $accountModel->getByRememberToken($_COOKIE['remember_login']);

    if ($account && (int)$account->is_active === 1) {
        $_SESSION['user_id'] = $account->id;
        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;
        $_SESSION['full_name'] = $account->full_name ?? '';
        $_SESSION['avatar'] = $account->avatar ?? '';
    } else {
        setcookie('remember_login', '', time() - 3600, '/', '', false, true);
    }
}

$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = $url === '' ? array() : explode('/', $url);

// RESTful API router cho Lab 5&6.
// Các đường dẫn API bắt đầu bằng /api và luôn trả JSON.
if (isset($url[0]) && strtolower($url[0]) === 'api') {
    require_once 'app/helpers/ApiResponse.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        ApiResponse::success(null, 'OK');
    }

    $resource = strtolower($url[1] ?? '');
    $id = $url[2] ?? null;
    $subAction = strtolower($url[3] ?? '');

    try {
        switch ($resource) {
            case 'products':
                require_once 'app/controllers/api/ProductApiController.php';
                $api = new ProductApiController();
                if ($id === null || $id === '') {
                    $api->index();
                } elseif ($id === 'search') {
                    $api->index();
                } elseif ($subAction === '') {
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') $api->detail($id);
                    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') $api->update($id);
                    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') $api->delete($id);
                    else ApiResponse::error('Method Not Allowed', 405);
                } else {
                    ApiResponse::error('API endpoint không tồn tại', 404);
                }
                break;

            case 'categories':
                require_once 'app/controllers/api/CategoryApiController.php';
                $api = new CategoryApiController();
                if ($id === null || $id === '') {
                    $api->index();
                } elseif ($subAction === '') {
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') $api->detail($id);
                    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') $api->update($id);
                    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') $api->delete($id);
                    else ApiResponse::error('Method Not Allowed', 405);
                } else {
                    ApiResponse::error('API endpoint không tồn tại', 404);
                }
                break;

            case 'cart':
                require_once 'app/controllers/api/CartApiController.php';
                $api = new CartApiController();
                if ($id === null || $id === '') {
                    $api->index();
                } elseif ($id === 'total') {
                    $api->total();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $api->update($id);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    $api->delete($id);
                } else {
                    ApiResponse::error('Method Not Allowed', 405);
                }
                break;

            case 'orders':
                require_once 'app/controllers/api/OrderApiController.php';
                $api = new OrderApiController();
                if ($id === null || $id === '') {
                    $api->index();
                } elseif ($subAction === 'cancel') {
                    $api->cancel($id);
                } elseif ($subAction === 'status') {
                    $api->status($id);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $api->detail($id);
                } else {
                    ApiResponse::error('API endpoint không tồn tại', 404);
                }
                break;

            case 'payments':
                require_once 'app/controllers/api/PaymentApiController.php';
                $api = new PaymentApiController();
                if ($id === null || $id === '') {
                    $api->create();
                } else {
                    $api->update($id);
                }
                break;

            case 'banners':
                require_once 'app/controllers/api/BannerApiController.php';
                $api = new BannerApiController();
                $api->index($id);
                break;

            case 'auth':
                require_once 'app/controllers/api/AuthApiController.php';
                $api = new AuthApiController();
                $action = strtolower($id ?? '');
                if ($action === 'register') $api->register();
                elseif ($action === 'login') $api->login();
                elseif ($action === 'me') $api->me();
                elseif ($action === 'profile') $api->profile();
                elseif ($action === 'change-password') $api->changePassword();
                elseif ($action === 'forgot-password') $api->forgotPassword();
                elseif ($action === 'refresh') $api->refresh();
                else ApiResponse::error('API auth endpoint không tồn tại', 404);
                break;

            default:
                ApiResponse::error('API resource không tồn tại', 404);
        }
    } catch (Throwable $e) {
        ApiResponse::error('API server error: ' . $e->getMessage(), 500);
    }
    exit();
}

// Mặc định vào ProductController
$controllerName = isset($url[0]) && $url[0] !== ''
    ? ucfirst($url[0]) . 'Controller'
    : 'ProductController';

// Mặc định vào hàm list
$action = isset($url[1]) && $url[1] !== ''
    ? $url[1]
    : 'list';

$controllerPath = 'app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerPath)) {
    http_response_code(404);
    die('Controller not found');
}

require_once $controllerPath;

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    die('Action not found');
}

call_user_func_array(array($controller, $action), array_slice($url, 2));
?>

<?php
class ApiDemoController
{
    public function products()
    {
        $pageTitle = 'Demo API sản phẩm';
        include 'app/views/api_demo/products.php';
    }

    public function login()
    {
        $pageTitle = 'Demo API đăng nhập JWT';
        include 'app/views/api_demo/login.php';
    }

    public function cart()
    {
        $pageTitle = 'Demo API giỏ hàng';
        include 'app/views/api_demo/cart.php';
    }
}
?>

<?php
require_once __DIR__ . '/config.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/app/core/' . $class . '.php',
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$router = new Router();

$page = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if ($page === '' || $page === 'index' || $page === 'index.php') {
    $page = 'home';
}

$router->get('home', [HomeController::class, 'index']);
$router->get('catalog', [ProductController::class, 'catalog']);
$router->get('cart', [CartController::class, 'index']);
$router->get('subscription', [SubscriptionController::class, 'index']);
$router->get('promo', [PromoController::class, 'index']);
$router->get('orders', [OrdersController::class, 'index']);
$router->get('account', [AccountController::class, 'index']);
$router->get('login', [AuthController::class, 'login']);
$router->get('register', [AuthController::class, 'register']);
$router->get('admin', [AdminController::class, 'index']);
$router->post('login', [AuthController::class, 'login']);

$router->dispatch($page, $_SERVER['REQUEST_METHOD']);

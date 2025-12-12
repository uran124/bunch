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

Session::start();

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
$router->get('orders-history', [OrdersController::class, 'history']);
$router->get('account', [AccountController::class, 'index']);
$router->get('login', [AuthController::class, 'login']);
$router->get('logout', [AuthController::class, 'logout']);
$router->get('register', [AuthController::class, 'register']);
$router->get('recover', [AuthController::class, 'recover']);
$router->get('admin', [AdminController::class, 'index']);
$router->get('admin-users', [AdminController::class, 'users']);
$router->get('admin-user', [AdminController::class, 'user']);
$router->get('admin-broadcast', [AdminController::class, 'broadcasts']);
$router->get('admin-group-create', [AdminController::class, 'groupCreate']);
$router->get('admin-products', [AdminController::class, 'catalogProducts']);
$router->get('admin-promos', [AdminController::class, 'catalogPromos']);
$router->get('admin-attributes', [AdminController::class, 'catalogAttributes']);
$router->get('admin-supplies', [AdminController::class, 'catalogSupplies']);
$router->get('admin-supply-standing', [AdminController::class, 'supplyStandingForm']);
$router->get('admin-supply-single', [AdminController::class, 'supplySingleForm']);
$router->get('admin-supply-edit', [AdminController::class, 'editSupply']);
$router->get('admin-orders-one-time', [AdminController::class, 'ordersOneTime']);
$router->get('admin-orders-subscriptions', [AdminController::class, 'ordersSubscriptions']);
$router->get('admin-orders-wholesale', [AdminController::class, 'ordersWholesale']);
$router->get('admin-content-static', [AdminController::class, 'contentStatic']);
$router->get('admin-content-products', [AdminController::class, 'contentProducts']);
$router->get('admin-content-sections', [AdminController::class, 'contentSections']);
$router->post('login', [AuthController::class, 'login']);
$router->post('register', [AuthController::class, 'register']);
$router->post('recover', [AuthController::class, 'recover']);
$router->post('admin-users-toggle', [AdminController::class, 'toggleUserActive']);
$router->post('admin-group-create', [AdminController::class, 'saveGroup']);
$router->post('admin-broadcast', [AdminController::class, 'createBroadcast']);
$router->post('admin-supply-standing', [AdminController::class, 'createStandingSupply']);
$router->post('admin-supply-single', [AdminController::class, 'createSingleSupply']);
$router->post('admin-supply-update', [AdminController::class, 'updateSupply']);
$router->post('admin-supply-toggle-card', [AdminController::class, 'toggleSupplyCard']);
$router->post('admin-attribute-save', [AdminController::class, 'saveAttribute']);
$router->post('admin-attribute-delete', [AdminController::class, 'deleteAttribute']);
$router->post('admin-attribute-value-save', [AdminController::class, 'saveAttributeValue']);
$router->post('admin-attribute-value-delete', [AdminController::class, 'deleteAttributeValue']);
$router->post('admin-product-save', [AdminController::class, 'saveProduct']);
$router->post('admin-product-delete', [AdminController::class, 'deleteProduct']);
$router->post('admin-order-update', [AdminController::class, 'updateOneTimeOrder']);
$router->post('cart-add', [CartController::class, 'add']);
$router->post('cart-update', [CartController::class, 'update']);
$router->post('cart-remove', [CartController::class, 'remove']);
$router->post('cart-checkout', [CartController::class, 'checkout']);
$router->post('account-notifications', [AccountController::class, 'updateNotifications']);
$router->post('account-pin', [AccountController::class, 'updatePin']);

$publicPages = ['login', 'register', 'recover'];

if (!Auth::check() && !in_array($page, $publicPages, true)) {
    header('Location: /?page=login');
    exit;
}

if (Auth::check() && in_array($page, $publicPages, true)) {
    header('Location: /?page=home');
    exit;
}

$router->dispatch($page, $_SERVER['REQUEST_METHOD']);

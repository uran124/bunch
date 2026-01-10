<?php
require_once __DIR__ . '/config.php';

$logDir = __DIR__ . '/storage/logs';
$logFile = $logDir . '/error.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logError = static function (string $context, Throwable $e) use ($logFile): void {
    $timestamp = date('Y-m-d H:i:s');
    $message = sprintf(
        "[%s] %s: %s in %s:%d\n",
        $timestamp,
        $context,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );

    error_log($message, 3, $logFile);
};

set_exception_handler(static function (Throwable $e) use ($logError): void {
    $logError('Unhandled exception', $e);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    if (defined('APP_ENV') && APP_ENV === 'dev') {
        echo 'Ошибка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }

    echo 'Сервис временно недоступен';
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function () use ($logFile): void {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $message = sprintf(
        "[%s] Shutdown error: %s in %s:%d\n",
        $timestamp,
        $error['message'],
        $error['file'],
        $error['line']
    );

    error_log($message, 3, $logFile);
});

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

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if (str_starts_with($path, 'api/')) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

Session::start();

$router = new Router();

$page = $_GET['page'] ?? $path;

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
$router->get('order-edit', [OrdersController::class, 'edit']);
$router->get('order-payment', [OrdersController::class, 'payment']);
$router->get('account', [AccountController::class, 'index']);
$router->get('login', [AuthController::class, 'login']);
$router->get('logout', [AuthController::class, 'logout']);
$router->get('register', [AuthController::class, 'register']);
$router->get('recover', [AuthController::class, 'recover']);
$router->get('policy', [LegalController::class, 'policy']);
$router->get('consent', [LegalController::class, 'consent']);
$router->get('offer', [LegalController::class, 'offer']);
$router->get('admin', [AdminController::class, 'index']);
$router->get('admin-users', [AdminController::class, 'users']);
$router->get('admin-user', [AdminController::class, 'user']);
$router->get('admin-broadcast', [AdminController::class, 'broadcasts']);
$router->get('admin-group-create', [AdminController::class, 'groupCreate']);
$router->get('admin-products', [AdminController::class, 'catalogProducts']);
$router->get('admin-product-form', [AdminController::class, 'productForm']);
$router->get('admin-promos', [AdminController::class, 'catalogPromos']);
$router->get('admin-auction-create', [AdminController::class, 'auctionCreate']);
$router->get('admin-auction-edit', [AdminController::class, 'auctionEdit']);
$router->get('admin-auction-view', [AdminController::class, 'auctionView']);
$router->get('admin-attributes', [AdminController::class, 'catalogAttributes']);
$router->get('admin-supplies', [AdminController::class, 'catalogSupplies']);
$router->get('admin-supply-standing', [AdminController::class, 'supplyStandingForm']);
$router->get('admin-supply-single', [AdminController::class, 'supplySingleForm']);
$router->get('admin-supply-edit', [AdminController::class, 'editSupply']);
$router->get('admin-orders-one-time', [AdminController::class, 'ordersOneTime']);
$router->get('admin-order-one-time-edit', [AdminController::class, 'orderOneTimeEdit']);
$router->get('admin-orders-subscriptions', [AdminController::class, 'ordersSubscriptions']);
$router->get('admin-orders-wholesale', [AdminController::class, 'ordersWholesale']);
$router->get('admin-services-payment', [AdminController::class, 'serviceOnlinePayment']);
$router->get('admin-services-delivery', [AdminController::class, 'serviceDelivery']);
$router->get('admin-services-telegram', [AdminController::class, 'serviceTelegram']);
$router->get('admin-content-static', [AdminController::class, 'contentStatic']);
$router->get('admin-content-products', [AdminController::class, 'contentProducts']);
$router->get('admin-content-sections', [AdminController::class, 'contentSections']);
$router->post('api-dadata-clean-address', [ApiController::class, 'cleanDadataAddress']);
$router->post('login', [AuthController::class, 'login']);
$router->post('register', [AuthController::class, 'register']);
$router->post('recover', [AuthController::class, 'recover']);
$router->post('admin-users-toggle', [AdminController::class, 'toggleUserActive']);
$router->post('admin-group-create', [AdminController::class, 'saveGroup']);
$router->post('admin-broadcast', [AdminController::class, 'createBroadcast']);
$router->post('admin-user-role', [AdminController::class, 'updateUserRole']);
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
$router->post('admin-product-toggle', [AdminController::class, 'toggleProductActive']);
$router->post('admin-services-telegram', [AdminController::class, 'saveServiceTelegram']);
$router->post('admin-lottery-save', [AdminController::class, 'saveLottery']);
$router->post('admin-auction-save', [AdminController::class, 'saveAuctionLot']);
$router->post('admin-auction-update', [AdminController::class, 'updateAuctionLot']);
$router->post('admin-promo-item-save', [AdminController::class, 'savePromoItem']);
$router->post('admin-promo-categories-save', [AdminController::class, 'savePromoCategories']);
$router->post('admin-promo-settings-save', [AdminController::class, 'savePromoSettings']);
$router->post('admin-order-update', [AdminController::class, 'updateOneTimeOrder']);
$router->post('cart-add', [CartController::class, 'add']);
$router->post('cart-update', [CartController::class, 'update']);
$router->post('cart-remove', [CartController::class, 'remove']);
$router->post('cart-checkout', [CartController::class, 'checkout']);
$router->post('order-edit', [OrdersController::class, 'update']);
$router->post('order-payment', [OrdersController::class, 'pay']);
$router->post('account-notifications', [AccountController::class, 'updateNotifications']);
$router->post('account-pin', [AccountController::class, 'updatePin']);

$publicPages = [
    'home',
    'promo',
    'login',
    'register',
    'recover',
    'policy',
    'consent',
    'offer',
    'api-dadata-clean-address',
    'cart-add',
    'cart-update',
    'cart-remove',
];

if (!Auth::check() && !in_array($page, $publicPages, true)) {
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $acceptHeader = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $isAjax = $requestedWith === 'xmlhttprequest' || str_contains($acceptHeader, 'application/json');

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Требуется авторизация']);
        exit;
    }

    Session::set('auth_redirect', $_SERVER['REQUEST_URI'] ?? '/');
    header('Location: /?page=login');
    exit;
}

if (Auth::check() && in_array($page, ['login', 'register', 'recover'], true)) {
    header('Location: /?page=home');
    exit;
}

$router->dispatch($page, $_SERVER['REQUEST_METHOD']);

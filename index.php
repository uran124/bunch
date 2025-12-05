<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/core/Database.php';

// index.php
$page = $_GET['page'] ?? 'home';

$router->get('home', [HomeController::class, 'index']);
$router->get('catalog', [ProductController::class, 'catalog']);
$router->get('cart', [CartController::class, 'index']);
$router->get('subscription', [SubscriptionController::class, 'index']);
$router->get('promo', [PromoController::class, 'index']);
$router->get('account', [AccountController::class, 'index']);
$router->get('login', [AuthController::class, 'login']);
$router->get('register', [AuthController::class, 'register']);

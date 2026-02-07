<?php

return [
    'register' => static function (Router $router): void {
        $router->get('home', [HomeController::class, 'index']);
        $router->get('catalog', [ProductController::class, 'catalog']);
        $router->get('cart', [CartController::class, 'index']);
        $router->get('subscription', [SubscriptionController::class, 'index']);
        $router->get('promo', [PromoController::class, 'index']);
        $router->get('wholesale', [WholesaleController::class, 'index']);
        $router->get('orders', [OrdersController::class, 'index']);
        $router->get('orders-history', [OrdersController::class, 'history']);
        $router->get('order-edit', [OrdersController::class, 'edit']);
        $router->get('order-payment', [OrdersController::class, 'payment']);
        $router->get('payment-result', [PaymentController::class, 'result']);
        $router->post('payment-result', [PaymentController::class, 'result']);
        $router->get('payment-success', [PaymentController::class, 'success']);
        $router->get('payment-fail', [PaymentController::class, 'fail']);
        $router->get('account', [AccountController::class, 'index']);
        $router->get('account-notifications', [AccountController::class, 'notifications']);
        $router->get('account-calendar', [AccountController::class, 'calendar']);
        $router->get('login', [AuthController::class, 'login']);
        $router->get('logout', [AuthController::class, 'logout']);
        $router->get('register', [AuthController::class, 'register']);
        $router->get('recover', [AuthController::class, 'recover']);
        $router->get('policy', [LegalController::class, 'policy']);
        $router->get('consent', [LegalController::class, 'consent']);
        $router->get('offer', [LegalController::class, 'offer']);
        $router->get('about', [InfoController::class, 'about']);
        $router->get('roses', [InfoController::class, 'roses']);
        $router->get('delivery', [InfoController::class, 'delivery']);
        $router->get('discount', [InfoController::class, 'discount']);
        $router->get('static', [StaticPageController::class, 'show']);
        $router->get('admin', [AdminController::class, 'index']);
        $router->get('admin-users', [AdminController::class, 'users']);
        $router->get('admin-user', [AdminController::class, 'user']);
        $router->get('admin-cashback', [AdminController::class, 'cashback']);
        $router->get('admin-broadcast', [AdminController::class, 'broadcasts']);
        $router->get('admin-group-create', [AdminController::class, 'groupCreate']);
        $router->get('admin-products', [AdminController::class, 'catalogProducts']);
        $router->get('admin-product-form', [AdminController::class, 'productForm']);
        $router->get('admin-promos', [AdminController::class, 'catalogPromos']);
        $router->get('admin-auction-create', [AdminController::class, 'auctionCreate']);
        $router->get('admin-promo-item-create', [AdminController::class, 'promoItemCreate']);
        $router->get('admin-lottery-create', [AdminController::class, 'lotteryCreate']);
        $router->get('admin-promo-item-edit', [AdminController::class, 'promoItemEdit']);
        $router->get('admin-lottery-edit', [AdminController::class, 'lotteryEdit']);
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
        $router->get('admin-services-frontpad', [AdminController::class, 'serviceFrontpad']);
        $router->get('admin-content-static', [AdminController::class, 'contentStatic']);
        $router->get('admin-content-products', [AdminController::class, 'contentProducts']);
        $router->get('admin-content-sections', [AdminController::class, 'contentSections']);
        $router->post('api-dadata-clean-address', [ApiController::class, 'cleanDadataAddress']);
        $router->post('login', [AuthController::class, 'login']);
        $router->post('register', [AuthController::class, 'register']);
        $router->post('recover', [AuthController::class, 'recover']);
        $router->post('admin-users-toggle', [AdminController::class, 'toggleUserActive']);
        $router->post('admin-cashback-level-save', [AdminController::class, 'saveCashbackLevel']);
        $router->post('admin-cashback-product-toggle', [AdminController::class, 'toggleCashbackProduct']);
        $router->post('admin-cashback-promo-toggle', [AdminController::class, 'toggleCashbackPromo']);
        $router->post('admin-group-create', [AdminController::class, 'saveGroup']);
        $router->post('admin-broadcast', [AdminController::class, 'createBroadcast']);
        $router->post('admin-user-role', [AdminController::class, 'updateUserRole']);
        $router->post('admin-user-notifications', [AdminController::class, 'updateUserNotifications']);
        $router->post('admin-user-broadcast-group', [AdminController::class, 'updateUserBroadcastGroup']);
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
        $router->post('admin-services-payment', [AdminController::class, 'saveServicePayment']);
        $router->post('admin-services-telegram', [AdminController::class, 'saveServiceTelegram']);
        $router->post('admin-services-frontpad', [AdminController::class, 'saveServiceFrontpad']);
        $router->post('admin-services-delivery', [AdminController::class, 'saveServiceDelivery']);
        $router->post('admin-lottery-save', [AdminController::class, 'saveLottery']);
        $router->post('admin-lottery-update', [AdminController::class, 'updateLottery']);
        $router->post('admin-auction-save', [AdminController::class, 'saveAuctionLot']);
        $router->post('admin-auction-update', [AdminController::class, 'updateAuctionLot']);
        $router->post('admin-promo-item-save', [AdminController::class, 'savePromoItem']);
        $router->post('admin-promo-item-update', [AdminController::class, 'updatePromoItem']);
        $router->post('admin-promo-categories-save', [AdminController::class, 'savePromoCategories']);
        $router->post('admin-promo-settings-save', [AdminController::class, 'savePromoSettings']);
        $router->post('admin-order-update', [AdminController::class, 'updateOneTimeOrder']);
        $router->post('admin-order-delete', [AdminController::class, 'deleteOneTimeOrder']);
        $router->post('admin-static-page-save', [AdminController::class, 'saveStaticPage']);
        $router->post('admin-static-page-toggle', [AdminController::class, 'toggleStaticPage']);
        $router->post('admin-static-page-delete', [AdminController::class, 'deleteStaticPage']);
        $router->post('cart-add', [CartController::class, 'add']);
        $router->post('cart-update', [CartController::class, 'update']);
        $router->post('cart-remove', [CartController::class, 'remove']);
        $router->post('cart-checkout', [CartController::class, 'checkout']);
        $router->post('order-edit', [OrdersController::class, 'update']);
        $router->post('order-payment', [OrdersController::class, 'pay']);
        $router->post('account-notifications', [AccountController::class, 'updateNotifications']);
        $router->post('account-pin', [AccountController::class, 'updatePin']);
        $router->get('support-messages', [SupportController::class, 'listMessages']);
        $router->post('support-message', [SupportController::class, 'sendMessage']);
    },
    'publicPages' => [
        'home',
        'promo',
        'login',
        'register',
        'recover',
        'policy',
        'consent',
        'offer',
        'about',
        'roses',
        'delivery',
        'discount',
        'static',
        'payment-result',
        'payment-success',
        'payment-fail',
        'api-dadata-clean-address',
        'cart-add',
        'cart-update',
        'cart-remove',
        'support-messages',
        'support-message',
    ],
];

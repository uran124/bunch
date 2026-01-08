<?php
$currentPage = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: 'home';

$navItems = [
    [
        'id' => 'home',
        'label' => 'Главная',
        'href' => '/?page=home',
        'icon' => 'home'
    ],
    [
        'id' => 'promo',
        'label' => 'Акции',
        'href' => '/?page=promo',
        'icon' => 'local_offer'
    ],
    [
        'id' => 'cart',
        'label' => 'Корзина',
        'href' => '/?page=cart',
        'icon' => 'shopping_cart'
    ],
    [
        'id' => 'orders',
        'label' => 'Заказы',
        'href' => '/?page=orders',
        'icon' => 'inventory_2'
    ],
    [
        'id' => 'account',
        'label' => 'Профиль',
        'href' => '/?page=account',
        'icon' => 'account_circle'
    ],
];

$cart = class_exists('Cart') ? new Cart() : null;
$cartCount = $cart ? $cart->getItemCount() : 0;
$userId = class_exists('Auth') ? Auth::userId() : null;
$hasActiveOrders = false;
$hasActiveSubscriptions = false;
if ($userId) {
    $orderModel = class_exists('Order') ? new Order() : null;
    $subscriptionModel = class_exists('Subscription') ? new Subscription() : null;
    $hasActiveOrders = $orderModel ? !empty($orderModel->getActiveOrdersForUser($userId)) : false;
    $hasActiveSubscriptions = $subscriptionModel ? !empty($subscriptionModel->getActiveListForUser($userId)) : false;
}
?>

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto grid w-full max-w-6xl grid-cols-5 gap-2 px-3 pt-2 pb-[calc(0.65rem+env(safe-area-inset-bottom))] text-xs font-semibold text-slate-500">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['id']; ?>
            <?php
            $indicatorAttributes = '';
            $indicatorClasses = '';
            if ($item['id'] === 'cart') {
                $cartActive = $cartCount > 0;
                $indicatorAttributes = sprintf('data-cart-indicator data-cart-active="%s"', $cartActive ? 'true' : 'false');
                $indicatorClasses = ' data-[cart-active=true]:bg-rose-50 data-[cart-active=true]:text-rose-600 data-[cart-active=true]:shadow-inner data-[cart-active=true]:shadow-rose-100';
            }
            if ($item['id'] === 'orders') {
                $ordersActive = $hasActiveOrders || $hasActiveSubscriptions;
                $indicatorAttributes = sprintf('data-orders-indicator data-orders-active="%s"', $ordersActive ? 'true' : 'false');
                $indicatorClasses = ' data-[orders-active=true]:bg-emerald-50 data-[orders-active=true]:text-emerald-600 data-[orders-active=true]:shadow-inner data-[orders-active=true]:shadow-emerald-100';
            }
            ?>
            <a
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 transition <?php echo $isActive ? 'bg-rose-50 text-rose-600 shadow-inner shadow-rose-100' : 'hover:bg-slate-50 hover:text-rose-600'; ?><?php echo $indicatorClasses; ?>"
                href="<?php echo $item['href']; ?>"
                <?php echo $indicatorAttributes; ?>
            >
                <span class="material-symbols-rounded text-xl">
                    <?php echo $item['icon']; ?>
                </span>
                <span class="relative inline-flex items-center gap-1">
                    <?php echo $item['label']; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

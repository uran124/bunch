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
?>

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto grid w-full max-w-6xl grid-cols-5 gap-2 px-3 pt-2 pb-[calc(0.65rem+env(safe-area-inset-bottom))] text-xs font-semibold text-slate-500">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['id']; ?>
            <a
                class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 transition <?php echo $isActive ? 'bg-rose-50 text-rose-600 shadow-inner shadow-rose-100' : 'hover:bg-slate-50 hover:text-rose-600'; ?>"
                href="<?php echo $item['href']; ?>"
            >
                <span class="material-symbols-rounded text-xl">
                    <?php echo $item['icon']; ?>
                </span>
                <span class="relative inline-flex items-center gap-1">
                    <?php echo $item['label']; ?>
                    <?php if ($item['id'] === 'cart'): ?>
                        <span
                            data-cart-count
                            class="<?php echo $cartCount > 0 ? 'flex' : 'hidden'; ?> h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-600 px-1 text-[11px] font-bold text-white shadow-lg shadow-rose-200"
                        >
                            <?php echo (int) $cartCount; ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

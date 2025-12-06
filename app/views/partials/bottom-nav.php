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
?>

<nav class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 gap-2 border-t border-slate-200 bg-white/95 px-3 py-2 text-xs font-semibold text-slate-500 backdrop-blur">
    <?php foreach ($navItems as $item): ?>
        <?php $isActive = $currentPage === $item['id']; ?>
        <a
            class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 transition <?php echo $isActive ? 'bg-rose-50 text-rose-600 shadow-inner shadow-rose-100' : 'hover:bg-slate-50 hover:text-rose-600'; ?>"
            href="<?php echo $item['href']; ?>"
        >
            <span class="material-symbols-rounded text-xl">
                <?php echo $item['icon']; ?>
            </span>
            <span><?php echo $item['label']; ?></span>
        </a>
    <?php endforeach; ?>
</nav>

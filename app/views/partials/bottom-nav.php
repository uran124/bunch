<?php
$currentPage = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: 'home';

$navItems = [
    [
        'id' => 'home',
        'label' => 'Главная',
        'href' => '/?page=home',
        'icon' => '<path d="M4 10.5 12 4l8 6.5V19a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1z"/>'
    ],
    [
        'id' => 'promo',
        'label' => 'Акции',
        'href' => '/?page=promo',
        'icon' => '<path d="M5 8.5 12 4l7 4.5-7 4.5L5 8.5Zm7 4.5v7"/>'
    ],
    [
        'id' => 'cart',
        'label' => 'Корзина',
        'href' => '/?page=cart',
        'icon' => '<path d="M4 6h2l1.5 9h9l1-6h-11"/><circle cx="9.5" cy="17.5" r="1.5"/><circle cx="15.5" cy="17.5" r="1.5"/>'
    ],
    [
        'id' => 'orders',
        'label' => 'Заказы',
        'href' => '/?page=orders',
        'icon' => '<path d="M8 4h8l2 3v11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7z"/><path d="M9 10h6M9 13h6"/>'
    ],
    [
        'id' => 'account',
        'label' => 'Профиль',
        'href' => '/?page=account',
        'icon' => '<circle cx="12" cy="8.5" r="3"/><path d="M6.5 18c1.2-2 3.4-3.2 5.5-3.2 2.1 0 4.3 1.2 5.5 3.2"/>'
    ],
];
?>

<nav class="bottom-nav" aria-label="Навигация приложения">
    <?php foreach ($navItems as $item): ?>
        <?php $isActive = $currentPage === $item['id']; ?>
        <a class="bottom-nav__item <?php echo $isActive ? 'is-active' : ''; ?>" href="<?php echo $item['href']; ?>">
            <span class="bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <?php echo $item['icon']; ?>
                </svg>
            </span>
            <span class="bottom-nav__label"><?php echo $item['label']; ?></span>
        </a>
    <?php endforeach; ?>
</nav>

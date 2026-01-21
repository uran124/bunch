<?php
$pageMeta = $pageMeta ?? [];
$pageTitle = $pageMeta['title'] ?? 'Bunch flowers — панель';
$pageDescription = $pageMeta['description'] ?? 'Панель управления Bunch flowers.';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($currentPath === '' || $currentPath === 'index' || $currentPath === 'index.php') {
    $currentPage = 'home';
} elseif (str_starts_with($currentPath, 'static/')) {
    $currentPage = 'static';
} else {
    $currentPage = $currentPath;
}
$authPages = ['login', 'register', 'recover'];
$isAuthPage = in_array($currentPage, $authPages, true);
$isAdminPage = str_starts_with($currentPage, 'admin');
$isAuthenticated = class_exists('Auth') ? Auth::check() : false;
$currentUserRole = $currentUserRole ?? 'customer';
$isAdminUser = $currentUserRole === 'admin';
$mainClasses = 'mx-auto flex w-full max-w-6xl flex-1 flex-col gap-4 px-3 py-3 pb-[calc(6.5rem+env(safe-area-inset-bottom))] sm:gap-6 sm:px-4 sm:pt-8 sm:pb-[calc(6.5rem+env(safe-area-inset-bottom))]';
if ($currentPage === 'home') {
    $mainClasses = 'mx-auto flex w-full max-w-6xl flex-1 flex-col gap-4 px-3 py-3 pb-[calc(3rem+env(safe-area-inset-bottom))] sm:gap-6 sm:px-4 sm:pt-8 sm:pb-[calc(6.5rem+env(safe-area-inset-bottom))]';
}
if ($isAuthPage) {
    $mainClasses .= ' items-center justify-center';
}
if ($isAdminPage) {
    $mainClasses = 'mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 px-4 pb-10 pt-6 pl-20 sm:pl-24';
}
$bodyClasses = 'min-h-screen antialiased font-["Manrope",system-ui,sans-serif] flex flex-col pb-[calc(6.5rem+env(safe-area-inset-bottom))]';
if ($isAdminPage) {
    $bodyClasses .= ' bg-slate-950 text-slate-100';
} else {
    $bodyClasses .= ' bg-slate-50 text-slate-900';
}
$adminNavigation = [
    [
        'title' => 'Главная',
        'items' => [
            [
                'label' => 'Обзор',
                'href' => '/admin',
                'page' => 'admin',
                'icon' => 'dashboard',
            ],
        ],
    ],
    [
        'title' => 'Пользователи',
        'items' => [
            [
                'label' => 'Пользователи',
                'href' => '/admin-users',
                'page' => 'admin-users',
                'icon' => 'group',
            ],
            [
                'label' => 'Рассылки',
                'href' => '/admin-broadcast',
                'page' => 'admin-broadcast',
                'icon' => 'campaign',
            ],
        ],
    ],
    [
        'title' => 'Каталог',
        'items' => [
            [
                'label' => 'Товары',
                'href' => '/admin-products',
                'page' => 'admin-products',
                'icon' => 'inventory_2',
            ],
            [
                'label' => 'Акции',
                'href' => '/admin-promos',
                'page' => 'admin-promos',
                'icon' => 'local_offer',
            ],
            [
                'label' => 'Атрибуты',
                'href' => '/admin-attributes',
                'page' => 'admin-attributes',
                'icon' => 'tune',
            ],
            [
                'label' => 'Поставки',
                'href' => '/admin-supplies',
                'page' => 'admin-supplies',
                'icon' => 'local_shipping',
            ],
        ],
    ],
    [
        'title' => 'Заказы',
        'items' => [
            [
                'label' => 'Разовые',
                'href' => '/admin-orders-one-time',
                'page' => 'admin-orders-one-time',
                'icon' => 'shopping_bag',
            ],
            [
                'label' => 'Подписки',
                'href' => '/admin-orders-subscriptions',
                'page' => 'admin-orders-subscriptions',
                'icon' => 'autorenew',
            ],
            [
                'label' => 'Мелкий опт',
                'href' => '/admin-orders-wholesale',
                'page' => 'admin-orders-wholesale',
                'icon' => 'inventory',
            ],
        ],
    ],
    [
        'title' => 'Сервисы',
        'items' => [
            [
                'label' => 'Онлайн-оплата',
                'href' => '/admin-services-payment',
                'page' => 'admin-services-payment',
                'icon' => 'payments',
            ],
            [
                'label' => 'Веб-аналитика',
                'href' => '#',
                'page' => '',
                'icon' => 'monitoring',
                'disabled' => true,
            ],
            [
                'label' => 'FrontPad',
                'href' => '/admin-services-frontpad',
                'page' => 'admin-services-frontpad',
                'icon' => 'hub',
            ],
            [
                'label' => 'Доставка',
                'href' => '/admin-services-delivery',
                'page' => 'admin-services-delivery',
                'icon' => 'map',
            ],
            [
                'label' => 'Telegram бот',
                'href' => '/admin-services-telegram',
                'page' => 'admin-services-telegram',
                'icon' => 'send',
            ],
        ],
    ],
    [
        'title' => 'Контент',
        'items' => [
            [
                'label' => 'Статика',
                'href' => '/admin-content-static',
                'page' => 'admin-content-static',
                'icon' => 'text_snippet',
            ],
            [
                'label' => 'Товары',
                'href' => '/admin-content-products',
                'page' => 'admin-content-products',
                'icon' => 'photo_library',
            ],
            [
                'label' => 'Разделы',
                'href' => '/admin-content-sections',
                'page' => 'admin-content-sections',
                'icon' => 'category',
            ],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48x48.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icon-120x120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/maskable.svg" color="#EA5289">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-150x150.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
        rel="stylesheet">
    <script src="/assets/js/tailwindcss.js"></script>
    <?php if ($isAdminPage): ?>
        <style>
            body[data-page^="admin"] {
                --admin-bg: #0b0f16;
                --admin-surface: #111827;
                --admin-surface-strong: #161f2e;
                --admin-muted: #94a3b8;
                --admin-text: #e5e7eb;
                --admin-primary: #4f8cff;
                --admin-secondary: #6b7280;
                --admin-danger: #f87171;
                --admin-primary-soft: rgba(79, 140, 255, 0.16);
                --admin-secondary-soft: rgba(148, 163, 184, 0.14);
                --admin-danger-soft: rgba(248, 113, 113, 0.16);
                background-color: var(--admin-bg);
                color: var(--admin-text);
            }
            body[data-page^="admin"] .bg-white {
                background-color: var(--admin-surface) !important;
            }
            body[data-page^="admin"] .bg-slate-50 {
                background-color: var(--admin-surface-strong) !important;
            }
            body[data-page^="admin"] .bg-slate-900 {
                background-color: var(--admin-surface-strong) !important;
            }
            body[data-page^="admin"] .bg-emerald-50 {
                background-color: var(--admin-primary-soft) !important;
            }
            body[data-page^="admin"] .bg-rose-50 {
                background-color: var(--admin-danger-soft) !important;
            }
            body[data-page^="admin"] .bg-amber-50,
            body[data-page^="admin"] .bg-violet-50 {
                background-color: var(--admin-secondary-soft) !important;
            }
            body[data-page^="admin"] .bg-emerald-600,
            body[data-page^="admin"] .bg-emerald-700 {
                background-color: var(--admin-primary) !important;
            }
            body[data-page^="admin"] .bg-amber-500,
            body[data-page^="admin"] .bg-violet-600 {
                background-color: var(--admin-secondary) !important;
            }
            body[data-page^="admin"] .bg-rose-600,
            body[data-page^="admin"] .bg-rose-700 {
                background-color: var(--admin-danger) !important;
            }
            body[data-page^="admin"] input,
            body[data-page^="admin"] select,
            body[data-page^="admin"] textarea {
                background-color: var(--admin-surface);
                border: none;
                box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.12);
                color: var(--admin-text);
            }
            body[data-page^="admin"] input:focus,
            body[data-page^="admin"] select:focus,
            body[data-page^="admin"] textarea:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(79, 140, 255, 0.35);
            }
            body[data-page^="admin"] input::placeholder,
            body[data-page^="admin"] textarea::placeholder {
                color: var(--admin-muted);
            }
            body[data-page^="admin"] select option {
                background-color: var(--admin-surface);
                color: var(--admin-text);
            }
            body[data-page^="admin"] select:disabled,
            body[data-page^="admin"] input:disabled,
            body[data-page^="admin"] textarea:disabled {
                color: #6b7280;
                background-color: var(--admin-bg);
                box-shadow: none;
            }
            body[data-page^="admin"] .border,
            body[data-page^="admin"] [class*="border-"] {
                border-color: transparent !important;
                border-width: 0 !important;
            }
            body[data-page^="admin"] header,
            body[data-page^="admin"] aside {
                box-shadow: 0 12px 28px rgba(15, 23, 42, 0.35);
            }
            body[data-page^="admin"] .text-slate-900 {
                color: #f8fafc !important;
            }
            body[data-page^="admin"] .text-slate-800 {
                color: #e5e7eb !important;
            }
            body[data-page^="admin"] .text-slate-700 {
                color: #d1d5db !important;
            }
            body[data-page^="admin"] .text-slate-600,
            body[data-page^="admin"] .text-slate-500,
            body[data-page^="admin"] .text-slate-400 {
                color: var(--admin-muted) !important;
            }
            body[data-page^="admin"] .text-emerald-700,
            body[data-page^="admin"] .text-emerald-900 {
                color: var(--admin-primary) !important;
            }
            body[data-page^="admin"] .text-rose-700,
            body[data-page^="admin"] .text-rose-900 {
                color: var(--admin-danger) !important;
            }
            body[data-page^="admin"] .text-emerald-900,
            body[data-page^="admin"] .text-emerald-800 {
                color: var(--admin-primary) !important;
            }
            body[data-page^="admin"] .ring-slate-800,
            body[data-page^="admin"] [class*="ring-"] {
                --tw-ring-color: transparent !important;
            }
            body[data-page^="admin"] .transition {
                transition-property: color, background-color, box-shadow, filter;
                transition-duration: 200ms;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }
            body[data-page^="admin"] a,
            body[data-page^="admin"] button,
            body[data-page^="admin"] [role="button"],
            body[data-page^="admin"] input[type="submit"],
            body[data-page^="admin"] input[type="button"] {
                cursor: pointer;
            }
            body[data-page^="admin"] button:disabled,
            body[data-page^="admin"] input:disabled {
                cursor: not-allowed;
                filter: none;
            }
            body[data-page^="admin"] a:hover,
            body[data-page^="admin"] button:not(:disabled):hover,
            body[data-page^="admin"] [role="button"]:hover,
            body[data-page^="admin"] input[type="submit"]:not(:disabled):hover,
            body[data-page^="admin"] input[type="button"]:not(:disabled):hover {
                filter: brightness(1.08);
            }
            body[data-page^="admin"] .hover\:-translate-y-0\.5:hover,
            body[data-page^="admin"] .hover\:-translate-y-1:hover {
                transform: none !important;
            }
        </style>
    <?php endif; ?>
</head>
<body
    class="<?php echo htmlspecialchars($bodyClasses, ENT_QUOTES, 'UTF-8'); ?>"
    data-page="<?php echo htmlspecialchars($currentPage, ENT_QUOTES, 'UTF-8'); ?>"
>
    <?php if ($isAdminPage): ?>
        <aside class="group fixed left-0 top-0 z-40 flex h-screen w-16 flex-col border-r border-slate-800 bg-slate-950/95 shadow-2xl shadow-slate-900/40 backdrop-blur transition-[width] duration-300 hover:w-64">
            <div class="flex items-center gap-3 px-4 py-5">
                <span class="material-symbols-rounded text-2xl text-rose-400">settings_suggest</span>
                <span class="text-sm font-semibold text-white opacity-0 transition duration-300 group-hover:opacity-100">Bunch Admin</span>
            </div>
            <nav class="flex-1 space-y-4 px-2 pb-6" aria-label="Админ-навигация">
                <?php foreach ($adminNavigation as $section): ?>
                    <div class="space-y-2">
                        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.3em] text-slate-500 opacity-0 transition duration-300 group-hover:opacity-100">
                            <?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <ul class="space-y-1">
                            <?php foreach ($section['items'] as $item): ?>
                                <?php
                                $isActive = isset($item['page']) && $item['page'] !== '' && $currentPage === $item['page'];
                                $isDisabled = (bool) ($item['disabled'] ?? false);
                                $itemClasses = 'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition';
                                if ($isActive) {
                                    $itemClasses .= ' bg-slate-800 text-white shadow-lg shadow-slate-900/30';
                                } elseif ($isDisabled) {
                                    $itemClasses .= ' text-slate-600';
                                } else {
                                    $itemClasses .= ' text-slate-300 hover:bg-slate-800/70 hover:text-white';
                                }
                                ?>
                                <li>
                                    <a
                                        class="<?php echo htmlspecialchars($itemClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                        href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php if ($isDisabled): ?>aria-disabled="true"<?php endif; ?>
                                    >
                                        <span class="material-symbols-rounded text-xl"><?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="whitespace-nowrap opacity-0 transition duration-300 group-hover:opacity-100">
                                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </nav>
            <div class="px-3 pb-5">
                <a class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800/70 hover:text-white" href="/">
                    <span class="material-symbols-rounded text-xl text-rose-400">arrow_back</span>
                    <span class="whitespace-nowrap opacity-0 transition duration-300 group-hover:opacity-100">На сайт</span>
                </a>
            </div>
        </aside>
    <?php endif; ?>
    <header class="<?php echo htmlspecialchars($isAdminPage ? 'sticky top-0 z-30 border-b border-slate-800 bg-slate-950/80 backdrop-blur' : 'sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="<?php echo htmlspecialchars($isAdminPage ? 'mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3 pl-20 sm:pl-24' : 'mx-auto flex w-full max-w-6xl items-center justify-between px-3 py-2', ENT_QUOTES, 'UTF-8'); ?>">
            <div>
                <?php if ($isAdminPage): ?>
                    <a class="text-lg font-semibold tracking-tight text-white" href="/admin">
                        bunch admin
                    </a>
                <?php else: ?>
                    <a class="flex items-center gap-3 text-lg font-semibold tracking-tight text-slate-900" href="/">
                        <img
                            alt="Bunch flowers"
                            class="h-8 w-auto"
                            src="/bunchlogo.svg"
                            width="143"
                        >
                        <span class="sr-only"><?php echo htmlspecialchars($pageMeta['headerTitle'] ?? 'Bunch flowers', ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="<?php echo htmlspecialchars($isAdminPage ? 'flex items-center gap-3' : 'flex items-center gap-2', ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($isAdminPage): ?>
                    <a class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-3 py-2 text-sm font-semibold text-slate-200 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-500 hover:text-white" href="/">
                        <span class="material-symbols-rounded text-lg text-rose-400">arrow_back</span>
                        На сайт
                    </a>
                <?php else: ?>
                    <?php if ($isAdminUser): ?>
                        <a
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:text-rose-600 sm:h-auto sm:w-auto sm:justify-start sm:gap-2 sm:px-3 sm:py-2 sm:text-sm sm:font-semibold sm:text-slate-700 sm:shadow-sm sm:hover:shadow-md"
                            href="/admin"
                            aria-label="Админка"
                        >
                            <span class="material-symbols-rounded text-lg text-emerald-500">admin_panel_settings</span>
                            <span class="hidden sm:inline">Админка</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($isAuthenticated): ?>
                        <a
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:text-rose-600 sm:h-auto sm:w-auto sm:justify-start sm:gap-2 sm:rounded-xl sm:px-3 sm:py-2 sm:text-sm sm:font-semibold sm:text-slate-700 sm:shadow-sm sm:hover:shadow-md"
                            href="/account-notifications"
                            aria-label="Уведомления"
                        >
                            <span class="material-symbols-rounded text-base">notifications</span>
                            <span class="hidden sm:inline">Уведомления</span>
                        </a>
                    <?php endif; ?>
                    <button
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:text-rose-600 sm:h-auto sm:w-auto sm:justify-start sm:gap-2 sm:px-3 sm:py-2 sm:text-sm sm:font-semibold sm:text-slate-700 sm:shadow-sm sm:hover:shadow-md"
                        type="button"
                        data-support-open
                        aria-label="Поддержка"
                    >
                        <span class="material-symbols-rounded text-lg text-rose-500">support_agent</span>
                        <span class="hidden sm:inline">Поддержка</span>
                    </button>
                    <button
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:text-rose-600 sm:hidden"
                        type="button"
                        data-info-open
                        aria-label="Меню"
                    >
                        <span class="material-symbols-rounded text-xl">menu</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="<?php echo htmlspecialchars($mainClasses, ENT_QUOTES, 'UTF-8'); ?>">
        <?php
        $notice = Session::get('auth_notice');
        if ($notice) {
            Session::remove('auth_notice');
            echo '<div class="w-full max-w-3xl rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm"><div class="flex items-start gap-2"><span class="material-symbols-rounded text-base">info</span><p>' . htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') . '</p></div></div>';
        }
        ?>

        <?php echo $content; ?>
    </main>

    <?php
    $defaultInfoLinks = [
        ['title' => 'О нас', 'href' => '/about'],
        ['title' => 'Наши розы', 'href' => '/roses'],
        ['title' => 'Оплата и доставка', 'href' => '/delivery'],
        ['title' => 'Как получить скидку?', 'href' => '/discount'],
    ];
    $defaultLegalLinks = [
        ['title' => 'Политика обработки персональных данных', 'href' => '/static/policy'],
        ['title' => 'Согласие на обработку персональных данных', 'href' => '/static/consent'],
        ['title' => 'Пользовательское соглашение', 'href' => '/static/offer'],
    ];
    $staticMenuPages = $staticMenuPages ?? [];
    $staticFooterPages = $staticFooterPages ?? [];
    $menuLinks = $staticMenuPages
        ? array_map(static fn ($page) => ['title' => $page['title'], 'href' => '/static/' . urlencode($page['slug'])], $staticMenuPages)
        : $defaultInfoLinks;
    $footerColumns = [1 => [], 2 => []];
    foreach ($staticFooterPages as $page) {
        $column = (int) ($page['footer_column'] ?? 1);
        $column = in_array($column, [1, 2], true) ? $column : 1;
        $footerColumns[$column][] = [
            'title' => $page['title'],
            'href' => '/static/' . urlencode($page['slug']),
        ];
    }
    $footerLinksFirst = $footerColumns[1] ?: $defaultInfoLinks;
    $footerLinksSecond = $footerColumns[2] ?: $defaultLegalLinks;
    ?>

    <?php if (!$isAdminPage): ?>
        <footer class="hidden border-t border-slate-200 bg-white/90 backdrop-blur sm:block">
            <div class="mx-auto w-full max-w-6xl px-4 py-6 text-xs text-slate-500">
                <div class="grid gap-6 sm:grid-cols-3">
                    <div class="space-y-3">
                        <a class="text-sm font-semibold text-slate-700 transition hover:text-rose-600" href="/">2026 bunch flowers - купить розы в Красноярске</a>
                        <div class="flex flex-wrap gap-3 text-sm font-semibold">
                            <a class="text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="https://t.me/bunch_flowers" target="_blank" rel="noopener">Telegram</a>
                            <a class="text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="https://vk.com/bunchflowers" target="_blank" rel="noopener">VK</a>
                        </div>
                        <div class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-500">Адрес:</span>
                            <noindex>
                                <a class="ml-1 underline underline-offset-2 transition hover:text-rose-600" href="https://yandex.ru/maps/?text=Красноярск%2C%209%20мая%2073" target="_blank" rel="nofollow noopener">9 мая 73</a>
                            </noindex>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
                        <div class="flex flex-col gap-2 text-sm font-semibold text-slate-600">
                            <?php foreach ($footerLinksFirst as $link): ?>
                                <a class="transition hover:text-rose-600" href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Правовая информация</p>
                        <div class="flex flex-col gap-2 text-sm font-semibold text-slate-600">
                            <?php foreach ($footerLinksSecond as $link): ?>
                                <a class="transition hover:text-rose-600" href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <?php include __DIR__ . '/../partials/bottom-nav.php'; ?>
    <?php endif; ?>

    <?php
    $footerLeft = trim((string) ($pageMeta['footerLeft'] ?? ''));
    $footerRight = trim((string) ($pageMeta['footerRight'] ?? ''));
    ?>

    <?php if (!$isAdminPage && ($footerLeft !== '' || $footerRight !== '')): ?>
        <footer class="hidden border-t border-slate-200 bg-white/90 backdrop-blur sm:block">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4 text-sm text-slate-500">
                <?php if ($footerLeft !== ''): ?>
                    <span><?php echo htmlspecialchars($footerLeft, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>

                <?php if ($footerRight !== ''): ?>
                    <span class="inline-flex items-center gap-2">
                        <span class="material-symbols-rounded text-base text-emerald-500">schedule</span>
                        <?php echo htmlspecialchars($footerRight, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </footer>
    <?php endif; ?>

    <?php if (!$isAdminPage): ?>
        <div class="fixed inset-0 z-50 hidden" data-info-panel>
            <div class="absolute inset-0 bg-slate-900/40" data-info-overlay></div>
            <div class="absolute inset-y-0 right-0 flex w-full max-w-xs flex-col gap-6 overflow-y-auto bg-white px-5 py-6 shadow-2xl transition duration-300 ease-out translate-x-full" data-info-drawer>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Меню</p>
                        <h2 class="text-lg font-semibold text-slate-900">Информация</h2>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-info-close>
                        <span class="material-symbols-rounded text-base">close</span>
                    </button>
                </div>
                <div class="space-y-3">
                    <?php if ($isAdminUser): ?>
                        <a class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="/admin">
                            <span class="material-symbols-rounded text-base text-emerald-500">admin_panel_settings</span>
                            Админка
                        </a>
                    <?php endif; ?>
                    <?php if ($isAuthenticated): ?>
                        <a class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="/account-notifications">
                            <span class="material-symbols-rounded text-base">notifications</span>
                            Уведомления
                        </a>
                    <?php endif; ?>
                    <button class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" type="button" data-support-open>
                        <span class="material-symbols-rounded text-lg text-rose-500">support_agent</span>
                        Поддержка
                    </button>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 text-sm text-slate-700">
                        <a class="font-semibold text-slate-800 transition hover:text-rose-600" href="/">2026 bunch flowers - купить розы в Красноярске</a>
                        <div class="mt-2 flex flex-wrap gap-3 text-sm font-semibold">
                            <a class="text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="https://t.me/bunch_flowers" target="_blank" rel="noopener">Telegram</a>
                            <a class="text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="https://vk.com/bunchflowers" target="_blank" rel="noopener">VK</a>
                        </div>
                        <div class="mt-2 text-sm text-slate-600">
                            <span class="font-semibold text-slate-500">Адрес:</span>
                            <noindex>
                                <a class="ml-1 underline underline-offset-2 transition hover:text-rose-600" href="https://yandex.ru/maps/?text=Красноярск%2C%209%20мая%2073" target="_blank" rel="nofollow noopener">9 мая 73</a>
                            </noindex>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                        <?php foreach ($menuLinks as $link): ?>
                            <a class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 transition hover:border-rose-200 hover:text-rose-600" href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                        <a class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 transition hover:border-rose-200 hover:text-rose-600" href="/static/policy">Политика обработки персональных данных</a>
                        <a class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 transition hover:border-rose-200 hover:text-rose-600" href="/static/consent">Согласие на обработку персональных данных</a>
                        <a class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 transition hover:border-rose-200 hover:text-rose-600" href="/static/offer">Пользовательское соглашение</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isAdminPage): ?>
        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6" data-support-modal>
            <div class="w-full max-w-lg space-y-4 rounded-3xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Поддержка</p>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-support-close>
                        <span class="material-symbols-rounded text-base">close</span>
                    </button>
                </div>
                <div class="max-h-[50vh] space-y-3 overflow-y-auto rounded-2xl border border-slate-100 bg-slate-50 p-3" data-support-messages>
                    <p class="text-sm text-slate-500" data-support-empty>Пока нет сообщений. Напишите, чтобы начать диалог.</p>
                </div>
                <form class="space-y-2" data-support-form>
                    <textarea
                        class="min-h-[96px] w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-rose-300 focus:outline-none"
                        placeholder="Напишите сообщение..."
                        data-support-input
                    ></textarea>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-emerald-600" data-support-status></p>
                        <button
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700"
                            type="submit"
                        >
                            <span class="material-symbols-rounded text-base">send</span>
                            Отправить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div
        class="fixed inset-x-0 bottom-0 z-50 hidden border-t border-slate-200 bg-white px-4 py-4 text-sm text-slate-700 shadow-[0_-10px_30px_rgba(15,23,42,0.12)]"
        data-cookie-banner
    >
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <p class="text-base font-semibold text-slate-900">Cookie на сайте</p>
                <p class="text-sm text-slate-600">
                    Мы используем cookie, чтобы сайт работал корректно, а также для аналитики и маркетинга (Яндекс.Метрика, Google Analytics, пиксель VK).
                    Вы можете принять все cookie или настроить выбор. Подробнее — в
                    <a class="text-rose-600 underline underline-offset-2" href="/static/policy">Политике обработки персональных данных</a>.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-rose-200 hover:text-rose-600" data-cookie-settings-open>
                    Настроить
                </button>
                <button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-cookie-accept-all>
                    Принять все
                </button>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6" data-cookie-settings>
        <div class="w-full max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Настройки cookie</p>
                    <h2 class="text-xl font-semibold text-slate-900">Выберите категории cookie</h2>
                </div>
                <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-cookie-settings-close>
                    <span class="material-symbols-rounded text-base">close</span>
                </button>
            </div>
            <div class="space-y-3 text-sm text-slate-600">
                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                    <span>
                        <span class="font-semibold text-slate-800">Обязательные</span>
                        <span class="block text-xs text-slate-500">Работа сайта, безопасность, корзина.</span>
                    </span>
                    <input type="checkbox" checked disabled class="h-5 w-5 accent-rose-600">
                </label>
                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                    <span>
                        <span class="font-semibold text-slate-800">Аналитика</span>
                        <span class="block text-xs text-slate-500">Яндекс.Метрика, Google Analytics.</span>
                    </span>
                    <input type="checkbox" class="h-5 w-5 accent-rose-600" data-cookie-analytics>
                </label>
                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                    <span>
                        <span class="font-semibold text-slate-800">Маркетинг</span>
                        <span class="block text-xs text-slate-500">Пиксель VK.</span>
                    </span>
                    <input type="checkbox" class="h-5 w-5 accent-rose-600" data-cookie-marketing>
                </label>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-rose-200 hover:text-rose-600" data-cookie-settings-close>
                    Отменить
                </button>
                <button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-cookie-save>
                    Сохранить выбор
                </button>
            </div>
        </div>
    </div>

    <script type="module" src="/assets/js/app.js"></script>
</body>
</html>

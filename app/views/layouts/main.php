<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $pageMeta = $pageMeta ?? []; ?>
    <?php $pageTitle = $pageMeta['title'] ?? 'Bunch flowers — панель'; ?>
    <?php $pageDescription = $pageMeta['description'] ?? 'Панель управления Bunch flowers.'; ?>
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
</head>
<?php
$currentPage = $_GET['page'] ?? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: 'home';
$authPages = ['login', 'register', 'recover'];
$isAuthPage = in_array($currentPage, $authPages, true);
$mainClasses = 'mx-auto flex w-full max-w-6xl flex-1 flex-col gap-4 px-3 py-3 pb-[calc(6.5rem+env(safe-area-inset-bottom))] sm:gap-6 sm:px-4 sm:pt-8 sm:pb-[calc(6.5rem+env(safe-area-inset-bottom))]';
if ($isAuthPage) {
    $mainClasses .= ' items-center justify-center';
}
?>
<body
    class="min-h-screen bg-slate-50 text-slate-900 antialiased font-[\"Manrope\",system-ui,sans-serif] flex flex-col pb-[calc(6.5rem+env(safe-area-inset-bottom))]"
    data-page="<?php echo htmlspecialchars($currentPage, ENT_QUOTES, 'UTF-8'); ?>"
>
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-3 py-2">
            <div>
                <a class="flex items-center gap-3 text-lg font-semibold tracking-tight text-slate-900" href="/?page=home">
                    <img
                        alt="Bunch flowers"
                        class="h-8 w-auto"
                        src="/bunchlogo.svg"
                        width="143"
                    >
                    <span class="sr-only"><?php echo htmlspecialchars($pageMeta['headerTitle'] ?? 'Bunch flowers', ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            </div>
            <div class="hidden items-center gap-3 sm:flex">
                <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">notifications</span>
                    Уведомления
                </button>
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-lg text-rose-500">support_agent</span>
                    Поддержка
                </button>
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

    <footer class="border-t border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-2 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <span>© <?php echo date('Y'); ?> Bunch flowers</span>
            <div class="flex flex-wrap items-center gap-3">
                <a class="font-semibold text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="/?page=policy">Политика обработки персональных данных</a>
                <a class="font-semibold text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="/?page=consent">Согласие на обработку персональных данных</a>
                <a class="font-semibold text-slate-600 underline underline-offset-2 transition hover:text-rose-600" href="/?page=offer">Пользовательское соглашение</a>
            </div>
        </div>
    </footer>

    <?php include __DIR__ . '/../partials/bottom-nav.php'; ?>

    <?php
    $footerLeft = trim((string) ($pageMeta['footerLeft'] ?? ''));
    $footerRight = trim((string) ($pageMeta['footerRight'] ?? ''));
    ?>

    <?php if ($footerLeft !== '' || $footerRight !== ''): ?>
        <footer class="border-t border-slate-200 bg-white/90 backdrop-blur">
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
                    <a class="text-rose-600 underline underline-offset-2" href="/?page=policy">Политике обработки персональных данных</a>.
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

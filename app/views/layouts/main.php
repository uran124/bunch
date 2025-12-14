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
                <div class="flex items-center gap-2 text-lg font-semibold tracking-tight text-slate-900">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-600 shadow-sm">BF</span>
                    <span class="sr-only"><?php echo htmlspecialchars($pageMeta['headerTitle'] ?? 'Bunch flowers', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
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

    <script type="module" src="/assets/js/app.js"></script>
</body>
</html>

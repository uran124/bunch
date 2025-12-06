<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunch flowers — панель</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
        rel="stylesheet">
    <script src="/assets/js/tailwindcss.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased font-[\"Manrope\",system-ui,sans-serif]">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4">
            <div>
                <div class="flex items-center gap-2 text-lg font-semibold tracking-tight text-slate-900">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-600 shadow-sm">BF</span>
                    <span>Bunch flowers</span>
                </div>
                <p class="text-sm text-slate-500">Панель управления</p>
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

    <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 px-4 py-8">
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/../partials/bottom-nav.php'; ?>

    <footer class="border-t border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4 text-sm text-slate-500">
            <span>© <?php echo date('Y'); ?> Bunch flowers</span>
            <span class="inline-flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-emerald-500">schedule</span>
                Красноярск · Asia/Krasnoyarsk
            </span>
        </div>
    </footer>
</body>
</html>

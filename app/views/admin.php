<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Администрирование</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Панель управления', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500"><?php echo htmlspecialchars($pageMeta['description'] ?? 'Быстрый доступ к ключевым разделам сайта и операционным настройкам.', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="material-symbols-rounded text-base">auto_awesome_mosaic</span>
                Создать акцию
            </button>
            <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:shadow-md">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Добавить товар
            </button>
        </div>
    </div>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article class="xl:col-span-2 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 shadow-2xl shadow-slate-900/30 ring-1 ring-slate-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-300">Мониторинг</p>
                    <h2 class="text-2xl font-semibold text-white">Операционный обзор</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-2 text-sm font-semibold text-emerald-200 ring-1 ring-emerald-300/40">
                    <span class="material-symbols-rounded text-base">check_circle</span>
                    Стабильно
                </span>
            </div>
            <ul class="mt-6 grid gap-3 sm:grid-cols-3">
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Активных заказов</span>
                    <strong class="text-2xl font-semibold text-white">18</strong>
                </li>
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Подписок в работе</span>
                    <strong class="text-2xl font-semibold text-white">42</strong>
                </li>
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Остатков на складе</span>
                    <strong class="text-2xl font-semibold text-white">3 150 шт</strong>
                </li>
            </ul>
            <p class="mt-5 text-sm text-slate-300">Проверьте, достаточно ли остатков под утренние доставки и запланированные акции.</p>
        </article>

        <?php foreach ($sections as $section): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent transition hover:-translate-y-0.5 hover:shadow-md hover:ring-rose-100">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Раздел</p>
                    <span class="material-symbols-rounded text-base text-rose-400">chevron_right</span>
                </div>
                <h3 class="mt-2 text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <ul class="mt-4 space-y-3">
                    <?php foreach ($section['items'] as $item): ?>
                        <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-sm text-slate-500"><?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <?php
                                $href = $item['href'] ?? '#';
                                $cta = $item['cta'] ?? 'Открыть';
                                ?>
                                <a
                                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-rose-600 shadow-sm ring-1 ring-rose-100 transition hover:-translate-y-0.5 hover:bg-rose-50"
                                    href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    <span class="material-symbols-rounded text-base">open_in_new</span>
                                    <?php echo htmlspecialchars($cta, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>

        <article class="xl:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-rose-50/60 ring-1 ring-transparent transition hover:-translate-y-0.5 hover:shadow-md hover:ring-rose-100">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Быстрые действия</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900">Ручное управление</h3>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    <span class="material-symbols-rounded text-base">bolt</span>
                    Instant
                </span>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="material-symbols-rounded text-base">shopping_bag</span>
                    Создать заказ
                </button>
                <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">redeem</span>
                    Начислить бонусы
                </button>
                <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-100 bg-white px-4 py-3 text-sm font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">mail</span>
                    Запланировать рассылку
                </button>
                <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">pause_circle</span>
                    Остановить продажи
                </button>
            </div>
            <p class="mt-4 text-sm text-slate-500">Все действия фиксируются в логах администрирования. Проверьте доступы перед изменением настроек.</p>
        </article>
    </section>
</section>

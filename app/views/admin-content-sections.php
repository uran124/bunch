<?php /** @var array $sections */ ?>
<?php /** @var array $landingBlocks */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Контент · Структура сайта</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Разделы сайта', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Редактируйте меню, лендинги и ярлыки в приложении. Кликайте на блоки, чтобы поменять заголовки, ссылки и статусы.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <?php foreach ($sections as $section): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent transition hover:-translate-y-0.5 hover:shadow-md hover:ring-rose-100">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Раздел</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold <?php echo $section['status'] === 'Активно' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>">
                        <span class="material-symbols-rounded text-base">toggle_on</span>
                        <?php echo htmlspecialchars($section['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <?php foreach ($section['items'] as $item): ?>
                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="#" class="text-xs font-semibold text-rose-700 hover:underline">Изменить</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-3 text-xs text-slate-500">Обновлено: <?php echo htmlspecialchars($section['updatedAt'], ENT_QUOTES, 'UTF-8'); ?></div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Лендинги</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">Быстрые страницы</h2>
                </div>
                <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                    <span class="material-symbols-rounded text-base">add</span>
                    Новый лендинг
                </a>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <?php foreach ($landingBlocks as $landing): ?>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="space-y-1">
                                <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($landing['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-sm text-slate-500"><?php echo htmlspecialchars($landing['slug'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-xs text-slate-500">Трафик: <?php echo htmlspecialchars($landing['traffic'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold <?php echo $landing['status'] === 'Опубликован' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>">
                                <span class="material-symbols-rounded text-base">visibility</span>
                                <?php echo htmlspecialchars($landing['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <div class="mt-3 flex gap-2 text-sm font-semibold">
                            <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                                <span class="material-symbols-rounded text-base">edit</span>
                                Изменить
                            </a>
                            <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                                <span class="material-symbols-rounded text-base">analytics</span>
                                Метрики
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
            <div class="flex items-center gap-2 text-emerald-900">
                <span class="material-symbols-rounded text-base">route</span>
                <span class="font-semibold">Подсказка по навигации</span>
            </div>
            <ul class="mt-3 space-y-2">
                <li>Меню в шапке и футере должно совпадать по основным ссылкам.</li>
                <li>Держите не более 6 пунктов в верхней навигации для компактности.</li>
                <li>Для мобильного приложения используйте короткие ярлыки и добавляйте иконки.</li>
            </ul>
            <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">checklist_rtl</span>
                Проверка консистентности навигации
            </div>
        </article>
    </section>
</section>

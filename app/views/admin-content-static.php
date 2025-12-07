<?php /** @var array $pages */ ?>
<?php /** @var array $faqs */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Контент · Статичные страницы</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Статичные страницы', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Управляйте блоками контента, SEO-текстами и часто задаваемыми вопросами для клиентских страниц.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Страница</span>
            <span>Блоки</span>
            <span>SEO</span>
            <span>Обновлено</span>
            <span class="text-right">Действие</span>
        </div>
        <?php foreach ($pages as $page): ?>
            <article class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $page['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500"><?php echo htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="text-sm text-slate-700">Блоков: <?php echo (int) $page['blocks']; ?></div>
                <div class="text-sm text-slate-700"><?php echo htmlspecialchars($page['seo'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div><?php echo htmlspecialchars($page['updatedAt'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold <?php echo $page['status'] === 'Опубликовано' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>">
                        <span class="material-symbols-rounded text-base">fiber_manual_record</span>
                        <?php echo htmlspecialchars($page['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Изменить
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">FAQ</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">Блоки вопросов</h2>
                </div>
                <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                    <span class="material-symbols-rounded text-base">add</span>
                    Добавить вопрос
                </a>
            </div>
            <ul class="mt-4 divide-y divide-slate-100">
                <?php foreach ($faqs as $faq): ?>
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div class="space-y-1">
                            <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-xs text-slate-500">Обновлено: <?php echo htmlspecialchars($faq['updatedAt'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold <?php echo $faq['status'] === 'Опубликован' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>">
                            <span class="material-symbols-rounded text-base">visibility</span>
                            <?php echo htmlspecialchars($faq['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
            <div class="flex items-center gap-2 text-emerald-900">
                <span class="material-symbols-rounded text-base">tips_and_updates</span>
                <span class="font-semibold">Подсказка по публикации</span>
            </div>
            <ul class="mt-3 space-y-2">
                <li>Заполняйте H1 и мета-теги перед публикацией.</li>
                <li>Проверяйте alt у изображений — это влияет на поисковую выдачу.</li>
                <li>Держите блоки FAQ в актуальном состоянии: клиенты видят их первыми.</li>
            </ul>
            <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">history</span>
                Последняя публикация: 31.05, 18:20
            </div>
        </article>
    </section>
</section>

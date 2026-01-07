<?php
$pageMeta = $pageMeta ?? [];
$monitoring = $monitoring ?? [];
?>

<section class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Администрирование</p>
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
            <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Разовых заказов · Новый</span>
                    <strong class="text-2xl font-semibold text-white"><?php echo (int) ($monitoring['one_time_new'] ?? 0); ?></strong>
                </li>
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Активных подписок</span>
                    <strong class="text-2xl font-semibold text-white"><?php echo (int) ($monitoring['active_subscriptions'] ?? 0); ?></strong>
                </li>
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Роз до следующей поставки</span>
                    <strong class="text-2xl font-semibold text-white"><?php echo (int) ($monitoring['ordered_stems'] ?? 0); ?></strong>
                </li>
                <li class="flex flex-col gap-1 rounded-xl bg-white/5 px-4 py-3 text-slate-100 ring-1 ring-white/10">
                    <span class="text-sm text-slate-300">Текущих акций</span>
                    <strong class="text-2xl font-semibold text-white"><?php echo (int) ($monitoring['active_promos'] ?? 0); ?></strong>
                </li>
            </ul>
            <?php if (!empty($monitoring['current_supply_date']) || !empty($monitoring['next_supply_date'])): ?>
                <p class="mt-5 text-sm text-slate-300">
                    <?php if (!empty($monitoring['current_supply_date'])): ?>
                        Текущая поставка: <?php echo htmlspecialchars($monitoring['current_supply_date'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                    <?php if (!empty($monitoring['next_supply_date'])): ?>
                        · Следующая: <?php echo htmlspecialchars($monitoring['next_supply_date'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </article>

        <?php foreach ($sections as $section): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent transition hover:-translate-y-0.5 hover:shadow-md hover:ring-rose-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <span class="material-symbols-rounded text-base text-rose-400">chevron_right</span>
                </div>
                <ul class="mt-4 space-y-3">
                    <?php foreach ($section['items'] as $item): ?>
                        <?php $href = $item['href'] ?? '#'; ?>
                        <li>
                            <a
                                class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:border-rose-100 hover:bg-white hover:text-rose-700"
                                href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="material-symbols-rounded text-base text-rose-400">arrow_forward</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </section>
</section>

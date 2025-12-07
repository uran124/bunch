<?php /** @var array $subscriptions */ ?>
<?php /** @var array $periods */ ?>
<?php /** @var array $discountTiers */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Подписки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Подписки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Настройте периодичность и скидочные ступени, чтобы клиент видел «Оформить подписку -N%» и получал рост скидки на каждый следующий букет.</p>
        </div>
        <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В панель
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Настройка скидок</p>
            <h2 class="text-xl font-semibold text-slate-900">Размер скидки на подписку</h2>
            <div class="grid gap-2 md:grid-cols-2">
                <?php foreach ($discountTiers as $tier): ?>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 shadow-sm">
                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($tier['step'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-lg font-bold text-rose-700"><?php echo htmlspecialchars($tier['discount'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars($tier['comment'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span class="material-symbols-rounded mr-2 align-middle text-base">check_circle</span>
                Эти параметры отображаются клиенту в кнопке «Оформить подписку -N%» и в шаге выбора частоты.
            </p>
        </div>
        <div class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Периодичность</p>
            <h2 class="text-xl font-semibold text-slate-900">Предустановленные частоты</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($periods as $period): ?>
                    <button class="inline-flex items-center gap-2 rounded-lg border border-rose-100 bg-white px-3 py-2 text-sm font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200">
                        <span class="material-symbols-rounded text-base">repeat</span>
                        <?php echo htmlspecialchars($period, ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <label class="flex flex-col gap-2 text-sm text-slate-700">
                <span class="font-semibold text-slate-900">Дополнительная опция</span>
                <input type="text" placeholder="Например, Раз в 10 дней" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[1fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Клиент</span>
            <span>План</span>
            <span>Следующая доставка</span>
            <span>Скидка</span>
            <span class="text-right">Статус</span>
        </div>
        <?php foreach ($subscriptions as $subscription): ?>
            <article class="grid grid-cols-[1fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($subscription['customer'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Товар: <?php echo htmlspecialchars($subscription['sku'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($subscription['plan'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm text-slate-700"><?php echo htmlspecialchars($subscription['nextDelivery'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm font-semibold text-rose-700"><?php echo htmlspecialchars($subscription['discount'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="flex justify-end">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"><?php echo htmlspecialchars($subscription['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Подсказка</p>
            <h2 class="text-xl font-semibold text-slate-900">Как показывается клиенту</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Кнопка «Оформить подписку -N%» берёт значение из первого шага скидки.</li>
                <li>На втором шаге клиент выбирает периодичность из списка выше.</li>
                <li>На экране подтверждения показывается расклад: -N1% на второй букет, -N2% на третий и т.д.</li>
            </ul>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">toggle_on</span>
                Быстрое действие
            </div>
            <p>Включите автопаузу подписок, если у поставки меньше 10% остатков — клиенты увидят уведомление о переносе доставки.</p>
            <div class="flex flex-wrap gap-2">
                <a href="/?page=admin-supplies" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">inventory</span>
                    Проверить поставки
                </a>
                <a href="/?page=admin-orders-one-time" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">shopping_bag</span>
                    Разовые заказы
                </a>
            </div>
        </div>
    </section>
</section>

<?php /** @var array $orders */ ?>
<?php /** @var array $filters */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Разовые покупки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Разовые заказы', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Отслеживайте статусы, оплаты и доставку разовых заказов. Фильтры помогают быстро найти проблемные доставки.</p>
        </div>
        <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В панель
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.4fr_1fr_1fr]">
        <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Поиск по клиенту или номеру</span>
            <input type="search" placeholder="например, B-3012 или Соколова" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
        </label>
        <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Статус доставки</span>
            <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                <?php foreach ($filters['status'] as $option): ?>
                    <option><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Оплата</span>
            <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                <?php foreach ($filters['payment'] as $option): ?>
                    <option><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[120px_1.1fr_1fr_1fr_1fr_150px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Номер</span>
            <span>Клиент</span>
            <span>Сумма</span>
            <span>Статус</span>
            <span>Доставка</span>
            <span class="text-right">Действия</span>
        </div>
        <?php foreach ($orders as $order): ?>
            <article class="grid grid-cols-[120px_1.1fr_1fr_1fr_1fr_150px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['customer'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Канал: <?php echo htmlspecialchars($order['channel'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo htmlspecialchars($order['sum'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"><?php echo htmlspecialchars($order['payment'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-rose-700"><?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Обновлено 5 мин назад</div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['delivery'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Маршрут: 1 курьер</div>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">fact_check</span>
                        Детали
                    </a>
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Изменить
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Контроль SLA</p>
            <h2 class="text-xl font-semibold text-slate-900">Что проверить, если заказ завис</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Убедитесь, что оплата прошла, прежде чем отправлять курьера.</li>
                <li>Проверьте, есть ли в поставке нужные позиции и атрибуты упаковки.</li>
                <li>Сообщите клиенту о переносе, если доставка не успевает в обещанный слот.</li>
            </ul>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <div class="flex items-center gap-2 font-semibold text-amber-900">
                <span class="material-symbols-rounded text-base">notifications_active</span>
                Быстрое действие
            </div>
            <p>Настройте push-уведомления для заказов со статусом «Ожидает оплаты» — курьер получит сигнал только после подтверждения платежа.</p>
            <div class="flex flex-wrap gap-2">
                <a href="/?page=admin-broadcast" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-amber-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">send</span>
                    Настроить уведомления
                </a>
                <a href="/?page=admin-orders-subscriptions" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">repeat</span>
                    Посмотреть подписки
                </a>
            </div>
        </div>
    </section>
</section>

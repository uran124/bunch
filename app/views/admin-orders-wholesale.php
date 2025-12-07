<?php /** @var array $wholesaleOrders */ ?>
<?php /** @var array $limits */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Мелкий опт</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Мелкий опт', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Следите за резервами и оплатами по группам заказов. Лимиты помогают не выйти за остатки поставки.</p>
        </div>
        <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В панель
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Доступно пачек</p>
                <div class="text-2xl font-bold text-slate-900"><?php echo (int) $limits['packsAvailable']; ?></div>
                <p class="text-xs text-slate-500">По текущим поставкам</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">В резерве</p>
                <div class="text-2xl font-bold text-rose-700"><?php echo (int) $limits['reserved']; ?></div>
                <p class="text-xs text-slate-500">По подтверждённым броням</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Счётов к оплате</p>
                <div class="text-2xl font-bold text-amber-700"><?php echo (int) $limits['pendingInvoices']; ?></div>
                <p class="text-xs text-slate-500">Ожидают закрытия</p>
            </div>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">lock_clock</span>
                Автоблок запасов
            </div>
            <p>При достижении лимита резерва курьеры и менеджеры увидят предупреждение о дефиците. Снимите блокировку вручную, если есть резервный склад.</p>
            <div class="flex flex-wrap gap-2">
                <a href="/?page=admin-supplies" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">warehouse</span>
                    Проверить поставки
                </a>
                <a href="/?page=admin-orders-subscriptions" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">repeat</span>
                    Подписки
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[1fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Клиент</span>
            <span>Поставки</span>
            <span>Пачки</span>
            <span>Сумма</span>
            <span class="text-right">Статус</span>
        </div>
        <?php foreach ($wholesaleOrders as $order): ?>
            <article class="grid grid-cols-[1fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['client'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Дата: <?php echo htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="text-sm text-slate-700"><?php echo htmlspecialchars($order['supply'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm font-semibold text-slate-900"><?php echo (int) $order['packs']; ?> пачек</div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo htmlspecialchars($order['sum'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="flex justify-end">
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200"><?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Памятка менеджеру</p>
            <h2 class="text-xl font-semibold text-slate-900">Как фиксировать опт без ошибок</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Создавайте бронь только под конкретную поставку, чтобы не исчерпать остатки.</li>
                <li>Сверяйте статусы оплат: «Ожидает», «Подтверждено», «Отгружено».</li>
                <li>Отмечайте источники обращений (менеджер, сайт, корпоративный клиент) — это помогает прогнозировать спрос.</li>
            </ul>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div class="flex items-center gap-2 font-semibold text-rose-900">
                <span class="material-symbols-rounded text-base">description</span>
                Шаблон счёта
            </div>
            <p>Скачайте шаблон договора и счета, чтобы отправить клиенту без правок. В нём уже есть поля для количества пачек и даты поставки.</p>
            <div class="flex flex-wrap gap-2">
                <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">download</span>
                    Скачать шаблон
                </a>
                <a href="/?page=admin-orders-one-time" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">shopping_bag</span>
                    Разовые заказы
                </a>
            </div>
        </div>
    </section>
</section>

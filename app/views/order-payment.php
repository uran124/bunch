<?php
/** @var array $order */
/** @var array $gateway */
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Оплата</p>
            <h1 class="text-3xl font-semibold text-slate-900">Заказ <?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Оплата проходит через активный шлюз <?php echo htmlspecialchars($gateway['name'], ENT_QUOTES, 'UTF-8'); ?>.</p>
        </div>
        <a
            href="/orders"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <span class="material-symbols-rounded text-base">arrow_back</span>
            К заказам
        </a>
    </header>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Сумма к оплате</p>
                        <h2 class="text-2xl font-semibold text-slate-900"><?php echo htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="material-symbols-rounded text-base">task_alt</span>
                        Статус: <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="mt-4 grid gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Получение</span>
                        <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['deliveryType'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Окно доставки</span>
                        <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['scheduled'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Адрес</span>
                        <span class="text-right font-semibold text-slate-900"><?php echo htmlspecialchars($order['address'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </div>

            <form method="post" action="/order-payment" class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-600">Платёжный шлюз</p>
                        <p class="text-lg font-semibold text-emerald-900"><?php echo htmlspecialchars($gateway['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="<?php echo htmlspecialchars($gateway['link'], ENT_QUOTES, 'UTF-8'); ?>" class="text-xs font-semibold text-emerald-700 underline underline-offset-2">
                            Настройки шлюза
                        </a>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <span class="material-symbols-rounded text-base">payments</span>
                        Оплатить
                    </button>
                </div>
                <p class="mt-3 text-xs text-emerald-700">После подтверждения оплаты заказ автоматически перейдёт в работу.</p>
            </form>
        </div>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Позиции</p>
                <div class="mt-4 grid gap-3">
                    <?php foreach ($order['items'] as $item): ?>
                        <article class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <div class="h-12 w-12 overflow-hidden rounded-xl bg-white">
                                <img
                                    src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?> ×<?php echo (int) $item['qty']; ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 shadow-sm">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-base">lock</span>
                    <p>Оплата защищена и проходит через выбранный шлюз. Сохраните чек после оплаты.</p>
                </div>
            </div>
        </aside>
    </div>
</section>

<?php
/** @var array $activeSubscriptions */
/** @var array $activeOrders */
/** @var array $completedOrders */
/** @var int $historyLimit */
/** @var bool $historyHasMore */
?>

<section class="grid gap-3 sm:gap-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1"><h1>
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.08em] text-rose-600">
                <span class="material-symbols-rounded text-base">history</span>
                 Заказы и подписки 
            </p></h1>

        </div>

    </header>

    <?php if (!empty($activeSubscriptions) || !empty($activeOrders)): ?>
        <div class="sticky top-2 z-10 space-y-3">
            <?php if (!empty($activeSubscriptions)): ?>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-emerald-700">Активные подписки</p>
                            <h3 class="text-base font-semibold text-slate-900 sm:text-lg">Ближайшие списания</h3>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-100">
                            <span class="material-symbols-rounded text-base">push_pin</span>
                            Закреплено
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3">
                        <?php foreach ($activeSubscriptions as $subscription): ?>
                            <article class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-inner shadow-emerald-100 sm:p-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                    <span class="material-symbols-rounded text-xl">event_repeat</span>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-900">
                                        <span><?php echo htmlspecialchars($subscription['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">×<?php echo (int) $subscription['qty']; ?></span>
                                    </div>
                                    <p class="text-xs text-slate-600">Следующая доставка: <?php echo htmlspecialchars($subscription['nextDelivery'] ?: 'уточняется', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs font-semibold text-emerald-700"><?php echo htmlspecialchars($subscription['plan'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="text-right text-sm font-semibold text-slate-900 sm:text-base"><?php echo htmlspecialchars($subscription['total'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($activeOrders)): ?>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-3 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-amber-700">Активные заказы</p>
                            <h3 class="text-base font-semibold text-slate-900 sm:text-lg">В работе</h3>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 shadow-sm ring-1 ring-amber-100">
                            <span class="material-symbols-rounded text-base">push_pin</span>
                            Закреплено
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3">
                            <?php foreach ($activeOrders as $order): ?>
                                <?php
                                $badgeClasses = match ($order['status']) {
                                    'new' => 'bg-rose-50 text-rose-700 ring-rose-100',
                                    'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                    'assembled' => 'bg-sky-50 text-sky-700 ring-sky-100',
                                    'delivering' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                    'delivered' => 'bg-white text-slate-700 ring-slate-200',
                                    'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-200',
                                    default => 'bg-slate-50 text-slate-700 ring-slate-100',
                                };
                                ?>
                            <article class="flex items-start gap-3 rounded-2xl bg-white p-3 shadow-inner shadow-amber-100 sm:p-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 sm:h-12 sm:w-12">
                                    <span class="material-symbols-rounded text-xl">local_shipping</span>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-900">
                                            <span><?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="text-slate-500">· <?php echo htmlspecialchars($order['createdAt'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?php echo $badgeClasses; ?>">
                                            <span class="material-symbols-rounded text-base">radio_button_checked</span>
                                            <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($order['item'])): ?>
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($order['item']['title'], ENT_QUOTES, 'UTF-8'); ?> ×<?php echo (int) $order['item']['qty']; ?> · <?php echo htmlspecialchars($order['item']['unit'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-700 sm:text-sm">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="material-symbols-rounded text-base text-amber-600"><?php echo $order['deliveryType'] === 'Доставка' ? 'local_shipping' : 'storefront'; ?></span>
                                            <span><?php echo htmlspecialchars($order['deliveryType'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php if (!empty($order['scheduled'])): ?>
                                                <span>· <?php echo htmlspecialchars($order['scheduled'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-sm font-semibold text-slate-900 sm:text-base"><?php echo htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a
                                        href="<?php echo htmlspecialchars($order['editLink'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md <?php echo $order['canEdit'] ? '' : 'pointer-events-none opacity-50'; ?>"
                                        <?php echo $order['canEdit'] ? '' : 'aria-disabled="true"'; ?>
                                    >
                                        <span class="material-symbols-rounded text-base">edit</span>
                                        Изменить
                                    </a>
                                    <a
                                        href="<?php echo htmlspecialchars($order['paymentLink'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-600 px-3 py-1 text-xs font-semibold text-white shadow-sm shadow-amber-200 transition hover:-translate-y-0.5 hover:shadow-md <?php echo $order['canPay'] ? '' : 'pointer-events-none bg-slate-200 text-slate-500 shadow-none opacity-70'; ?>"
                                        <?php echo $order['canPay'] ? '' : 'aria-disabled="true"'; ?>
                                    >
                                        <span class="material-symbols-rounded text-base">payments</span>
                                        Оплатить
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="rounded-3xl border border-slate-200 bg-white p-3 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Выполненные заказы</p>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">История покупок</h3>
            </div>

        </div>

        <div
            class="mt-4 grid gap-3"
            data-orders-history
            data-limit="<?php echo (int) $historyLimit; ?>"
            data-has-more="<?php echo $historyHasMore ? 'true' : 'false'; ?>"
            data-endpoint="/orders-history"
        >
            <div data-history-list class="grid gap-3">
                <?php if (empty($completedOrders)): ?>
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-3 text-xs text-slate-600 sm:p-4 sm:text-sm">
                        Выполненных заказов пока нет.
                    </div>
                <?php else: ?>
                        <?php foreach ($completedOrders as $order): ?>
                            <?php
                            $badgeClasses = match ($order['status']) {
                                'new' => 'bg-rose-50 text-rose-700 ring-rose-100',
                                'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                'assembled' => 'bg-sky-50 text-sky-700 ring-sky-100',
                                'delivering' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'delivered' => 'bg-white text-slate-700 ring-slate-200',
                                'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-200',
                                default => 'bg-slate-50 text-slate-700 ring-slate-100',
                            };
                            ?>
                            <article class="flex gap-3 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm ring-1 ring-slate-100 sm:gap-4 sm:p-4" data-order-card>
                                <div class="h-14 w-18 overflow-hidden rounded-xl bg-slate-100 sm:h-16 sm:w-20">
                                    <img
                                        src="<?php echo htmlspecialchars($order['item']['image'] ?? '/assets/images/products/bouquet.svg', ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($order['item']['title'] ?? 'Товар', ENT_QUOTES, 'UTF-8'); ?>"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold text-slate-900 sm:text-sm">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span><?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="text-slate-500">· <?php echo htmlspecialchars($order['createdAt'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?php echo $badgeClasses; ?>">
                                        <span class="material-symbols-rounded text-base">verified</span>
                                        <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <?php if (!empty($order['item'])): ?>
                                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($order['item']['title'], ENT_QUOTES, 'UTF-8'); ?> ×<?php echo (int) $order['item']['qty']; ?></p>
                                    <p class="text-[11px] text-slate-600 sm:text-xs"><?php echo htmlspecialchars($order['item']['price'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-700 sm:text-sm">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-rounded text-base text-slate-500"><?php echo $order['deliveryType'] === 'Доставка' ? 'local_shipping' : 'storefront'; ?></span>
                                        <span><?php echo htmlspecialchars($order['deliveryType'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($order['scheduled'])): ?>
                                            <span>· <?php echo htmlspecialchars($order['scheduled'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-900 sm:text-base"><?php echo htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="flex items-center justify-center gap-2 pt-2 text-sm text-slate-500" data-history-loader>
                <span class="material-symbols-rounded animate-spin text-base">progress_activity</span>
                <span>Загружаем еще заказы…</span>
            </div>
            <div data-history-sentinel class="h-1"></div>
        </div>
    </div>
</section>

<section class="grid gap-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.08em] text-rose-600">
                <span class="material-symbols-rounded text-base">account_circle</span>
                Аккаунт
            </p>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Личный кабинет</h1>
            <p class="text-sm text-slate-600">Личные данные, адреса, активные заказы и подписки.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-emerald-700">
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 ring-1 ring-emerald-100">
                <span class="material-symbols-rounded text-base">verified</span>
                Аккаунт активен
            </span>
            <?php if (!empty($cartShortcut)): ?>
                <a
                    href="/?page=cart"
                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-amber-700 ring-1 ring-amber-100 hover:bg-amber-100"
                >
                    <span class="material-symbols-rounded text-base">shopping_bag</span>
                    В корзине: <?php echo htmlspecialchars($cartShortcut['title'], ENT_QUOTES, 'UTF-8'); ?><?php echo $cartShortcut['qty'] > 1 ? ' ×' . (int) $cartShortcut['qty'] : ''; ?>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Профиль</p>
                        <h2 class="text-2xl font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="grid gap-2 text-sm text-slate-700">
                            <div class="inline-flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 font-semibold text-slate-800">
                                <span class="material-symbols-rounded text-base text-rose-500">call</span>
                                <span><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 font-semibold text-slate-800">
                                <span class="material-symbols-rounded text-base text-rose-500">mail</span>
                                <?php if (!empty($user['email'])): ?>
                                    <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php else: ?>
                                    <a href="#" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700">
                                        <span>+ e-mail</span>
                                        <span class="material-symbols-rounded text-base">add</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <button
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700"
                        type="button"
                    >
                        <span class="material-symbols-rounded text-base">edit</span>
                        Изменить
                    </button>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Адреса доставки</p>
                        <h3 class="text-lg font-semibold text-slate-900">Управление адресами</h3>
                    </div>
                    <button
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700"
                        type="button"
                    >
                        <span class="material-symbols-rounded text-base">add</span>
                        Новый адрес
                    </button>
                </div>

                <div class="mt-4 grid gap-3">
                    <?php foreach ($addresses as $address): ?>
                        <?php
                        $recipientName = $address['raw']['recipient_name'] ?? '';
                        $recipientPhone = $address['raw']['recipient_phone'] ?? '';
                        if ($recipientName === '') {
                            $recipientName = $user['name'] ?? '';
                        }
                        if ($recipientPhone === '') {
                            $recipientPhone = $user['phone'] ?? '';
                        }
                        $recipientLine = trim($recipientName . ($recipientPhone ? ' · ' . $recipientPhone : ''));
                        ?>
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900">
                                    <?php echo htmlspecialchars($address['address'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p class="text-sm text-slate-600">
                                    <?php echo htmlspecialchars($recipientLine ?: 'Получатель не указан', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <?php if (!empty($address['is_primary'])): ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">
                                        <span class="material-symbols-rounded text-xs">star</span>
                                        Основной
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($address['is_primary'])): ?>
                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:text-rose-600" type="button">
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </button>
                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200" type="button" disabled>
                                        <span class="material-symbols-rounded text-base">star</span>
                                    </button>
                                <?php else: ?>
                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-rose-600 shadow-sm ring-1 ring-rose-100 transition hover:bg-rose-50" type="button">
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-white shadow-sm transition hover:bg-slate-800" type="button">
                                        <span class="material-symbols-rounded text-base">star</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($activeOrder)): ?>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-amber-700">Активный заказ</p>
                            <h3 class="text-lg font-semibold text-slate-900">Ближайшая доставка</h3>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 shadow-sm ring-1 ring-amber-100">
                                <span class="material-symbols-rounded text-base">schedule</span>
                                <?php echo htmlspecialchars($activeOrder['datetime'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <a href="<?php echo htmlspecialchars($ordersLink, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1 rounded-full bg-amber-600 px-3 py-1 text-xs font-semibold text-white shadow-sm ring-1 ring-amber-500/70 transition hover:-translate-y-0.5 hover:shadow-md">
                                <span class="material-symbols-rounded text-base">receipt_long</span>
                                Все заказы
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-inner shadow-amber-100">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                            <span>Заказ <?php echo htmlspecialchars($activeOrder['number'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">
                                <?php echo htmlspecialchars($activeOrder['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-20 w-24 overflow-hidden rounded-2xl border border-slate-100 bg-slate-100">
                                <img
                                    src="<?php echo htmlspecialchars($activeOrder['item']['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($activeOrder['item']['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                            <div class="flex flex-1 flex-col justify-between gap-1 text-sm text-slate-700">
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($activeOrder['item']['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-slate-600">×<?php echo (int) $activeOrder['item']['qty']; ?> · <?php echo htmlspecialchars($activeOrder['item']['unitPrice'] ?? $activeOrder['item']['price'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="font-semibold text-slate-900">Сумма: <?php echo htmlspecialchars($activeOrder['total'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-700">
                            <span class="material-symbols-rounded text-base text-amber-700">
                                <?php echo $activeOrder['delivery_type'] === 'pickup' ? 'storefront' : 'local_shipping'; ?>
                            </span>
                            <?php if ($activeOrder['delivery_type'] === 'pickup'): ?>
                                <span>Самовывоз</span>
                            <?php else: ?>
                                <span>Доставка: <?php echo htmlspecialchars($activeOrder['delivery_address'] ?? 'Адрес уточняется', ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($activeOrders)): ?>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Активные заказы</p>
                            <h3 class="text-lg font-semibold text-slate-900">В обработке: <?php echo count($activeOrders); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars($ordersLink, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="material-symbols-rounded text-base">receipt_long</span>
                            История
                        </a>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <?php foreach ($activeOrders as $order): ?>
                            <article class="flex flex-col gap-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-3">
                                <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
                                    <span><?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-2 py-0.5 text-xs font-semibold text-white">
                                        <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($order['datetime'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($order['item']['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-600 flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm text-rose-500">
                                        <?php echo $order['delivery_type'] === 'pickup' ? 'storefront' : 'local_shipping'; ?>
                                    </span>
                                    <?php echo htmlspecialchars($order['delivery_type'] === 'pickup' ? 'Самовывоз' : ($order['delivery_address'] ?? 'Доставка'), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p class="text-sm font-semibold text-slate-900">Сумма: <?php echo htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($activeSubscription)): ?>
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-emerald-700">Подписка</p>
                            <h3 class="text-lg font-semibold text-slate-900">Активная подписка</h3>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-100">
                            <span class="material-symbols-rounded text-base">event_repeat</span>
                            <?php echo htmlspecialchars($activeSubscription['frequency'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <div class="mt-4 grid gap-2 rounded-2xl bg-white p-4 shadow-inner shadow-emerald-100">
                        <p class="flex items-center justify-between text-sm text-slate-700">
                            <span class="font-semibold text-slate-900">Товар</span>
                            <span><?php echo htmlspecialchars($activeSubscription['item'], ENT_QUOTES, 'UTF-8'); ?> ×<?php echo (int) $activeSubscription['qty']; ?></span>
                        </p>
                        <p class="flex items-center justify-between text-sm text-slate-700">
                            <span class="font-semibold text-slate-900">Скидка</span>
                            <span><?php echo htmlspecialchars($activeSubscription['discount'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                        <p class="flex items-center justify-between text-sm text-slate-700">
                            <span class="font-semibold text-slate-900">Стоимость заказа</span>
                            <span class="text-lg font-semibold text-emerald-700"><?php echo htmlspecialchars($activeSubscription['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($activeSubscriptions)): ?>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Подписки</p>
                            <h3 class="text-lg font-semibold text-slate-900">Активных: <?php echo count($activeSubscriptions); ?></h3>
                        </div>
                        <a href="/?page=subscription" class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="material-symbols-rounded text-base">event_repeat</span>
                            Управлять
                        </a>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <?php foreach ($activeSubscriptions as $subscription): ?>
                            <article class="flex flex-col gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-3">
                                <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
                                    <span><?php echo htmlspecialchars($subscription['frequency'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Следующая: <?php echo htmlspecialchars($subscription['nextDelivery'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($subscription['item'], ENT_QUOTES, 'UTF-8'); ?> ×<?php echo (int) $subscription['qty']; ?></p>
                                <p class="text-sm font-semibold text-emerald-700"><?php echo htmlspecialchars($subscription['total'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Быстрые действия</p>
                        <h3 class="text-lg font-semibold text-slate-900">Текущее состояние</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                        <span class="material-symbols-rounded text-base">rocket_launch</span>
                        Быстро
                    </span>
                </div>
                <div class="mt-4 space-y-3 text-sm text-slate-800">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-base text-emerald-500">check_circle</span>
                            <span>Активных заказов</span>
                        </div>
                        <span class="text-base font-semibold text-slate-900"><?php echo count($activeOrders); ?></span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-base text-emerald-500">calendar_month</span>
                            <span>Активные подписки</span>
                        </div>
                        <span class="text-base font-semibold text-slate-900"><?php echo count($activeSubscriptions); ?></span>
                    </div>
                    <a href="<?php echo htmlspecialchars($ordersLink, ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center justify-between rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <span class="inline-flex items-center gap-2">
                            <span class="material-symbols-rounded text-base">list_alt</span>
                            Все заказы
                        </span>
                        <span class="material-symbols-rounded text-base">arrow_forward</span>
                    </a>
                    <?php if (!empty($cartShortcut)): ?>
                        <a href="/?page=cart" class="flex items-center justify-between rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 ring-1 ring-amber-100 transition hover:-translate-y-0.5 hover:shadow-sm">
                            <span class="inline-flex items-center gap-2">
                                <span class="material-symbols-rounded text-base">shopping_cart</span>
                                В корзине: <?php echo htmlspecialchars($cartShortcut['title'], ENT_QUOTES, 'UTF-8'); ?><?php echo $cartShortcut['count'] > 1 ? ' +' . ((int) $cartShortcut['count'] - 1) . ' товар' : ''; ?>
                            </span>
                            <span class="material-symbols-rounded text-base">open_in_new</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Уведомления</p>
                        <h3 class="text-lg font-semibold text-slate-900">Настройка уведомлений</h3>
                        <p class="text-sm text-slate-600">Выберите, какие события отслеживать. Настройки учитываются при рассылке из админпанели.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                        <span class="material-symbols-rounded text-base">tune</span>
                        Управление
                    </span>
                </div>

                <div class="mt-4 space-y-4" id="birthday-reminders">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Напоминать заранее</p>
                                <p class="text-xs text-slate-500">Выберите, за сколько дней предупредить.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <?php foreach ($birthdayReminderDays as $day): ?>
                                    <label class="relative">
                                        <input
                                            type="radio"
                                            name="birthday-reminder-days"
                                            value="<?php echo (int) $day; ?>"
                                            class="peer sr-only"
                                            <?php echo $day === $birthdayReminderLeadDays ? 'checked' : ''; ?>
                                        >
                                        <span class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition peer-checked:border-emerald-200 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                            <?php echo (int) $day; ?> дн.
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Напоминания о днях рождения</p>
                                <p class="text-xs text-slate-500">Открывайте карточку, чтобы изменить данные получателя.</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                <span class="material-symbols-rounded text-sm">celebration</span>
                                <?php echo count($birthdayReminders); ?>
                            </span>
                        </div>
                        <div class="mt-3 grid gap-2">
                            <div class="grid grid-cols-3 gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                                <span>Получатель</span>
                                <span>Повод</span>
                                <span>Дата</span>
                            </div>
                            <?php if (!empty($birthdayReminders)): ?>
                                <?php foreach ($birthdayReminders as $reminder): ?>
                                    <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                                        <?php
                                        $dataAttrs = sprintf(
                                            'data-birthday-reminder-edit data-birthday-reminder-id="%s" data-birthday-reminder-recipient="%s" data-birthday-reminder-occasion="%s" data-birthday-reminder-date="%s"',
                                            htmlspecialchars((string) $reminder['id'], ENT_QUOTES, 'UTF-8'),
                                            htmlspecialchars($reminder['recipient'], ENT_QUOTES, 'UTF-8'),
                                            htmlspecialchars($reminder['occasion'], ENT_QUOTES, 'UTF-8'),
                                            htmlspecialchars($reminder['date_raw'], ENT_QUOTES, 'UTF-8')
                                        );
                                        ?>
                                        <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                            <?php echo htmlspecialchars($reminder['recipient'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                        <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                            <?php echo htmlspecialchars($reminder['occasion'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                        <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                            <?php echo htmlspecialchars($reminder['date'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-500">Пока нет добавленных получателей.</p>
                            <?php endif; ?>
                        </div>
                        <p class="mt-2 hidden text-xs font-semibold text-emerald-700" data-birthday-reminder-status></p>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($notificationOptions as $option): ?>
                            <?php
                            $code = $option['code'];
                            $enabled = !empty($notificationSettings[$code]);
                            $locked = !empty($option['locked']);
                            $link = $option['link'] ?? null;
                            ?>
                            <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-rounded mt-0.5 text-base text-rose-500">notifications</span>
                                    <div class="space-y-1">
                                        <?php if ($link): ?>
                                            <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" class="text-sm font-semibold text-rose-700 underline decoration-rose-200 decoration-2 underline-offset-4 hover:text-rose-800">
                                                <?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($option['description'] ?? 'Будет учитываться при массовой отправке через админпанель.', ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php if ($locked): ?>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-emerald-600">Всегда активно</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        data-notification-toggle="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo $enabled ? 'checked' : ''; ?>
                                        <?php echo $locked ? 'disabled' : ''; ?>
                                    >
                                    <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 peer-disabled:bg-slate-100"></span>
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5 peer-disabled:opacity-70"></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <p class="text-xs text-slate-500">Все уведомления включены по умолчанию; изменения сохраняются сразу.</p>
                        <p class="hidden text-xs font-semibold text-emerald-700" data-notification-status>Настройки обновлены.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Безопасность</p>
                        <h3 class="text-lg font-semibold text-slate-900">Управление входом</h3>
                        <p class="text-sm text-slate-600">Следите за активностью аккаунта и обновляйте PIN по необходимости.</p>
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" type="button" data-open-pin-modal>
                        <span class="material-symbols-rounded text-base">lock_reset</span>
                        Сменить PIN
                    </button>
                </div>
                <div class="mt-4 grid gap-2 text-sm text-slate-700">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="inline-flex items-center gap-2 font-semibold text-slate-800">
                            <span class="material-symbols-rounded text-base text-emerald-500">check</span>
                            Последний вход: <?php echo htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <a class="text-rose-600 hover:text-rose-700" href="/?page=logout">Выйти</a>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="material-symbols-rounded text-base">info</span>
                        <span>Изменение настроек вступает в силу мгновенно и используется при сегментации уведомлений.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-pin-modal>
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Безопасность</p>
                    <h3 class="text-xl font-semibold text-slate-900">Новый PIN</h3>
                    <p class="text-sm text-slate-600">Введите 4 цифры и подтвердите. Сохранение произойдет автоматически.</p>
                </div>
                <button type="button" class="rounded-full p-1 text-slate-500 hover:bg-slate-100" data-close-pin-modal>
                    <span class="material-symbols-rounded text-xl">close</span>
                </button>
            </div>
            <div class="mt-5 space-y-4" data-pin-form>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Новый PIN</p>
                    <div class="grid grid-cols-4 gap-2">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 text-center text-xl font-semibold tracking-widest text-slate-900 focus:border-rose-300 focus:bg-white focus:outline-none" data-pin-input data-pin-group="new" data-pin-index="<?php echo $i; ?>">
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Подтвердите PIN</p>
                    <div class="grid grid-cols-4 gap-2">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 text-center text-xl font-semibold tracking-widest text-slate-900 focus:border-rose-300 focus:bg-white focus:outline-none" data-pin-input data-pin-group="confirm" data-pin-index="<?php echo $i; ?>">
                        <?php endfor; ?>
                    </div>
                </div>
                <p class="hidden text-sm font-semibold text-emerald-700" data-pin-success>PIN обновлен.</p>
                <p class="hidden text-sm font-semibold text-rose-700" data-pin-error>Не удалось сохранить PIN.</p>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-birthday-reminder-modal>
        <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Уведомления</p>
                    <h3 class="text-xl font-semibold text-slate-900">Редактировать напоминание</h3>
                    <p class="text-sm text-slate-600">Обновите данные получателя или повод.</p>
                </div>
                <button type="button" class="rounded-full p-1 text-slate-500 hover:bg-slate-100" data-birthday-reminder-close>
                    <span class="material-symbols-rounded text-xl">close</span>
                </button>
            </div>
            <form class="mt-5 space-y-4" data-birthday-reminder-form>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-recipient">Получатель</label>
                    <input id="birthday-recipient" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="recipient">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-occasion">Повод</label>
                    <input id="birthday-occasion" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="occasion">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-date">Дата</label>
                    <input id="birthday-date" type="date" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="date">
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100" data-birthday-reminder-delete>
                        <span class="material-symbols-rounded text-base">delete</span>
                        Удалить
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-500" data-birthday-reminder-save>
                        <span class="material-symbols-rounded text-base">save</span>
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

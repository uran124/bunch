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
        <button
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:shadow-md"
            type="button"
        >
            <span class="material-symbols-rounded text-base text-emerald-500">check_circle</span>
            Данные обновлены
        </button>
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
                        <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                        <?php echo htmlspecialchars(mb_substr($address['label'], 0, 1), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($address['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($address['is_primary'])): ?>
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">Основной</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-slate-600">
                                    <?php echo htmlspecialchars($address['address'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-1 text-sm font-semibold text-slate-700">
                                <?php if (!empty($address['is_primary'])): ?>
                                    <button class="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200" type="button">
                                        <span class="material-symbols-rounded text-base">edit</span>
                                        Изменить
                                    </button>
                                <?php else: ?>
                                    <button class="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-rose-600 shadow-sm ring-1 ring-rose-100" type="button">
                                        <span class="material-symbols-rounded text-base">delete</span>
                                        Удалить
                                    </button>
                                    <button class="inline-flex items-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-white shadow-sm" type="button">
                                        <span class="material-symbols-rounded text-base">star</span>
                                        Сделать основным
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($activeOrder)): ?>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-amber-700">Активный заказ</p>
                            <h3 class="text-lg font-semibold text-slate-900">Ближайшая доставка</h3>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 shadow-sm ring-1 ring-amber-100">
                            <span class="material-symbols-rounded text-base">schedule</span>
                            <?php echo htmlspecialchars($activeOrder['datetime'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
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
        </div>

        <div class="space-y-5">
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

                <div class="mt-4 space-y-3">
                    <?php
                    $notificationLabels = [
                        'order_updates' => 'Уведомления о моих заказах',
                        'bonus_updates' => 'Уведомление о начислении бонусных баллов',
                        'promo_updates' => 'Уведомления об акционных товарах',
                        'holiday_reminders' => 'Напоминания о заказах к праздникам',
                        'system_updates' => 'Системные уведомления',
                    ];
                    ?>
                    <?php foreach ($notificationLabels as $key => $label): ?>
                        <label class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-rounded mt-0.5 text-base text-rose-500">notifications</span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-slate-500">Будет учитываться при массовой отправке через админпанель.</p>
                                </div>
                            </div>
                            <span class="relative inline-flex h-7 w-12 items-center rounded-full transition <?php echo !empty($notificationSettings[$key]) ? 'bg-emerald-500' : 'bg-slate-300'; ?>">
                                <span class="absolute inset-1 rounded-full bg-white shadow-sm transition <?php echo !empty($notificationSettings[$key]) ? 'translate-x-5' : 'translate-x-0'; ?>"></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Безопасность</p>
                        <h3 class="text-lg font-semibold text-slate-900">Управление входом</h3>
                        <p class="text-sm text-slate-600">Следите за активностью аккаунта и обновляйте PIN по необходимости.</p>
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" type="button">
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
                        <a class="text-rose-600 hover:text-rose-700" href="/?page=login">Выйти</a>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="material-symbols-rounded text-base">info</span>
                        <span>Изменение настроек вступает в силу мгновенно и используется при сегментации уведомлений.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

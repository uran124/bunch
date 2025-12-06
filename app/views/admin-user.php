<?php /** @var array $user */ ?>
<?php /** @var array $orders */ ?>
<?php /** @var int $totalPages */ ?>
<?php /** @var int $currentPage */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Клиент</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Карточка клиента', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500">Общая информация, адреса, подписки и история заказов разбитая на страницы.</p>
            <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-700 ring-1 ring-rose-200">
                <span class="material-symbols-rounded text-base">send</span>
                Рассылки через телеграм-бота
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin-users"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К списку
            </a>
            <a
                href="/?page=admin-group-create"
                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl"
            >
                <span class="material-symbols-rounded text-base">group_add</span>
                В группу рассылки
            </a>
        </div>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid gap-3 md:grid-cols-2">
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Контакты</p>
                <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm text-slate-500">Телефон: <?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 font-semibold ring-1 ring-slate-200">
                        <span class="material-symbols-rounded text-base text-emerald-500">check_circle</span>
                        <?php echo $user['active'] ? 'Активен' : 'Не активен'; ?>
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 font-semibold ring-1 ring-indigo-200">
                        <span class="material-symbols-rounded text-base text-indigo-500">verified_user</span>
                        Пройдено KYC
                    </span>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Последний заказ</p>
                    <div class="mt-1 text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($user['lastOrder'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <p class="text-sm text-slate-500">Статус: доставлен</p>
                </div>
                <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-rose-600">Рассылки</p>
                    <div class="mt-1 text-lg font-semibold text-rose-700">Телеграм-бот</div>
                    <p class="text-sm text-rose-600">Напоминания и акции уходят в личный чат.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <header class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Адреса доставки</p>
                    <h2 class="text-lg font-semibold text-slate-900">Сохраненные адреса</h2>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">Изменить</button>
            </header>
            <div class="space-y-3">
                <?php foreach ($addresses as $address): ?>
                    <article class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($address['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($address['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($address['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                <span class="material-symbols-rounded text-base">location_on</span>
                                Основной
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <header class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Подписки</p>
                    <h2 class="text-lg font-semibold text-slate-900">Текущие программы</h2>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 shadow-sm">Настроить</button>
            </header>
            <div class="space-y-3">
                <?php foreach ($subscriptions as $subscription): ?>
                    <article class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($subscription['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($subscription['nextDelivery'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($subscription['tier'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                <span class="material-symbols-rounded text-base">autorenew</span>
                                <?php echo htmlspecialchars($subscription['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="mb-4 flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Заказы</p>
                <h2 class="text-xl font-semibold text-slate-900">История заказов</h2>
                <p class="text-sm text-slate-500">Показываем по 10 заказов на страницу. Всего страниц: <?php echo (int) $totalPages; ?>.</p>
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                <span class="material-symbols-rounded text-base">file_download</span>
                Экспорт
            </button>
        </header>

        <div class="overflow-hidden rounded-xl border border-slate-100">
            <div class="grid grid-cols-[1fr_1fr_1fr_auto] gap-3 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
                <span>Номер</span>
                <span>Дата</span>
                <span>Сумма</span>
                <span>Статус</span>
            </div>
            <?php foreach ($orders as $order): ?>
                <div class="grid grid-cols-[1fr_1fr_1fr_auto] items-center gap-3 px-4 py-3 text-sm text-slate-700">
                    <span class="font-semibold text-slate-900">#<?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['sum'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?php echo $order['status'] === 'Отменён' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'; ?>">
                        <span class="material-symbols-rounded text-base"><?php echo $order['status'] === 'Отменён' ? 'block' : 'check_circle'; ?></span>
                        <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
            <span>Страница <?php echo (int) $currentPage; ?> из <?php echo (int) $totalPages; ?></span>
            <div class="flex items-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a
                        href="/?page=admin-user&id=<?php echo (int) $user['id']; ?>&p=<?php echo $i; ?>"
                        class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg px-3 text-sm font-semibold transition <?php echo $i === $currentPage ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:-translate-y-0.5 hover:ring-rose-200'; ?>"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>
</section>

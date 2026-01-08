<?php /** @var array $selectedOrder */ ?>
<?php /** @var array $filters */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>
<?php $returnQuery = $returnQuery ?? ['page' => 'admin-orders-one-time']; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Разовые покупки</p>
            <h1 class="text-3xl font-semibold text-slate-900">Карточка заказа</h1>
            <p class="max-w-3xl text-base text-slate-500">Обновляйте статус, параметры доставки и контактные данные получателя. Изменения сохраняются вручную.</p>
        </div>
        <a href="/?<?php echo htmlspecialchars(http_build_query($returnQuery), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            К списку заказов
        </a>
    </header>

    <?php if ($message === 'updated'): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Заказ обновлён.</div>
    <?php elseif ($message === 'error'): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">Не удалось применить изменения. Проверьте данные и попробуйте снова.</div>
    <?php endif; ?>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Заказ</p>
                <h2 class="text-2xl font-semibold text-slate-900"><?php echo htmlspecialchars($selectedOrder['number'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-sm text-slate-500">Клиент: <?php echo htmlspecialchars($selectedOrder['customer'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    <span class="material-symbols-rounded text-base">payments</span>
                    <?php echo htmlspecialchars($selectedOrder['payment'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    <span class="material-symbols-rounded text-base">attach_money</span>
                    <?php echo htmlspecialchars($selectedOrder['sum'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </header>

        <div class="mt-4 grid gap-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Доставка</span>
                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($selectedOrder['delivery'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($selectedOrder['deliveryType'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Адрес</span>
                <span class="text-right font-semibold text-slate-900"><?php echo htmlspecialchars($selectedOrder['address'] ?? 'Не указано', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Комментарий клиента</span>
                <span class="text-right text-slate-900"><?php echo htmlspecialchars($selectedOrder['comment'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <form method="post" action="/?page=admin-order-update" class="mt-4 space-y-4">
            <input type="hidden" name="order_id" value="<?php echo (int) $selectedOrder['id']; ?>">
            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars(http_build_query(['page' => 'admin-order-one-time-edit', 'id' => $selectedOrder['id'], 'q' => $returnQuery['q'] ?? null, 'status_filter' => $returnQuery['status_filter'] ?? null, 'payment_filter' => $returnQuery['payment_filter'] ?? null]), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Статус</span>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <?php foreach ($filters['status'] as $value => $label): ?>
                            <?php if ($value === 'all') { continue; } ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedOrder['status'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Тип доставки</span>
                    <select name="delivery_type" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <option value="delivery" <?php echo $selectedOrder['deliveryType'] === 'Доставка' ? 'selected' : ''; ?>>Доставка</option>
                        <option value="pickup" <?php echo $selectedOrder['deliveryType'] === 'Самовывоз' ? 'selected' : ''; ?>>Самовывоз</option>
                    </select>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Дата</span>
                    <input type="date" name="scheduled_date" value="<?php echo htmlspecialchars($selectedOrder['scheduled_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Время</span>
                    <input type="time" name="scheduled_time" value="<?php echo htmlspecialchars(substr((string) ($selectedOrder['scheduled_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Получатель</span>
                    <input type="text" name="recipient_name" value="<?php echo htmlspecialchars($selectedOrder['recipient_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Например, Анна" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Телефон получателя</span>
                    <input type="text" name="recipient_phone" value="<?php echo htmlspecialchars($selectedOrder['recipient_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="+7..." class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="md:col-span-2 flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Адрес</span>
                    <textarea name="address_text" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Улица, дом, подъезд"><?php echo htmlspecialchars($selectedOrder['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
                <label class="md:col-span-2 flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Комментарий</span>
                    <textarea name="comment" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Добавьте инструкции курьеру или оператору"><?php echo htmlspecialchars($selectedOrder['comment'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-500">Обновлено: <?php echo htmlspecialchars($selectedOrder['updated_at'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">save</span>
                    Сохранить изменения
                </button>
            </div>
        </form>

        <div class="mt-6 space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Состав заказа</p>
            <?php foreach ($selectedOrder['items'] as $item): ?>
                <article class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="h-12 w-12 rounded-lg object-cover">
                    <div class="flex-1">
                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-500"><?php echo (int) $item['qty']; ?> шт · <?php echo htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['total'], ENT_QUOTES, 'UTF-8'); ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

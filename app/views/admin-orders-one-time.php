<?php /** @var array $orders */ ?>
<?php /** @var array $filters */ ?>
<?php /** @var array $selectedOrder */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $query = $query ?? ''; ?>
<?php $activeFilters = $activeFilters ?? ['status' => 'all', 'payment' => 'all']; ?>
<?php $message = $message ?? null; ?>
<?php $selectedOrder = $selectedOrder ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Разовые покупки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Разовые заказы', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Отслеживайте статусы, оплаты и доставку разовых заказов. Фильтры и поиск работают по данным из базы — можно быстро найти клиента или номер заказа.</p>
        </div>
        <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В панель
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.4fr_1fr_1fr]">
        <form method="get" action="/" class="contents">
            <input type="hidden" name="page" value="admin-orders-one-time">
            <label class="flex flex-col gap-2">
                <input
                    type="search"
                    name="q"
                    value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="поиск"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <label class="flex flex-col gap-2">
                <select name="status_filter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <?php foreach ($filters['status'] as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $value === ($activeFilters['status'] ?? 'all') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-2">
                <select name="payment_filter" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <?php foreach ($filters['payment'] as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $value === ($activeFilters['payment'] ?? 'all') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="lg:col-span-3 flex items-center justify-between pt-2">
                <p class="text-sm text-slate-500">Найдено заказов: <?php echo count($orders); ?>. Данные загружаются напрямую из базы.</p>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">search</span>
                    Применить
                </button>
            </div>
        </form>
    </div>

    <?php if ($message === 'updated'): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Заказ обновлён.</div>
    <?php elseif ($message === 'error'): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">Не удалось применить изменения. Проверьте данные и попробуйте снова.</div>
    <?php endif; ?>

    <div class="grid gap-5 xl:grid-cols-[1.9fr_1.2fr]">
        <div class="overflow-hidden rounded-2xl shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <?php foreach ($orders as $order): ?>
                <?php
                $deliveryDate = '—';

                if (!empty($order['scheduled_date_raw'])) {
                    try {
                        $deliveryDate = (new DateTimeImmutable($order['scheduled_date_raw']))->format('d.m');
                    } catch (Throwable $e) {
                        $deliveryDate = $order['scheduled_date_raw'];
                    }
                }

                $deliveryTime = $order['scheduled_time_raw'] ? substr((string) $order['scheduled_time_raw'], 0, 5) : '—';

                $linkParams = [
                    'page' => 'admin-orders-one-time',
                    'id' => $order['id'],
                    'q' => $query,
                    'status_filter' => $activeFilters['status'] ?? 'all',
                    'payment_filter' => $activeFilters['payment'] ?? 'all',
                ];
                ?>
                <article class="space-y-4 border-b border-slate-100 last:border-b-0 rounded-xl bg-slate-50 p-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
                            <a href="/?<?php echo http_build_query($linkParams); ?>" class="text-base font-semibold text-rose-700 underline-offset-4 hover:text-rose-800 hover:underline">
                                <?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                <span class="material-symbols-rounded text-base">event</span>
                                <?php echo htmlspecialchars($deliveryDate, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($deliveryTime, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                <span class="material-symbols-rounded text-base">payments</span>
                                <?php echo htmlspecialchars($order['sum'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <form method="post" action="/?page=admin-order-update" class="flex flex-wrap items-center gap-2">
                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                            <input type="hidden" name="delivery_type" value="<?php echo htmlspecialchars($order['deliveryTypeValue'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="scheduled_date" value="<?php echo htmlspecialchars($order['scheduled_date_raw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="scheduled_time" value="<?php echo htmlspecialchars($order['scheduled_time_raw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="recipient_name" value="<?php echo htmlspecialchars($order['recipientNameRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="recipient_phone" value="<?php echo htmlspecialchars($order['recipientPhoneRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="address_text" value="<?php echo htmlspecialchars($order['addressTextRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="comment" value="<?php echo htmlspecialchars($order['commentRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                                    <?php foreach ($filters['status'] as $value => $label): ?>
                                        <?php if ($value === 'all') { continue; } ?>
                                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $order['status'] === $value ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                                <span class="material-symbols-rounded text-base">save</span>
                                Обновить
                            </button>
                        </form>
                    </div>

                    <div class="grid gap-4 rounded-xl p-0 md:grid-cols-[1fr_1.5fr]">
                        <div class="space-y-3">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="rounded-lg bg-white p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-xs text-slate-500">Атрибуты: <?php echo htmlspecialchars($item['attributes'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <div class="text-right text-sm font-semibold text-slate-900"><?php echo (int) $item['qty']; ?> шт</div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-700">
                                        <span>Полная стоимость</span>
                                        <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if ($order['deliveryTypeValue'] === 'delivery' && $order['deliveryPrice']): ?>
                                <div class="rounded-lg border border-dashed border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold">Доставка</span>
                                        <span class="font-semibold"><?php echo htmlspecialchars($order['deliveryPrice'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-700">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-600"><?php echo htmlspecialchars($order['customer'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo htmlspecialchars($order['customerPhone'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <button type="button" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-copy-text="<?php echo htmlspecialchars(trim(($order['customer'] ?? '') . ' ' . ($order['customerPhone'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="material-symbols-rounded text-base">content_copy</span>
                                </button>
                            </div>

                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo $order['deliveryTypeValue'] === 'delivery' ? 'Доставка' : 'Самовывоз'; ?></p>
                                    <p class="text-slate-600"><?php echo htmlspecialchars($order['address'] ?? 'Не указано', ENT_QUOTES, 'UTF-8'); ?>
                                        <button type="button" class="text-xs font-semibold text-slate-700 transition hover:border-rose-200 hover:text-rose-700" data-copy-text="<?php echo htmlspecialchars(trim(($order['deliveryTypeValue'] === 'delivery' ? ($order['address'] ?? '') : 'Самовывоз')), ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="material-symbols-rounded text-base">content_copy</span>
                                        </button>
                                    </p>
                                </div>
                                
                            </div>

                            <?php if (!empty($order['recipient_name']) || !empty($order['recipient_phone'])): ?>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($order['recipient_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($order['recipient_phone'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-copy-text="<?php echo htmlspecialchars(trim(($order['recipient_name'] ?? '') . ' ' . ($order['recipient_phone'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                                        <span class="material-symbols-rounded text-base">content_copy</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            

                            <?php if (!empty($order['comment'])): ?>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold tracking-[0.12em] text-slate-500">Примечания</p>
                                        <p class="text-slate-700"><?php echo nl2br(htmlspecialchars($order['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-copy-text="<?php echo htmlspecialchars($order['comment'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <span class="material-symbols-rounded text-base">content_copy</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <?php if (!empty($selectedOrder)): ?>
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
            <?php else: ?>
                <div class="flex h-full flex-col items-center justify-center gap-3 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                        <span class="material-symbols-rounded">assignment_add</span>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold text-slate-900">Выберите заказ для обработки</h3>
                        <p class="text-sm text-slate-500">Нажмите «Обработать» в списке слева, чтобы открыть карточку и внести изменения.</p>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<script>
document.addEventListener('click', (event) => {
    const copyButton = event.target.closest('[data-copy-text]');

    if (!copyButton || !navigator.clipboard) {
        return;
    }

    const text = copyButton.getAttribute('data-copy-text');

    if (!text) {
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        copyButton.classList.add('border-emerald-200', 'text-emerald-700');
        setTimeout(() => {
            copyButton.classList.remove('border-emerald-200', 'text-emerald-700');
        }, 1200);
    }).catch(() => {
    });
});
</script>

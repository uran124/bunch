<?php
/** @var array $order */
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Редактирование</p>
            <h1 class="text-3xl font-semibold text-slate-900">Заказ <?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Измените детали доставки перед оплатой. После оплаты изменения станут недоступны.</p>
        </div>
        <a
            href="/orders"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <span class="material-symbols-rounded text-base">arrow_back</span>
            К заказам
        </a>
    </header>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <form method="post" action="/order-edit" class="space-y-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">

            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Детали доставки</p>
                    <h2 class="text-xl font-semibold text-slate-900">Настройки заказа</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    <span class="material-symbols-rounded text-base">payments</span>
                    Статус: <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Тип получения
                    <select name="delivery_type" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <option value="pickup" <?php echo $order['deliveryTypeValue'] === 'pickup' ? 'selected' : ''; ?>>Самовывоз</option>
                        <option value="delivery" <?php echo $order['deliveryTypeValue'] === 'delivery' ? 'selected' : ''; ?>>Доставка</option>
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Дата доставки
                    <input
                        type="date"
                        name="scheduled_date"
                        value="<?php echo htmlspecialchars($order['scheduled_date_raw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Время
                    <input
                        type="time"
                        name="scheduled_time"
                        value="<?php echo htmlspecialchars($order['scheduled_time_raw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                    Адрес доставки
                    <input
                        type="text"
                        name="address_text"
                        placeholder="Введите адрес"
                        value="<?php echo htmlspecialchars($order['addressTextRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Получатель
                    <input
                        type="text"
                        name="recipient_name"
                        placeholder="Имя"
                        value="<?php echo htmlspecialchars($order['recipientNameRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Телефон получателя
                    <input
                        type="tel"
                        name="recipient_phone"
                        placeholder="+7 (___) ___-__-__"
                        value="<?php echo htmlspecialchars($order['recipientPhoneRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                    Комментарий к заказу
                    <textarea
                        name="comment"
                        rows="3"
                        class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        placeholder="Например, оставить у консьержа"
                    ><?php echo htmlspecialchars($order['commentRaw'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <p class="text-xs text-slate-500">Нажимая «Сохранить», вы обновляете детали заказа.</p>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <span class="material-symbols-rounded text-base">save</span>
                    Сохранить
                </button>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Состав заказа</p>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                        <span class="material-symbols-rounded text-base">shopping_bag</span>
                        <?php echo htmlspecialchars($order['total'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
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

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-base">info</span>
                    <p>После оплаты заказ автоматически перейдёт в работу.</p>
                </div>
            </div>
        </aside>
    </div>
</section>

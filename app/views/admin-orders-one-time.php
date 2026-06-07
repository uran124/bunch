<?php /** @var array $orders */ ?>
<?php /** @var array $filters */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $query = $query ?? ''; ?>
<?php $activeFilters = $activeFilters ?? ['status' => 'all', 'payment' => 'all']; ?>
<?php $message = $message ?? null; ?>
<?php $canDeleteOrders = $canDeleteOrders ?? true; ?>
<?php
$returnQuery = [
    'q' => $query,
    'status_filter' => $activeFilters['status'] ?? 'all',
    'payment_filter' => $activeFilters['payment'] ?? 'all',
];
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Разовые покупки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Разовые заказы', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Отслеживайте статусы, оплаты и доставку разовых заказов. Фильтры и поиск работают по данным из базы — можно быстро найти клиента или номер заказа.</p>
        </div>
        <a href="/admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В панель
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.4fr_1fr_1fr]">
        <form method="get" action="/admin-orders-one-time" class="contents" data-order-filters>
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
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Фильтры применяются автоматически</span>
            </div>
        </form>
    </div>

    <?php if ($message === 'updated'): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Заказ обновлён.</div>
    <?php elseif ($message === 'deleted'): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Заказ удалён.</div>
    <?php elseif ($message === 'error'): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">Не удалось применить изменения. Проверьте данные и попробуйте снова.</div>
    <?php endif; ?>

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
                'id' => $order['id'],
                'q' => $query,
                'status_filter' => $activeFilters['status'] ?? 'all',
                'payment_filter' => $activeFilters['payment'] ?? 'all',
            ];
            $statusBadgeClasses = match ($order['status']) {
                'new' => 'bg-rose-50 text-rose-700 ring-rose-100',
                'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'assembled' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'delivering' => 'bg-amber-50 text-amber-700 ring-amber-100',
                'completed', 'delivered' => 'bg-white text-slate-700 ring-slate-200',
                'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-200',
                'returned' => 'bg-orange-50 text-orange-700 ring-orange-100',
                default => 'bg-slate-50 text-slate-700 ring-slate-100',
            };
            ?>
            <article class="space-y-4 border-b border-slate-100 last:border-b-0 rounded-xl bg-slate-50 p-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
                        <a href="/admin-order-one-time-edit?<?php echo http_build_query($linkParams); ?>" class="text-base font-semibold text-rose-700 underline-offset-4 hover:text-rose-800 hover:underline">
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
                        <?php if (!empty($order['cashbackSpent']) && $order['cashbackSpent'] !== '0 ₽'): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                <span class="material-symbols-rounded text-base">savings</span>
                                Списано: <?php echo htmlspecialchars($order['cashbackSpent'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="post" action="/admin-order-status" class="relative flex flex-wrap items-center gap-2" data-order-status-menu>
                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars('/admin-orders-one-time' . '?' . http_build_query($returnQuery), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="button" class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition hover:-translate-y-0.5 hover:shadow-sm <?php echo htmlspecialchars($statusBadgeClasses, ENT_QUOTES, 'UTF-8'); ?>" data-order-status-toggle aria-expanded="false" title="Сменить статус">
                                <span class="material-symbols-rounded text-base">sync_alt</span>
                                <?php echo htmlspecialchars($order['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <div class="absolute right-0 top-full z-20 mt-2 hidden min-w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 text-sm shadow-xl shadow-slate-200/70" data-order-status-options>
                                <?php foreach (($filters['editableStatus'] ?? $filters['status']) as $value => $label): ?>
                                    <?php if ($value === 'all') { continue; } ?>
                                    <?php $statusEnabled = in_array($value, $filters['enabledStatusValues'] ?? [], true) || $order['status'] === $value; ?>
                                    <button
                                        type="submit"
                                        name="status"
                                        value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left font-semibold transition <?php echo $order['status'] === $value ? 'bg-rose-50 text-rose-700' : 'text-slate-700 hover:bg-slate-50 hover:text-rose-700'; ?> <?php echo $statusEnabled ? '' : 'cursor-not-allowed opacity-45'; ?>"
                                        <?php echo $statusEnabled ? '' : 'disabled'; ?>
                                    >
                                        <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($order['status'] === $value): ?>
                                            <span class="material-symbols-rounded text-base">check</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </form>
                        <?php if ($canDeleteOrders): ?>
                            <form method="post" action="/admin-order-delete">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars('/admin-orders-one-time' . '?' . http_build_query($returnQuery), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-800" data-order-delete aria-label="Удалить заказ">
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
                                    <span>Цена со стебл. атрибутами × кол-во</span>
                                    <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['stemsTotal'] ?? $item['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <?php if (!empty($item['bouquetTotal'])): ?>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-700">
                                        <span>Атрибут к букету</span>
                                        <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($item['bouquetTotal'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-2 flex items-center justify-between text-sm text-slate-700">
                                    <span>Полная стоимость позиции</span>
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

document.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-order-delete]');

    if (!deleteButton) {
        return;
    }

    if (!window.confirm('Удалить заказ?')) {
        event.preventDefault();
    }
});

const filterForm = document.querySelector('[data-order-filters]');

if (filterForm) {
    const searchField = filterForm.querySelector('input[type="search"]');
    const selectFields = filterForm.querySelectorAll('select');
    let searchTimeout;

    selectFields.forEach((select) => {
        select.addEventListener('change', () => filterForm.submit());
    });

    if (searchField) {
        searchField.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => filterForm.submit(), 500);
        });
    }
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-order-status-toggle]');

    document.querySelectorAll('[data-order-status-menu]').forEach((menu) => {
        if (toggle && menu.contains(toggle)) {
            return;
        }

        menu.querySelector('[data-order-status-options]')?.classList.add('hidden');
        menu.querySelector('[data-order-status-toggle]')?.setAttribute('aria-expanded', 'false');
    });

    if (!toggle) {
        return;
    }

    event.preventDefault();
    const menu = toggle.closest('[data-order-status-menu]');
    const options = menu?.querySelector('[data-order-status-options]');
    if (!options) {
        return;
    }

    const isHidden = options.classList.toggle('hidden');
    toggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
});
</script>

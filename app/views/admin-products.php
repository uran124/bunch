<?php /** @var array $products */ ?>
<?php /** @var array $filters */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>
<?php $blockedRelations = $blockedRelations ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Товары</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Товары', ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-product-form" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">add</span>
                Новый товар
            </a>
            <a href="/?page=admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">redeem</span>
                Акции
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <?php $isError = in_array($message, ['error', 'delete-blocked'], true); ?>
        <div class="flex items-start gap-3 rounded-xl border <?php echo $isError ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'; ?> px-4 py-3 text-sm">
            <span class="material-symbols-rounded text-lg"><?php echo $isError ? 'error' : ($message === 'deleted' ? 'delete' : 'check_circle'); ?></span>
            <div>
                <p class="font-semibold">
                    <?php if ($message === 'error'): ?>
                        Заполните обязательные поля: поставку и базовую цену.
                    <?php elseif ($message === 'delete-blocked'): ?>
                        Нельзя удалить товар с активными заказами, подписками или в корзинах.
                    <?php elseif ($message === 'deleted'): ?>
                        Товар удалён.
                    <?php else: ?>
                        Товар сохранён.
                    <?php endif; ?>
                </p>
                <?php if ($message === 'delete-blocked'): ?>
                    <p>Удалите связи с заказами, подписками или корзинами и попробуйте снова.</p>
                    <?php if (!empty($blockedRelations)): ?>
                        <div class="mt-2 space-y-1 text-sm text-rose-700">
                            <?php if (!empty($blockedRelations['orders'])): ?>
                                <div>
                                    <span class="font-semibold text-rose-800">Заказы:</span>
                                    <?php foreach ($blockedRelations['orders'] as $index => $order): ?>
                                        <a href="/?page=admin-order-one-time-edit&id=<?php echo (int) $order['id']; ?>" class="font-semibold text-rose-700 underline-offset-4 hover:underline">#<?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?></a><?php echo $index + 1 < count($blockedRelations['orders']) ? ', ' : ''; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($blockedRelations['subscriptions'])): ?>
                                <div>
                                    <span class="font-semibold text-rose-800">Подписки:</span>
                                    <?php foreach ($blockedRelations['subscriptions'] as $index => $subscription): ?>
                                        <a href="/?page=admin-user&id=<?php echo (int) $subscription['user_id']; ?>" class="font-semibold text-rose-700 underline-offset-4 hover:underline">
                                            <?php echo htmlspecialchars($subscription['name'] ?? 'Клиент', ENT_QUOTES, 'UTF-8'); ?>
                                        </a><?php echo $index + 1 < count($blockedRelations['subscriptions']) ? ', ' : ''; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($blockedRelations['carts'])): ?>
                                <div>
                                    <span class="font-semibold text-rose-800">Корзины:</span>
                                    <?php foreach ($blockedRelations['carts'] as $index => $cart): ?>
                                        <?php if (!empty($cart['user_id'])): ?>
                                            <a href="/?page=admin-user&id=<?php echo (int) $cart['user_id']; ?>" class="font-semibold text-rose-700 underline-offset-4 hover:underline">
                                                <?php echo htmlspecialchars($cart['name'] ?? 'Клиент', ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="font-semibold text-rose-700">Гостевая корзина #<?php echo (int) $cart['id']; ?></span>
                                        <?php endif; ?>
                                        <?php echo $index + 1 < count($blockedRelations['carts']) ? ', ' : ''; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ($message === 'error'): ?>
                    <p>Карточка сохраняется только при заполнении обязательных полей.</p>
                <?php else: ?>
                    <p>Карточка обновляется с выбранными атрибутами и ценовыми уровнями.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[120px_1.6fr_1.2fr_120px_140px_120px_60px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Артикул</span>
            <span>Название</span>
            <span>Поставка</span>
            <span>След. поставка</span>
            <span>Стоимость</span>
            <span>Активность</span>
            <span class="text-right">Удалить</span>
        </div>
        <?php foreach ($products as $product): ?>
            <?php
                $articleLabel = $product['article'] ?? '—';
                $supplyTitle = trim(($product['flower_name'] ?? '') . ' ' . ($product['variety'] ?? ''));
                if ($supplyTitle === '' && !empty($product['supply_id'])) {
                    $supplyTitle = 'Поставка #' . (int) $product['supply_id'];
                }

                $nextDeliveryLabel = '—';
                if (!empty($product['supply_next_delivery'])) {
                    try {
                        $nextDeliveryLabel = (new DateTime($product['supply_next_delivery']))->format('d.m');
                    } catch (Exception $e) {
                        $nextDeliveryLabel = '—';
                    }
                }

                $prices = [(float) $product['price']];
                foreach ($product['price_tiers'] as $tier) {
                    $prices[] = (float) $tier['price'];
                }
                $minPrice = min($prices);
                $maxPrice = max($prices);
                $priceLabel = $minPrice < $maxPrice
                    ? number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2)
                    : number_format($maxPrice, 2);
            ?>
            <article class="grid grid-cols-[120px_1.6fr_1.2fr_120px_140px_120px_60px] items-center gap-4 border-b border-slate-100 px-5 py-4 text-sm last:border-b-0">
                <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($articleLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="space-y-1">
                    <a href="/?page=admin-product-form&edit_id=<?php echo (int) $product['id']; ?>" class="text-base font-semibold text-slate-900 underline-offset-4 hover:text-rose-600 hover:underline">
                        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <div class="text-xs text-slate-500">ID: #<?php echo (int) $product['id']; ?></div>
                </div>
                <div class="space-y-1">
                    <?php if (!empty($product['supply_id'])): ?>
                        <a href="/?page=admin-supply-edit&id=<?php echo (int) $product['supply_id']; ?>" class="font-semibold text-slate-700 underline-offset-4 hover:text-emerald-700 hover:underline">
                            <?php echo htmlspecialchars($supplyTitle !== '' ? $supplyTitle : 'Поставка', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-slate-400">—</span>
                    <?php endif; ?>
                </div>
                <div class="text-slate-700"><?php echo htmlspecialchars($nextDeliveryLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?> ₽</div>
                <div>
                    <form action="/?page=admin-product-toggle" method="post">
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                        <label class="relative inline-flex h-8 w-14 cursor-pointer items-center" aria-label="Активность товара">
                            <input type="checkbox" name="is_active" class="peer sr-only" onchange="this.form.submit()" <?php echo ($product['is_active'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                            <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                        </label>
                    </form>
                </div>
                <div class="flex justify-end">
                    <form action="/?page=admin-product-delete" method="post" onsubmit="return confirm('Удалить товар?');">
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-rose-100 bg-rose-50 text-rose-700 transition hover:-translate-y-0.5 hover:border-rose-200" aria-label="Удалить товар">
                            <span class="material-symbols-rounded text-base">delete</span>
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

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
        <div class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Наименование</span>
            <span>Поставка</span>
            <span>Характеристики</span>
            <span>Цена/Статус</span>
            <span class="text-right">Действия</span>
        </div>
        <?php foreach ($products as $product): ?>
            <article class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $product['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Артикул: <?php echo htmlspecialchars($product['article'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Атрибуты: <?php echo !empty($product['attribute_ids']) ? count($product['attribute_ids']) : 0; ?></div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-rose-700"><?php echo htmlspecialchars(($product['flower_name'] ?? '') . ' ' . ($product['variety'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Страна: <?php echo htmlspecialchars($product['country'] ?? $product['supply_country'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Высота: <?php echo htmlspecialchars($product['stem_height_cm'] ?? $product['supply_height'] ?? '—', ENT_QUOTES, 'UTF-8'); ?> см · Вес: <?php echo htmlspecialchars($product['stem_weight_g'] ?? $product['supply_weight'] ?? '—', ENT_QUOTES, 'UTF-8'); ?> г</div>
                </div>
                <div class="text-sm text-slate-700">
                    <div>Создан: <?php echo htmlspecialchars($product['created_at'] ?? $product['createdAt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>Обновлён: <?php echo htmlspecialchars($product['updated_at'] ?? $product['updatedAt'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="flex flex-col gap-1 text-sm text-slate-700">
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8'); ?> ₽</span>
                    <?php if (!empty($product['price_tiers'])): ?>
                        <div class="text-xs text-slate-500">
                            <?php foreach ($product['price_tiers'] as $tier): ?>
                                <div>от <?php echo (int) $tier['min_qty']; ?> шт — <?php echo htmlspecialchars(number_format((float) $tier['price'], 2), ENT_QUOTES, 'UTF-8'); ?> ₽</div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-wide <?php echo ($product['is_active'] ?? 0) ? 'text-emerald-700 ring-emerald-200' : 'text-slate-500 ring-slate-200'; ?> ring-1"><?php echo ($product['is_active'] ?? 0) ? 'Активен' : 'Черновик'; ?></span>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="/?page=admin-product-form&edit_id=<?php echo (int) $product['id']; ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Редактировать
                    </a>
                    <form action="/?page=admin-product-delete" method="post" onsubmit="return confirm('Удалить товар?');">
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                            <span class="material-symbols-rounded text-base">delete</span>
                            Удалить
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php /** @var array $supplies */ ?>
<?php /** @var array $attributes */ ?>
<?php /** @var array|null $editingProduct */ ?>
<?php /** @var array|null $productRelations */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>
<?php $productRelations = $productRelations ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Товары</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? ($editingProduct ? 'Редактирование товара' : 'Новый товар'), ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin-products" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К товарам
            </a>
            <a href="/admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">dashboard</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <?php $isError = in_array($message, ['error'], true); ?>
        <div class="flex items-start gap-3 rounded-xl border <?php echo $isError ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'; ?> px-4 py-3 text-sm">
            <span class="material-symbols-rounded text-lg"><?php echo $isError ? 'error' : 'check_circle'; ?></span>
            <div>
                <p class="font-semibold">
                    <?php if ($message === 'error'): ?>
                        Заполните обязательные поля: поставку и базовую цену.
                    <?php else: ?>
                        Товар сохранён.
                    <?php endif; ?>
                </p>
                <?php if ($message === 'error'): ?>
                    <p>Карточка сохраняется только при заполнении обязательных полей.</p>
                <?php else: ?>
                    <p>Карточка обновляется с выбранными атрибутами и ценовыми уровнями.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500"><?php echo $editingProduct ? 'Редактирование товара' : 'Новый товар'; ?></p>
            <h2 class="text-xl font-semibold text-slate-900">Карточка товара</h2>
            <form action="/admin-product-save" method="post" enctype="multipart/form-data" class="space-y-3">
                <?php if ($editingProduct): ?>
                    <input type="hidden" name="product_id" value="<?php echo (int) $editingProduct['id']; ?>">
                <?php endif; ?>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Поставка
                        <select name="supply_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                            <option value="">Выберите поставку</option>
                            <?php foreach ($supplies as $supply): ?>
                                <?php
                                $label = $supply['flower_name'] . ' ' . $supply['variety'] . ' · ' . ($supply['country'] ?? '');
                                $isSelected = $editingProduct && (int) $editingProduct['supply_id'] === (int) $supply['id'];
                                if (!$editingProduct && isset($selectedSupplyId)) {
                                    $isSelected = (int) $selectedSupplyId === (int) $supply['id'];
                                }
                                ?>
                                <option value="<?php echo (int) $supply['id']; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Раздел каталога
                        <?php $categoryValue = $editingProduct['category'] ?? 'main'; ?>
                        <select name="category" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                            <option value="main" <?php echo $categoryValue === 'main' ? 'selected' : ''; ?>>Главная витрина</option>
                            <option value="wholesale" <?php echo $categoryValue === 'wholesale' ? 'selected' : ''; ?>>Опт</option>
                            <option value="accessory" <?php echo $categoryValue === 'accessory' ? 'selected' : ''; ?>>Сопутствующие товары</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Тип товара
                        <?php
                            $productTypeValue = $editingProduct['product_type'] ?? 'regular';
                            $productTypeOptions = [
                                'regular' => 'Обычный',
                                'small_wholesale' => 'Мелкий опт (пачки)',
                                'wholesale_box' => 'Опт (коробки)',
                            ];
                        ?>
                        <select name="product_type" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                            <?php foreach ($productTypeOptions as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $productTypeValue === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Артикул
                        <input name="article" value="<?php echo htmlspecialchars($editingProduct['article'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm" placeholder="SKU/артикул">
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Альтернативное название для карточки
                        <input name="alt_name" value="<?php echo htmlspecialchars($editingProduct['alt_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm" placeholder="Например: Роза Red Naomie 60 см">
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Базовая цена, ₽
                        <input name="price" type="number" step="1" required value="<?php echo htmlspecialchars($editingProduct['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm">
                    </label>
                </div>

                <?php
                $photoSlots = [
                    [
                        'key' => 'main',
                        'label' => 'Основное фото',
                        'urlField' => 'photo_url',
                        'fileField' => 'photo_file',
                        'deleteField' => 'photo_delete',
                        'value' => $editingProduct['photo_url'] ?? '',
                    ],
                    [
                        'key' => 'secondary',
                        'label' => 'Дополнительное фото',
                        'urlField' => 'photo_url_secondary',
                        'fileField' => 'photo_file_secondary',
                        'deleteField' => 'photo_delete_secondary',
                        'value' => $editingProduct['photo_url_secondary'] ?? '',
                    ],
                    [
                        'key' => 'tertiary',
                        'label' => 'Дополнительное фото',
                        'urlField' => 'photo_url_tertiary',
                        'fileField' => 'photo_file_tertiary',
                        'deleteField' => 'photo_delete_tertiary',
                        'value' => $editingProduct['photo_url_tertiary'] ?? '',
                    ],
                ];
                ?>

                <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-800">Фотографии товара</p>
                    <div class="grid gap-3 md:grid-cols-3">
                        <?php foreach ($photoSlots as $slot): ?>
                            <?php $hasPhoto = !empty($slot['value']); ?>
                            <div class="relative space-y-2" data-photo-slot>
                                <input type="hidden" name="<?php echo $slot['urlField']; ?>" value="<?php echo htmlspecialchars($slot['value'], ENT_QUOTES, 'UTF-8'); ?>" data-photo-url>
                                <input type="hidden" name="<?php echo $slot['deleteField']; ?>" value="0" data-photo-delete>
                                <input
                                    id="photo-<?php echo $slot['key']; ?>"
                                    name="<?php echo $slot['fileField']; ?>"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    data-photo-input
                                >
                                <label
                                    for="photo-<?php echo $slot['key']; ?>"
                                    class="relative flex aspect-square w-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-white text-slate-400 shadow-sm transition hover:border-rose-200 hover:text-rose-400"
                                >
                                    <span class="material-symbols-rounded text-3xl <?php echo $hasPhoto ? 'hidden' : ''; ?>" data-photo-placeholder>add</span>
                                    <img
                                        src="<?php echo htmlspecialchars($slot['value'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="absolute inset-0 h-full w-full object-cover <?php echo $hasPhoto ? '' : 'hidden'; ?>"
                                        data-photo-preview
                                    >
                                </label>
                                <button
                                    type="button"
                                    class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-sm transition hover:bg-white hover:text-rose-600 <?php echo $hasPhoto ? '' : 'hidden'; ?>"
                                    aria-label="Удалить фото"
                                    data-photo-remove
                                >
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                                <p class="text-center text-xs font-semibold text-slate-500"><?php echo htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs font-normal text-slate-500">Изображения обрежутся до квадрата и сохранятся в WebP.</p>
                </div>

                <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-800">Стоимость по количеству</p>
                        <button type="button" id="add-tier" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                            <span class="material-symbols-rounded text-base">add</span>
                            Добавить уровень
                        </button>
                    </div>
                    <div id="tier-fields" class="space-y-2">
                        <?php $tiers = $editingProduct['price_tiers'] ?? []; ?>
                        <?php if (empty($tiers)): ?>
                            <?php $tiers = [['min_qty' => '', 'price' => '']]; ?>
                        <?php endif; ?>
                        <?php foreach ($tiers as $tier): ?>
                            <div class="grid gap-2 md:grid-cols-[1fr_1fr_auto] md:items-center tier-row">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    От (шт)
                                    <input name="tier_min_qty[]" type="number" min="1" value="<?php echo htmlspecialchars($tier['min_qty'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Цена, ₽
                                    <input name="tier_price[]" type="number" step="1" value="<?php echo htmlspecialchars($tier['price'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                </label>
                                <button type="button" class="remove-tier inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                                    <span class="material-symbols-rounded text-base">delete</span>
                                    Удалить
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-800">Атрибуты для товара</p>
                    <div class="grid gap-2 md:grid-cols-2">
                        <?php foreach ($attributes as $attribute): ?>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                <span><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="relative inline-flex items-center">
                                    <input type="checkbox" name="attribute_ids[]" value="<?php echo (int) $attribute['id']; ?>" class="peer sr-only" <?php echo $editingProduct && in_array((int) $attribute['id'], $editingProduct['attribute_ids'] ?? [], true) ? 'checked' : ''; ?>>
                                    <span class="inline-flex h-6 w-11 items-center rounded-full border border-slate-200 bg-slate-100 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-500">
                                        <span class="inline-block h-5 w-5 translate-x-0.5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?php echo !$editingProduct || ($editingProduct['is_active'] ?? 0) ? 'checked' : ''; ?>>
                        Активен на витрине
                    </label>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                        <span class="material-symbols-rounded text-base">save</span>
                        <?php echo $editingProduct ? 'Обновить товар' : 'Создать товар'; ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <?php if ($editingProduct): ?>
        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Связи товара</p>
                <h2 class="text-xl font-semibold text-slate-900">Заказы, подписки и корзины</h2>
                <?php if (empty($productRelations['orders']) && empty($productRelations['subscriptions']) && empty($productRelations['carts'])): ?>
                    <p class="text-sm text-slate-600">Связей не найдено — товар не участвует в заказах, подписках или корзинах.</p>
                <?php else: ?>
                    <div class="grid gap-3 text-sm text-slate-700 md:grid-cols-3">
                        <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Заказы</p>
                            <?php if (!empty($productRelations['orders'])): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($productRelations['orders'] as $order): ?>
                                        <a href="/admin-order-one-time-edit?id=<?php echo (int) $order['id']; ?>" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-rose-200 hover:text-rose-700">
                                            #<?php echo htmlspecialchars($order['number'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">Нет активных заказов.</p>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Подписки</p>
                            <?php if (!empty($productRelations['subscriptions'])): ?>
                                <div class="flex flex-col gap-2">
                                    <?php foreach ($productRelations['subscriptions'] as $subscription): ?>
                                        <a href="/admin-user?id=<?php echo (int) $subscription['user_id']; ?>" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-rose-200 hover:text-rose-700">
                                            <?php echo htmlspecialchars($subscription['name'] ?? 'Клиент', ENT_QUOTES, 'UTF-8'); ?>
                                            <span class="text-[11px] font-normal text-slate-500">(<?php echo htmlspecialchars($subscription['status'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>)</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">Нет активных подписок.</p>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Корзины</p>
                            <?php if (!empty($productRelations['carts'])): ?>
                                <div class="flex flex-col gap-2">
                                    <?php foreach ($productRelations['carts'] as $cart): ?>
                                        <?php if (!empty($cart['user_id'])): ?>
                                            <a href="/admin-user?id=<?php echo (int) $cart['user_id']; ?>" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-rose-200 hover:text-rose-700">
                                                <?php echo htmlspecialchars($cart['name'] ?? 'Клиент', ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600">Гостевая корзина #<?php echo (int) $cart['id']; ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">Нет активных корзин.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<script>
    (function() {
        const addTierBtn = document.getElementById('add-tier');
        const tierContainer = document.getElementById('tier-fields');

        const photoSlots = document.querySelectorAll('[data-photo-slot]');
        if (photoSlots.length > 0) {
            photoSlots.forEach((slot) => {
                const input = slot.querySelector('[data-photo-input]');
                const preview = slot.querySelector('[data-photo-preview]');
                const placeholder = slot.querySelector('[data-photo-placeholder]');
                const removeBtn = slot.querySelector('[data-photo-remove]');
                const urlField = slot.querySelector('[data-photo-url]');
                const deleteField = slot.querySelector('[data-photo-delete]');

                const updateState = (url) => {
                    if (url) {
                        preview.src = url;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                        removeBtn.classList.remove('hidden');
                    } else {
                        preview.removeAttribute('src');
                        preview.classList.add('hidden');
                        placeholder.classList.remove('hidden');
                        removeBtn.classList.add('hidden');
                    }
                };

                if (urlField?.value) {
                    updateState(urlField.value);
                }

                input?.addEventListener('change', () => {
                    if (input.files && input.files[0]) {
                        const objectUrl = URL.createObjectURL(input.files[0]);
                        updateState(objectUrl);
                        if (deleteField) {
                            deleteField.value = '0';
                        }
                    }
                });

                removeBtn?.addEventListener('click', () => {
                    if (input) {
                        input.value = '';
                    }
                    if (urlField) {
                        urlField.value = '';
                    }
                    if (deleteField) {
                        deleteField.value = '1';
                    }
                    updateState('');
                });
            });
        }

        if (!addTierBtn || !tierContainer) {
            return;
        }

        const createRow = () => {
            const wrapper = document.createElement('div');
            wrapper.className = 'grid gap-2 md:grid-cols-[1fr_1fr_auto] md:items-center tier-row';
            wrapper.innerHTML = `
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    От (шт)
                    <input name="tier_min_qty[]" type="number" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                </label>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Цена, ₽
                    <input name="tier_price[]" type="number" step="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                </label>
                <button type="button" class="remove-tier inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                    <span class="material-symbols-rounded text-base">delete</span>
                    Удалить
                </button>
            `;
            tierContainer.appendChild(wrapper);
        };

        tierContainer.addEventListener('click', (event) => {
            const target = event.target.closest('.remove-tier');
            if (target) {
                const row = target.closest('.tier-row');
                if (row && tierContainer.children.length > 1) {
                    row.remove();
                }
            }
        });

        addTierBtn.addEventListener('click', () => createRow());
    })();
</script>

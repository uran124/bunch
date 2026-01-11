<?php /** @var array $supplies */ ?>
<?php /** @var array $attributes */ ?>
<?php /** @var array|null $editingProduct */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Товары</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? ($editingProduct ? 'Редактирование товара' : 'Новый товар'), ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-products" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К товарам
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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
            <form action="/?page=admin-product-save" method="post" enctype="multipart/form-data" class="space-y-3">
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
                        Артикул
                        <input name="article" value="<?php echo htmlspecialchars($editingProduct['article'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm" placeholder="SKU/артикул">
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Основное фото (URL)
                        <input name="photo_url" value="<?php echo htmlspecialchars($editingProduct['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm" placeholder="https://...">
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Загрузить фото
                        <input name="photo_file" type="file" accept="image/*" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm">
                        <span class="text-xs font-normal text-slate-500">Изображение обрежется до квадрата и сохранится в WebP.</span>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Базовая цена, ₽
                        <input name="price" type="number" step="0.01" required value="<?php echo htmlspecialchars($editingProduct['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm">
                    </label>
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
                                    <input name="tier_price[]" type="number" step="0.01" value="<?php echo htmlspecialchars($tier['price'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
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
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="attribute_ids[]" value="<?php echo (int) $attribute['id']; ?>" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?php echo $editingProduct && in_array((int) $attribute['id'], $editingProduct['attribute_ids'] ?? [], true) ? 'checked' : ''; ?>>
                                <span><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></span>
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
</section>

<script>
    (function() {
        const addTierBtn = document.getElementById('add-tier');
        const tierContainer = document.getElementById('tier-fields');

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
                    <input name="tier_price[]" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
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

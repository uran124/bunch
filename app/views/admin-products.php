<?php /** @var array $products */ ?>
<?php /** @var array $filters */ ?>
<?php /** @var array $supplies */ ?>
<?php /** @var array $attributes */ ?>
<?php /** @var array|null $editingProduct */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Товары</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Товары', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Управляйте карточками товаров, привязанными к поставкам: характеристики из поставки, атрибуты, базовая цена и активность.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
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
                <?php elseif ($message === 'error'): ?>
                    <p>Карточка сохраняется только при заполнении обязательных полей.</p>
                <?php else: ?>
                    <p>Карточка обновляется с выбранными атрибутами и ценовыми уровнями.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr]">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Поиск по названию</span>
                <input type="search" placeholder="Например, Freedom или Пион" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Активность</span>
                <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <?php foreach ($filters['active'] as $option): ?>
                        <option><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Поставка</span>
                <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <option>Все</option>
                    <?php foreach ($filters['supplies'] as $supply): ?>
                        <option><?php echo htmlspecialchars($supply, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <span class="material-symbols-rounded mr-2 align-middle text-base">info</span>
            Привязка к поставке обязательна для обычных товаров; атрибуты добавляются ниже в карточке.
        </div>
    </div>

    <section id="product-form" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
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

        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">inventory_2</span>
                Быстрое действие
            </div>
            <p>Создайте товар прямо из карточки поставки — сорт, цвет, высота и страна подтянутся автоматически в черновик.</p>
            <div class="flex flex-wrap gap-2">
                <a href="/?page=admin-supplies" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">playlist_add</span>
                    Создать из поставки
                </a>
                <a href="#product-form" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">download_done</span>
                    Импорт CSV
                </a>
            </div>
            <ul class="list-disc space-y-1 pl-4 text-sm text-emerald-800">
                <li>Базовая цена и уровни стоимости от количества.</li>
                <li>Атрибуты привязываются галочками, варианты подтягиваются из справочника.</li>
                <li>Фото и артикул добавляются только в карточке товара.</li>
            </ul>
        </div>
    </section>

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
                    <a href="/?page=admin-products&edit_id=<?php echo (int) $product['id']; ?>#product-form" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
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

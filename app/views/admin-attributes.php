<?php /** @var array $attributes */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Атрибуты</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Атрибуты', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">На этой странице можно добавить новый атрибут и управлять его значениями (редактировать/удалять) через список и модальное окно.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin-products" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">shopping_bag</span>
                Товары
            </a>
            <a href="/admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="flex items-start gap-3 rounded-xl border <?php echo $message === 'error' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'; ?> px-4 py-3 text-sm">
            <span class="material-symbols-rounded text-lg"><?php echo $message === 'error' ? 'error' : 'check_circle'; ?></span>
            <div>
                <p class="font-semibold"><?php echo $message === 'error' ? 'Не все обязательные поля заполнены.' : 'Изменения сохранены.'; ?></p>
                <p>Данные атрибутов и значений обновлены.</p>
            </div>
        </div>
    <?php endif; ?>

    <form action="/admin-attribute-save" method="post" class="space-y-3 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">add</span>
                Новый атрибут
            </div>
            <p>Заполните основные поля, чтобы добавить атрибут и позже прикрепить варианты.</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Название
                    <input name="name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="Например, Высота стебля">
                </label>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Тип
                    <select name="type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                        <option value="selector">selector</option>
                        <option value="toggle">toggle</option>
                        <option value="color">color</option>
                        <option value="text">text</option>
                        <option value="number">number</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Относится к
                    <select name="applies_to" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                        <option value="stem">стеблю (умножать на количество)</option>
                        <option value="bouquet">букету (фиксированная цена)</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Порядок в карточке товара
                    <input name="sort_order" type="number" value="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                </label>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700 sm:col-span-2">
                    Описание
                    <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="Пояснение для менеджеров"></textarea>
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" checked>
                    Активен
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200">
                    <span class="material-symbols-rounded text-base">save</span>
                    Сохранить атрибут
                </button>
            </div>
    </form>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Значения атрибутов</p>
                <h2 class="text-xl font-semibold text-slate-900">Редактор вариантов</h2>
            </div>
            <a href="/admin-products" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                <span class="material-symbols-rounded text-base">shoppingmode</span>
                Привязать к товару
            </a>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <?php foreach ($attributes as $attribute): ?>
                <?php $attributeId = (int) $attribute['id']; ?>
                <article id="attribute-<?php echo $attributeId; ?>" class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-3 border-b border-slate-200 pb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Атрибут #<?php echo $attributeId; ?></p>
                            <h3 class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <div class="space-y-1 text-xs text-slate-500">
                            <div>
                                <?php echo htmlspecialchars($attribute['type'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo $attribute['applies_to'] === 'bouquet' ? 'к букету' : 'к стеблю'; ?>
                            </div>
                            <form action="/admin-attribute-save" method="post" class="flex items-center gap-2">
                                <input type="hidden" name="id" value="<?php echo $attributeId; ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="description" value="<?php echo htmlspecialchars($attribute['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($attribute['type'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="applies_to" value="<?php echo htmlspecialchars($attribute['applies_to'] ?? 'stem', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="is_active" value="<?php echo (int) ($attribute['is_active'] ?? 0); ?>">
                                <label class="inline-flex items-center gap-1.5">
                                    Порядок:
                                    <input type="number" name="sort_order" value="<?php echo (int) ($attribute['sort_order'] ?? 0); ?>" class="w-16 rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-900">
                                </label>
                                <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700">
                                    <span class="material-symbols-rounded text-sm">save</span>
                                    Сохранить
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <?php foreach ($attribute['values'] as $value): ?>
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="text-xs text-slate-500">Δ <?php echo htmlspecialchars((string) $value['price_delta'], ENT_QUOTES, 'UTF-8'); ?> ₽ · порядок <?php echo (int) $value['sort_order']; ?><?php echo !empty($value['is_default']) ? ' · по умолчанию' : ''; ?></p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="attribute-value-edit inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700"
                                            data-id="<?php echo (int) $value['id']; ?>"
                                            data-attribute-id="<?php echo $attributeId; ?>"
                                            data-attribute-name="<?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-value="<?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-price-delta="<?php echo htmlspecialchars((string) $value['price_delta'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-photo-url="<?php echo htmlspecialchars($value['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-sort-order="<?php echo (int) $value['sort_order']; ?>"
                                            data-is-active="<?php echo (int) ($value['is_active'] ?? 0); ?>"
                                            data-is-default="<?php echo (int) ($value['is_default'] ?? 0); ?>"
                                        >
                                            <span class="material-symbols-rounded text-base">edit</span>
                                            Редактировать
                                        </button>
                                        <button type="submit" form="delete-value-<?php echo (int) $value['id']; ?>" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 font-semibold text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                                            <span class="material-symbols-rounded text-base">delete</span>
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <form id="delete-value-<?php echo (int) $value['id']; ?>" action="/admin-attribute-value-delete" method="post" class="hidden">
                                <input type="hidden" name="id" value="<?php echo (int) $value['id']; ?>">
                                <input type="hidden" name="attribute_id" value="<?php echo $attributeId; ?>">
                            </form>
                        <?php endforeach; ?>
                        <form action="/admin-attribute-value-save" method="post" class="rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-3 text-sm text-slate-700">
                            <input type="hidden" name="attribute_id" value="<?php echo $attributeId; ?>">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Добавить вариант</p>
                            <div class="grid gap-2 sm:grid-cols-[1.6fr_1fr_120px] sm:items-center">
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Название
                                    <input name="value" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="Например, 40 см" required>
                                </label>
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Дельта, ₽
                                    <input name="price_delta" type="number" step="1" value="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                </label>
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Фото (URL)
                                    <input name="photo_url" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="https://...">
                                </label>
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Порядок
                                    <input name="sort_order" type="number" value="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                </label>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-4">
                                    <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                                        <input type="checkbox" name="is_active" class="peer sr-only" checked>
                                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:shadow-md"></span>
                                        <span class="sr-only">Активен</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                                        <input type="checkbox" name="is_default" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Главный (по умолчанию)
                                    </label>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white shadow-sm shadow-emerald-200 hover:-translate-y-0.5 hover:shadow-md">
                                    <span class="material-symbols-rounded text-base">add_circle</span>
                                    Добавить новое значение
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<dialog id="attribute-value-modal" class="w-full max-w-2xl rounded-2xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/50">
    <form action="/admin-attribute-value-save" method="post" enctype="multipart/form-data" class="space-y-4 p-5">
        <input type="hidden" name="attribute_id" id="modal-attribute-id">
        <input type="hidden" name="id" id="modal-value-id">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Редактирование</p>
                <h3 id="modal-title" class="text-lg font-semibold text-slate-900">Значение атрибута</h3>
            </div>
            <button type="button" id="attribute-value-modal-close" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800">
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700 sm:col-span-2">
                Название значения
                <input id="modal-value" name="value" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
            </label>
            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                Дельта, ₽
                <input id="modal-price-delta" name="price_delta" type="number" step="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
            </label>
            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                Порядок
                <input id="modal-sort-order" name="sort_order" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
            </label>
            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700 sm:col-span-2">
                Фото (URL)
                <input id="modal-photo-url" name="photo_url" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="https://...">
            </label>
            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700 sm:col-span-2">
                Загрузить фото
                <input name="photo_file" type="file" accept="image/*" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                <input id="modal-is-active" type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Значение активно
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                <input id="modal-is-default" type="checkbox" name="is_default" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Главный вариант (выбирается по умолчанию)
            </label>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" id="attribute-value-modal-cancel" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Отмена</button>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить
            </button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        const modal = document.getElementById('attribute-value-modal');
        if (!modal) {
            return;
        }

        const fieldAttributeId = document.getElementById('modal-attribute-id');
        const fieldValueId = document.getElementById('modal-value-id');
        const fieldValue = document.getElementById('modal-value');
        const fieldPriceDelta = document.getElementById('modal-price-delta');
        const fieldPhotoUrl = document.getElementById('modal-photo-url');
        const fieldSortOrder = document.getElementById('modal-sort-order');
        const fieldIsActive = document.getElementById('modal-is-active');
        const fieldIsDefault = document.getElementById('modal-is-default');
        const title = document.getElementById('modal-title');
        const closeBtn = document.getElementById('attribute-value-modal-close');
        const cancelBtn = document.getElementById('attribute-value-modal-cancel');

        document.querySelectorAll('.attribute-value-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                fieldAttributeId.value = button.dataset.attributeId || '';
                fieldValueId.value = button.dataset.id || '';
                fieldValue.value = button.dataset.value || '';
                fieldPriceDelta.value = button.dataset.priceDelta || '0';
                fieldPhotoUrl.value = button.dataset.photoUrl || '';
                fieldSortOrder.value = button.dataset.sortOrder || '0';
                fieldIsActive.checked = button.dataset.isActive === '1';
                fieldIsDefault.checked = button.dataset.isDefault === '1';
                title.textContent = 'Редактировать: ' + (button.dataset.attributeName || 'значение атрибута');
                modal.showModal();
            });
        });

        [closeBtn, cancelBtn].forEach(function (button) {
            if (!button) {
                return;
            }

            button.addEventListener('click', function () {
                modal.close();
            });
        });
    }());
</script>

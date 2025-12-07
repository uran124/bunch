<?php /** @var array $attributes */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Атрибуты</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Атрибуты', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Управляйте справочником атрибутов (высота стебля, вид оформления и т.д.) и значениями атрибутов с дельтой цены и фото.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-products" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">shopping_bag</span>
                Товары
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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
                <p>Можно редактировать атрибуты и значения без перезагрузки страницы.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Справочник атрибутов</p>
            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="grid grid-cols-[80px_1.6fr_1fr_1fr] items-center gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <span>ID</span>
                    <span>Название</span>
                    <span>Тип</span>
                    <span class="text-right">Активность</span>
                </div>
                <?php foreach ($attributes as $attribute): ?>
                    <article class="grid grid-cols-[80px_1.6fr_1fr_1fr] items-center gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0">
                        <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $attribute['id']; ?></div>
                        <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-sm text-slate-700"><?php echo htmlspecialchars($attribute['type'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="flex items-center justify-end gap-2">
                            <label class="relative inline-flex h-8 w-14 cursor-not-allowed items-center opacity-60">
                                <input type="checkbox" class="peer sr-only" <?php echo ($attribute['is_active'] ?? 0) ? 'checked' : ''; ?> disabled>
                                <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                <span class="sr-only">Активен</span>
                            </label>
                            <a href="#attribute-<?php echo (int) $attribute['id']; ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                                <span class="material-symbols-rounded text-base">edit</span>
                                Править
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <form action="/?page=admin-attribute-save" method="post" class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
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
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Значения атрибутов</p>
                <h2 class="text-xl font-semibold text-slate-900">Редактор вариантов</h2>
            </div>
            <a href="/?page=admin-products" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                <span class="material-symbols-rounded text-base">shoppingmode</span>
                Привязать к товару
            </a>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <?php foreach ($attributes as $attribute): ?>
                <?php $attributeId = (int) $attribute['id']; ?>
                <article id="attribute-<?php echo $attributeId; ?>" class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                    <form action="/?page=admin-attribute-save" method="post" class="space-y-3">
                        <input type="hidden" name="id" value="<?php echo $attributeId; ?>">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex-1 space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Атрибут</p>
                                <div class="grid gap-2 sm:grid-cols-[2fr_1fr]">
                                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                        Название
                                        <input name="name" value="<?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" required>
                                    </label>
                                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                        Тип
                                        <select name="type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                            <?php foreach (['selector', 'toggle', 'color', 'text', 'number'] as $type): ?>
                                                <option value="<?php echo $type; ?>" <?php echo $attribute['type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                </div>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Описание
                                    <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="Комментарий для менеджеров"><?php echo htmlspecialchars($attribute['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </label>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <label class="relative inline-flex h-8 w-14 cursor-pointer items-center">
                                    <input type="checkbox" name="is_active" class="peer sr-only" <?php echo ($attribute['is_active'] ?? 0) ? 'checked' : ''; ?>>
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                    <span class="sr-only">Активен</span>
                                </label>
                                <div class="flex flex-wrap gap-2 text-sm font-semibold">
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700">
                                        <span class="material-symbols-rounded text-base">save</span>
                                        Сохранить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="mt-4 space-y-2">
                        <?php foreach ($attribute['values'] as $value): ?>
                            <form action="/?page=admin-attribute-value-save" method="post" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <input type="hidden" name="attribute_id" value="<?php echo $attributeId; ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $value['id']; ?>">
                                <div class="grid gap-2 sm:grid-cols-[1.4fr_1fr_1fr_80px] sm:items-center">
                                    <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                        Название
                                        <input name="value" value="<?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" required>
                                    </label>
                                    <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                        Дельта, ₽
                                        <input name="price_delta" type="number" step="0.01" value="<?php echo htmlspecialchars($value['price_delta'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                    </label>
                                    <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                        Фото (URL)
                                        <input name="photo_url" value="<?php echo htmlspecialchars($value['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="https://...">
                                    </label>
                                    <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                        Порядок
                                        <input name="sort_order" type="number" value="<?php echo (int) $value['sort_order']; ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
                                    </label>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                    <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                                        <input type="checkbox" name="is_active" class="peer sr-only" <?php echo ($value['is_active'] ?? 0) ? 'checked' : ''; ?>>
                                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:shadow-md"></span>
                                        <span class="sr-only">Активен</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700">
                                            <span class="material-symbols-rounded text-base">save</span>
                                            Сохранить вариант
                                        </button>
                                        <button type="submit" form="delete-value-<?php echo (int) $value['id']; ?>" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 font-semibold text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                                            <span class="material-symbols-rounded text-base">delete</span>
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <form id="delete-value-<?php echo (int) $value['id']; ?>" action="/?page=admin-attribute-value-delete" method="post" class="hidden">
                                <input type="hidden" name="id" value="<?php echo (int) $value['id']; ?>">
                                <input type="hidden" name="attribute_id" value="<?php echo $attributeId; ?>">
                            </form>
                        <?php endforeach; ?>
                        <form action="/?page=admin-attribute-value-save" method="post" class="rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-3 text-sm text-slate-700">
                            <input type="hidden" name="attribute_id" value="<?php echo $attributeId; ?>">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Добавить вариант</p>
                            <div class="grid gap-2 sm:grid-cols-[1.4fr_1fr_1fr_80px] sm:items-center">
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Название
                                    <input name="value" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm" placeholder="Например, 40 см" required>
                                </label>
                                <label class="flex flex-col gap-1 font-semibold text-slate-700">
                                    Дельта, ₽
                                    <input name="price_delta" type="number" step="0.01" value="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm">
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
                                <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                                    <input type="checkbox" name="is_active" class="peer sr-only" checked>
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:shadow-md"></span>
                                    <span class="sr-only">Активен</span>
                                </label>
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white shadow-sm shadow-emerald-200 hover:-translate-y-0.5 hover:shadow-md">
                                    <span class="material-symbols-rounded text-base">add_circle</span>
                                    Добавить вариант
                                </button>
                            </div>
                        </form>
                        <form action="/?page=admin-attribute-delete" method="post" class="flex justify-end">
                            <input type="hidden" name="id" value="<?php echo $attributeId; ?>">
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                                <span class="material-symbols-rounded text-base">delete</span>
                                Удалить атрибут
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

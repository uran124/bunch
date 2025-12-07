<?php /** @var array $attributes */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

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
                            <label class="relative inline-flex h-8 w-14 cursor-pointer items-center">
                                <input type="checkbox" class="peer sr-only" <?php echo $attribute['active'] ? 'checked' : ''; ?>>
                                <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                <span class="sr-only">Активен</span>
                            </label>
                            <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                                <span class="material-symbols-rounded text-base">edit</span>
                                Править
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">add</span>
                Быстрое действие
            </div>
            <p>Создайте новый атрибут: название, опциональное описание, тип отображения (selector/toggle/color и др.), активность.</p>
            <div class="flex flex-wrap gap-2">
                <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">auto_awesome_motion</span>
                    Новый атрибут
                </a>
                <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">upload</span>
                    Импорт вариантов
                </a>
            </div>
        </div>
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
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Атрибут</p>
                            <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="text-sm text-slate-500">Тип: <?php echo htmlspecialchars($attribute['type'], ENT_QUOTES, 'UTF-8'); ?> · Вариантов: <?php echo count($attribute['values']); ?></p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200"><?php echo $attribute['active'] ? 'Активен' : 'Отключен'; ?></span>
                    </div>
                    <div class="mt-3 space-y-2">
                        <?php foreach ($attribute['values'] as $value): ?>
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="space-y-0.5">
                                        <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($value['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-xs text-slate-500">Дельта: <?php echo htmlspecialchars($value['priceDelta'], ENT_QUOTES, 'UTF-8'); ?> · Фото: <?php echo htmlspecialchars($value['photo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                                        <input type="checkbox" class="peer sr-only" <?php echo $value['active'] ? 'checked' : ''; ?>>
                                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:shadow-md"></span>
                                        <span class="sr-only">Активен</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

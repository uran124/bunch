<?php /** @var array $products */ ?>
<?php /** @var array $filters */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

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

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_120px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Наименование</span>
            <span>Поставка</span>
            <span>Характеристики</span>
            <span>Цена/Статус</span>
            <span class="text-right">Действия</span>
        </div>
        <?php foreach ($products as $product): ?>
            <article class="grid grid-cols-[80px_1.2fr_1fr_1fr_1fr_120px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $product['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Атрибуты: <?php echo $product['hasAttributes'] ? 'есть' : 'нет'; ?></div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold text-rose-700"><?php echo htmlspecialchars($product['supply'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-slate-500">Цвет: <?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?> · Высота: <?php echo htmlspecialchars($product['height'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="text-sm text-slate-700">
                    <div>Создан: <?php echo htmlspecialchars($product['createdAt'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>Изменен: <?php echo htmlspecialchars($product['updatedAt'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <label class="relative inline-flex h-9 w-16 cursor-pointer items-center">
                        <input type="checkbox" class="peer sr-only" <?php echo $product['active'] ? 'checked' : ''; ?>>
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                        <span class="absolute left-1 top-1 h-7 w-7 rounded-full bg-white shadow-sm transition peer-checked:translate-x-7 peer-checked:shadow-md"></span>
                        <span class="sr-only">Активен</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Редактировать
                    </a>
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-rose-700 hover:-translate-y-0.5 hover:border-rose-200">
                        <span class="material-symbols-rounded text-base">delete</span>
                        Удалить
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Подсказка</p>
            <h2 class="text-xl font-semibold text-slate-900">Что хранится в карточке товара</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Привязка к поставке с характеристиками «Наименование», «Сорт», «Цвет», «Страна», «Высота стебля», «Вес стебля».</li>
                <li>Клиентские поля: базовая цена, основное фото, описание.</li>
                <li>Список выбранных атрибутов и вариантов с дельтой цены и фото.</li>
                <li>Переключатель активности и история изменений.</li>
            </ul>
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
                <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">download_done</span>
                    Импорт CSV
                </a>
            </div>
        </div>
    </section>
</section>

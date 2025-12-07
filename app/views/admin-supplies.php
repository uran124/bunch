<?php /** @var array $supplies */ ?>
<?php /** @var array $reservations */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Поставки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Управление расписанием поставок, сортами, количеством пачек и мелким оптом. Здесь же создаются черновики товаров из поставки.</p>
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

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr]">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Дата поставки</span>
                <input type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Статус</span>
                <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <option>Все</option>
                    <option>Планируется</option>
                    <option>Пришла</option>
                    <option>Закрыта</option>
                </select>
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Стендинг</span>
                <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <option>Все</option>
                    <option>Только стендинг</option>
                    <option>Разовые поставки</option>
                </select>
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Мелкий опт</span>
                <select class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <option>Все</option>
                    <option>Разрешён</option>
                    <option>Запрещён</option>
                </select>
            </label>
        </div>
        <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <span class="material-symbols-rounded mr-2 align-middle text-base">inventory</span>
            Для учёта мелкого опта фиксируйте количество пачек (всего/доступно) и разрешение на бронирование. Черновик товара можно создать прямо из строки поставки.
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[80px_1.3fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Название и дата</span>
            <span>Сорт/Страна</span>
            <span>Пачки</span>
            <span>Статусы</span>
            <span class="text-right">Действия</span>
        </div>
        <?php foreach ($supplies as $supply): ?>
            <article class="grid grid-cols-[80px_1.3fr_1fr_1fr_1fr_140px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $supply['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($supply['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Дата: <?php echo htmlspecialchars($supply['date'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="text-sm text-slate-700">
                    <div>Сорт: <?php echo htmlspecialchars($supply['sort'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>Страна: <?php echo htmlspecialchars($supply['country'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div>Всего: <?php echo (int) $supply['packsTotal']; ?> пачек (<?php echo (int) $supply['packSize']; ?> шт.)</div>
                    <div>Доступно: <?php echo (int) $supply['packsAvailable']; ?> · Стебель: <?php echo htmlspecialchars($supply['height'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-800 ring-1 ring-slate-200"><?php echo htmlspecialchars($supply['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if ($supply['isStanding']): ?>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 ring-1 ring-indigo-200">Стендинг</span>
                        <?php endif; ?>
                        <?php if ($supply['smallWholesale']): ?>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200">Мелкий опт</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-slate-500">Вес стебля: <?php echo htmlspecialchars($supply['weight'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">add_circle</span>
                        Товар из поставки
                    </a>
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Карточка
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Брони мелкого опта</p>
            <h2 class="text-xl font-semibold text-slate-900">Сводка резервов по поставкам</h2>
            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <span>Поставка</span>
                    <span>Клиент</span>
                    <span>Пачек</span>
                    <span>Статус</span>
                </div>
                <?php foreach ($reservations as $reservation): ?>
                    <article class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0">
                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($reservation['supply'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-sm text-slate-700"><?php echo htmlspecialchars($reservation['client'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-sm text-slate-700"><?php echo (int) $reservation['packs']; ?></div>
                        <div class="text-sm text-rose-700"><?php echo htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">summarize</span>
                Подсказка
            </div>
            <p>Карточка поставки включает сорт, цвет, высоту, страну, вес стебля, размер пачки и разрешение мелкого опта. Список товаров из поставки доступен внизу карточки.</p>
            <div class="flex flex-wrap gap-2">
                <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">history</span>
                    История изменений
                </a>
                <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">add_task</span>
                    Создать стендинг
                </a>
            </div>
        </div>
    </section>
</section>

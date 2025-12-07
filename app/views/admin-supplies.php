<?php /** @var array $supplies */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Поставки', ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="#standing-form" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Добавить стендинг
            </a>
            <a href="#single-form" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">calendar_add_on</span>
                Разовая поставка
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message) && $message === 'created'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="material-symbols-rounded text-lg">check_circle</span>
            <div>
                <p class="font-semibold">Поставка сохранена.</p>
                <p>Карточка попадёт в список запланированных поставок со следующей датой поставки.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'error'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <span class="material-symbols-rounded text-lg">error</span>
            <div>
                <p class="font-semibold">Заполните обязательные поля.</p>
                <p>Название, сорт, количество пачек, стеблей в пачке и дата поставки должны быть указаны.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid gap-5 lg:grid-cols-2">
        <form id="standing-form" action="/?page=admin-supply-standing" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Стендинг</p>
                    <h2 class="text-xl font-semibold text-slate-900">Добавить стендинг</h2>
                    <p class="text-sm text-slate-500">Периодическая поставка с расчётом ближайшей даты по первой поставке.</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 ring-1 ring-indigo-200">еженедельно/раз в 2 недели</span>
            </div>
            <div class="grid gap-3 lg:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Фото (URL)
                    <input name="photo_url" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="https://...">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Название цветка
                    <input name="flower_name" type="text" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="роза">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сорт цветка
                    <input name="variety" type="text" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Rhodos">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Страна происхождения
                    <input name="country" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Эквадор">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сколько пачек
                    <input name="packs_total" type="number" min="1" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="60">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сколько стеблей в пачке
                    <input name="stems_per_pack" type="number" min="1" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="25">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Высота стебля (см)
                    <input name="stem_height_cm" type="number" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="50">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Вес стебля (гр)
                    <input name="stem_weight_g" type="number" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="45">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Периодичность поставки
                    <select name="periodicity" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <option value="weekly">Раз в неделю</option>
                        <option value="biweekly">Раз в две недели</option>
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День первой поставки
                    <input name="first_delivery_date" type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День фактической поставки
                    <input name="actual_delivery_date" type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Дата пропуска поставки
                    <input name="skip_date" type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input name="allow_small_wholesale" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Возможность купить мелким оптом
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">save</span>
                    Сохранить стендинг
                </button>
            </div>
        </form>

        <form id="single-form" action="/?page=admin-supply-single" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Разовая поставка</p>
                    <h2 class="text-xl font-semibold text-slate-900">Добавить разовую поставку</h2>
                    <p class="text-sm text-slate-500">Однократная поставка с фиксированной датой и возможностью мелкого опта.</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-800 ring-1 ring-slate-200">по дате</span>
            </div>
            <div class="grid gap-3 lg:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Фото (URL)
                    <input name="photo_url" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="https://...">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Название цветка
                    <input name="flower_name" type="text" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="хризантема">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сорт цветка
                    <input name="variety" type="text" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Altaj">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Страна происхождения
                    <input name="country" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Колумбия">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сколько пачек
                    <input name="packs_total" type="number" min="1" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="30">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Сколько стеблей в пачке
                    <input name="stems_per_pack" type="number" min="1" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="10">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Высота стебля (см)
                    <input name="stem_height_cm" type="number" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="40">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Вес стебля (гр)
                    <input name="stem_weight_g" type="number" min="0" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="32">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День поставки
                    <input name="planned_delivery_date" type="date" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День фактической поставки
                    <input name="actual_delivery_date" type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input name="allow_small_wholesale" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Возможность купить мелким оптом
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">save</span>
                    Сохранить поставку
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Список ближайших поставок</p>
                <h2 class="text-lg font-semibold text-slate-900">План по датам и доступным пачкам</h2>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span class="material-symbols-rounded text-base text-emerald-500">deployed_code</span> Активные карточки
                <span class="material-symbols-rounded text-base text-slate-300">deployed_code</span> Создать карточку
            </div>
        </div>
        <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr_110px] items-center gap-4 border-b border-slate-100 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Название</span>
            <span>Сорт / Страна</span>
            <span>Пачки</span>
            <span>Дата поставки</span>
            <span class="text-right">Карточки</span>
        </div>
        <?php if (empty($supplies)): ?>
            <div class="px-5 py-4 text-sm text-slate-600">Добавьте стендинг или разовую поставку, чтобы увидеть расписание.</div>
        <?php else: ?>
            <?php foreach ($supplies as $supply): ?>
                <?php
                $title = trim(($supply['flower_name'] ?? '') . ' ' . ($supply['variety'] ?? ''));
                $packText = (int) ($supply['packs_total'] ?? 0) . '/' . (int) ($supply['packs_available'] ?? 0) . ' (' . (int) ($supply['pack_size'] ?? 0) . ' шт.)';
                $dateLabel = $supply['next_delivery'] ?: '—';
                $isStanding = !empty($supply['is_standing']);
                $hasProduct = !empty($supply['has_product_card']);
                $hasWholesale = !empty($supply['has_wholesale_card']);
                ?>
                <article class="grid grid-cols-[1.2fr_1fr_1fr_1fr_110px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="space-y-1">
                        <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-sm text-slate-500"><?php echo $isStanding ? 'Стендинг' : 'Разовая поставка'; ?></div>
                    </div>
                    <div class="text-sm text-slate-700">
                        <div>Сорт: <?php echo htmlspecialchars($supply['variety'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div>Страна: <?php echo htmlspecialchars($supply['country'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="space-y-1 text-sm text-slate-700">
                        <div>Всего/доступно: <?php echo htmlspecialchars($packText, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div>Стебель: <?php echo (int) ($supply['stem_height_cm'] ?? 0); ?> см · <?php echo (int) ($supply['stem_weight_g'] ?? 0); ?> г</div>
                    </div>
                    <div class="space-y-1 text-sm text-slate-700">
                        <div>Следующая: <?php echo htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php if (!empty($supply['actual_delivery_date'])): ?>
                            <div class="text-xs text-emerald-700">Фактическая дата указана</div>
                        <?php else: ?>
                            <div class="text-xs text-slate-500">По расписанию от первой поставки</div>
                        <?php endif; ?>
                        <?php if (!empty($supply['allow_small_wholesale'])): ?>
                            <div class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200">Мелкий опт</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-end gap-2 text-sm font-semibold">
                        <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700">
                            <span class="material-symbols-rounded text-base <?php echo $hasProduct ? 'text-emerald-500' : 'text-slate-300'; ?>">deployed_code</span>
                        </a>
                        <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700">
                            <span class="material-symbols-rounded text-base <?php echo $hasWholesale ? 'text-emerald-500' : 'text-slate-300'; ?>">group_work</span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

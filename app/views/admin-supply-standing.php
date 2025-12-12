<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Добавить стендинг', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500">Периодическая поставка с расчётом ближайшей даты по первой поставке.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-supply-single" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">event</span>
                Разовая поставка
            </a>
            <a href="/?page=admin-supplies" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К списку поставок
            </a>
        </div>
    </header>

    <?php if (!empty($message) && $message === 'error'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <span class="material-symbols-rounded text-lg">error</span>
            <div>
                <p class="font-semibold">Заполните обязательные поля.</p>
                <p>Название, сорт, количество пачек, стеблей в пачке и дата первой поставки должны быть указаны.</p>
            </div>
        </div>
    <?php endif; ?>

    <form id="standing-form" action="/?page=admin-supply-standing" method="post" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Стендинг</p>
                <h2 class="text-xl font-semibold text-slate-900">Добавить стендинг</h2>
            </div>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 ring-1 ring-indigo-200">еженедельно/раз в 2 недели</span>
        </div>
        <div class="grid gap-3 lg:grid-cols-2">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="photo_url" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="https://...">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Загрузить фото
                <input name="photo_file_standing" type="file" accept="image/*" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                <span class="text-xs font-normal text-slate-500">Изображение обрежется до квадрата и сохранится в WebP.</span>
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
                <input name="first_delivery_date" type="date" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Пропустить дату
                <input name="skip_date" type="date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                <span class="text-xs font-normal text-slate-500">Если поставка в праздничную неделю не требуется, пропустите конкретную дату.</span>
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
                Сохранить стендинг
            </button>
        </div>
    </form>
</section>

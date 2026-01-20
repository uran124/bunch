<?php /** @var array $supply */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $isStanding = !empty($supply['is_standing']); ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Редактирование поставки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500">Обновите параметры поставки, фото и даты. Тип поставки менять нельзя.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin-supplies" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К списку поставок
            </a>
        </div>
    </header>

    <?php if (!empty($message) && $message === 'saved'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="material-symbols-rounded text-lg">check_circle</span>
            <div>
                <p class="font-semibold">Изменения сохранены.</p>
                <p>Поставка обновлена, расписание пересчитано.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'error'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <span class="material-symbols-rounded text-lg">error</span>
            <div>
                <p class="font-semibold">Заполните обязательные поля.</p>
                <p>Название, сорт, количество коробок, пачек в коробке, стеблей и дата поставки должны быть указаны.</p>
            </div>
        </div>
    <?php endif; ?>

    <form action="/admin-supply-update" method="post" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500"><?php echo $isStanding ? 'Стендинг' : 'Разовая поставка'; ?></p>
                <h2 class="text-xl font-semibold text-slate-900">Параметры поставки</h2>
            </div>
            <?php if ($isStanding): ?>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 ring-1 ring-indigo-200">еженедельно/раз в 2 недели</span>
            <?php else: ?>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-amber-200">единоразово</span>
            <?php endif; ?>
        </div>
        <div class="grid gap-3 lg:grid-cols-2">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="photo_url" type="text" value="<?php echo htmlspecialchars($supply['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="https://...">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Загрузить фото
                <input name="photo_file" type="file" accept="image/*" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                <span class="text-xs font-normal text-slate-500">Изображение обрежется до квадрата и сохранится в WebP.</span>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название цветка
                <input name="flower_name" type="text" required value="<?php echo htmlspecialchars($supply['flower_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="роза">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Сорт цветка
                <input name="variety" type="text" required value="<?php echo htmlspecialchars($supply['variety'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Rhodos">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Страна происхождения
                <input name="country" type="text" value="<?php echo htmlspecialchars($supply['country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200" placeholder="Эквадор">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Сколько коробок
                <input name="boxes_total" type="number" min="1" required value="<?php echo (int) ($supply['boxes_total'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Сколько пачек в коробке
                <input name="packs_per_box" type="number" min="1" required value="<?php echo (int) ($supply['packs_per_box'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Сколько стеблей в пачке
                <input name="stems_per_pack" type="number" min="1" required value="<?php echo (int) ($supply['stems_per_pack'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Высота стебля (см)
                <input name="stem_height_cm" type="number" min="0" value="<?php echo (int) ($supply['stem_height_cm'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Вес стебля (гр)
                <input name="stem_weight_g" type="number" min="0" value="<?php echo (int) ($supply['stem_weight_g'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Размер бутона (см)
                <input name="bud_size_cm" type="number" min="0" value="<?php echo (int) ($supply['bud_size_cm'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700 lg:col-span-2">
                Описание поставки
                <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"><?php echo htmlspecialchars($supply['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <?php if ($isStanding): ?>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Периодичность поставки
                    <select name="periodicity" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <option value="weekly" <?php echo ($supply['periodicity'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Раз в неделю</option>
                        <option value="biweekly" <?php echo ($supply['periodicity'] ?? '') === 'biweekly' ? 'selected' : ''; ?>>Раз в две недели</option>
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День первой поставки
                    <input name="first_delivery_date" type="date" required value="<?php echo htmlspecialchars($supply['first_delivery_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Пропустить дату
                    <input name="skip_date" type="date" value="<?php echo htmlspecialchars($supply['skip_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <span class="text-xs font-normal text-slate-500">Если поставка в праздничную неделю не требуется, пропустите конкретную дату.</span>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День фактической поставки
                    <input name="actual_delivery_date" type="date" value="<?php echo htmlspecialchars($supply['actual_delivery_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            <?php else: ?>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День поставки
                    <input name="planned_delivery_date" type="date" required value="<?php echo htmlspecialchars($supply['planned_delivery_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    День фактической поставки
                    <input name="actual_delivery_date" type="date" value="<?php echo htmlspecialchars($supply['actual_delivery_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            <?php endif; ?>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input name="allow_small_wholesale" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?php echo !empty($supply['allow_small_wholesale']) ? 'checked' : ''; ?>>
                Возможность купить мелким оптом
            </label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input name="allow_box_order" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" <?php echo !empty($supply['allow_box_order']) ? 'checked' : ''; ?>>
                Возможность заказать коробку
            </label>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">save</span>
                Обновить поставку
            </button>
        </div>
    </form>
</section>

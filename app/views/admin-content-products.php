<?php /** @var array $products */ ?>
<?php /** @var array $attachments */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Контент · Товарные карточки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Контент для товаров', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Редактируйте тексты, медиа и подсказки для карточек товаров. Для точной информации используйте шаблоны описаний и наборы фото.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-products" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">inventory_2</span>
                Каталог
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[90px_1.5fr_1fr_1.2fr_1fr_140px] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Товар</span>
            <span>Фото</span>
            <span>SEO</span>
            <span>Обновлено</span>
            <span class="text-right">Действие</span>
        </div>
        <?php foreach ($products as $product): ?>
            <article class="grid grid-cols-[90px_1.5fr_1fr_1.2fr_1fr_140px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $product['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-xs text-slate-500">Редактор: <?php echo htmlspecialchars($product['owner'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-700">
                    <span class="material-symbols-rounded text-base text-rose-500">photo_camera</span>
                    <?php echo (int) $product['photos']; ?> файлов
                </div>
                <div class="text-sm text-slate-700"><?php echo htmlspecialchars($product['seo'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="space-y-1 text-sm text-slate-700">
                    <div><?php echo htmlspecialchars($product['updatedAt'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="material-symbols-rounded text-base">task_alt</span>
                        Готово к публикации
                    </span>
                </div>
                <div class="flex justify-end gap-2 text-sm font-semibold">
                    <a href="#" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                        <span class="material-symbols-rounded text-base">edit</span>
                        Изменить
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 lg:grid-cols-[1.2fr_1fr]">
        <article class="rounded-2xl border border-dashed border-rose-200 bg-rose-50 p-5 text-sm text-rose-800">
            <div class="flex items-center gap-2 text-rose-900">
                <span class="material-symbols-rounded text-base">collections_bookmark</span>
                <span class="font-semibold">Готовые материалы</span>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-white px-4 py-3 text-center shadow-sm ring-1 ring-rose-100">
                    <div class="text-2xl font-semibold text-rose-700"><?php echo (int) $attachments['photoPresets']; ?></div>
                    <div class="text-xs text-slate-500">сетапов фото</div>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 text-center shadow-sm ring-1 ring-rose-100">
                    <div class="text-2xl font-semibold text-rose-700"><?php echo (int) $attachments['descriptionTemplates']; ?></div>
                    <div class="text-xs text-slate-500">шаблонов описаний</div>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 text-center shadow-sm ring-1 ring-rose-100">
                    <div class="text-2xl font-semibold text-rose-700"><?php echo (int) $attachments['attributes']; ?></div>
                    <div class="text-xs text-slate-500">атрибутов с текстами</div>
                </div>
            </div>
            <p class="mt-3 text-slate-700">Используйте шаблоны, чтобы ускорить публикацию и сохранить единый тон описаний.</p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Рекомендации</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">Что проверять перед публикацией</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Главное фото + минимум два ракурса для карточки.</li>
                <li>Укажите выгоды: стойкость в вазе, рекомендации по уходу и упаковка.</li>
                <li>Проверьте, что SEO-текст уникален для каждой позиции.</li>
            </ul>
            <a href="#" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                <span class="material-symbols-rounded text-base">content_paste</span>
                Шаблон описания
            </a>
        </article>
    </section>
</section>

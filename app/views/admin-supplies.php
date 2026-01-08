<?php /** @var array $supplies */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Поставки', ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-supply-standing" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Добавить стендинг
            </a>
            <a href="/?page=admin-supply-single" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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
    <?php elseif (!empty($message) && $message === 'card-activated'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="material-symbols-rounded text-lg">deployed_code</span>
            <div>
                <p class="font-semibold">Карточка активирована.</p>
                <p>Позиция доступна на витрине или в мелкооптовом блоке.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'card-deactivated'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <span class="material-symbols-rounded text-lg">do_not_disturb_on</span>
            <div>
                <p class="font-semibold">Карточка деактивирована.</p>
                <p>Товар скрыт с витрины или из мелкооптового списка.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'notfound'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <span class="material-symbols-rounded text-lg">error</span>
            <div>
                <p class="font-semibold">Поставка не найдена.</p>
                <p>Обновите список и попробуйте снова.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Список ближайших поставок</p>
                <h2 class="text-lg font-semibold text-slate-900">План по датам и доступным пачкам</h2>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 ring-1 ring-emerald-200">
                    <span class="material-symbols-rounded text-base">local_florist</span>
                    Розница
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-indigo-700 ring-1 ring-indigo-200">
                    <span class="material-symbols-rounded text-base">deployed_code</span>
                    Мелкий опт
                </span>
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
                $retailCreateHref = '/?page=admin-product-form&create_from_supply=' . (int) $supply['id'];
                $wholesaleCreateHref = '/?page=admin-product-form&create_from_supply=' . (int) $supply['id'];
                ?>
                <article id="supply-<?php echo (int) $supply['id']; ?>" class="grid grid-cols-[1.2fr_1fr_1fr_1fr_110px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="space-y-1">
                        <a href="/?page=admin-supply-edit&id=<?php echo (int) $supply['id']; ?>" class="text-base font-semibold text-slate-900 underline-offset-4 hover:text-emerald-700 hover:underline">
                            <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
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
                        <?php if ($hasWholesale): ?>
                            <form action="/?page=admin-supply-toggle-card" method="post">
                                <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                <input type="hidden" name="card_type" value="wholesale">
                                <input type="hidden" name="activate" value="0">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="Деактивировать мелкооптовую карточку">
                                    <span class="material-symbols-rounded text-base text-emerald-500">deployed_code</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($wholesaleCreateHref, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="Создать мелкооптовую карточку">
                                <span class="material-symbols-rounded text-base text-slate-300">deployed_code</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($hasProduct): ?>
                            <form action="/?page=admin-supply-toggle-card" method="post">
                                <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                <input type="hidden" name="card_type" value="retail">
                                <input type="hidden" name="activate" value="0">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="Деактивировать розничную карточку">
                                    <span class="material-symbols-rounded text-base text-emerald-500">local_florist</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($retailCreateHref, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="Создать розничную карточку">
                                <span class="material-symbols-rounded text-base text-slate-300">local_florist</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

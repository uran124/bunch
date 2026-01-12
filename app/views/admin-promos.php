<?php /** @var array $activeLots */ ?>
<?php /** @var array $finishedLots */ ?>
<?php /** @var array $promoItems */ ?>
<?php /** @var array $lotteries */ ?>
<?php /** @var array $loadErrors */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $lotterySettings = $lotterySettings ?? []; ?>
<?php $promoItems = $promoItems ?? []; ?>
<?php $lotteries = $lotteries ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-col items-start gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Акции</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-auction-create" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Лот аукциона
            </a>
            <a href="/?page=admin-promo-item-create" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">hourglass_top</span>
                Лимитированный товар
            </a>
            <a href="/?page=admin-lottery-create" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">celebration</span>
                Товар для розыгрыша
            </a>
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
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo $message === 'saved' ? 'Изменения сохранены.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($loadErrors)): ?>
        <?php
        $loadErrorLabels = [
            'auctions' => 'аукционы',
        ];
        $visibleErrors = array_values(array_intersect_key($loadErrorLabels, array_flip($loadErrors)));
        ?>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Не удалось загрузить разделы: <?php echo htmlspecialchars(implode(', ', $visibleErrors), ENT_QUOTES, 'UTF-8'); ?>. Проверьте логи сервера.
        </div>
    <?php endif; ?>

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Товары по акции</p>
                <h2 class="text-xl font-semibold text-slate-900">Лимитированные предложения</h2>
            </div>
            <p class="text-sm text-slate-500">Кликните по названию, чтобы отредактировать товар.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-amber-50/60 ring-1 ring-transparent">
            <div class="grid grid-cols-[80px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span>ID</span>
                <span>Товар</span>
                <span>Активность</span>
                <span>Окончание</span>
                <span>Цена</span>
            </div>
            <?php if (empty($promoItems)): ?>
                <div class="px-5 py-4 text-sm text-slate-500">Акционные товары ещё не добавлены.</div>
            <?php endif; ?>
            <?php foreach ($promoItems as $item): ?>
                <article class="grid grid-cols-[80px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $item['id']; ?></div>
                    <div class="space-y-1">
                        <a class="text-base font-semibold text-slate-900 transition hover:text-amber-600" href="/?page=admin-promo-item-edit&id=<?php echo (int) $item['id']; ?>">
                            <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="text-sm text-slate-500"><?php echo htmlspecialchars($item['label'] ?? 'Разовая акция', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="text-sm font-semibold text-slate-700"><?php echo !empty($item['is_active']) ? 'Активен' : 'Скрыт'; ?></div>
                    <div class="text-sm text-slate-700"><?php echo !empty($item['ends_at']) ? htmlspecialchars($item['ends_at'], ENT_QUOTES, 'UTF-8') : 'Без ограничения'; ?></div>
                    <div class="text-sm font-semibold text-slate-900"><?php echo number_format((float) $item['price'], 2, '.', ' '); ?> ₽</div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Розыгрыши</p>
                <h2 class="text-xl font-semibold text-slate-900">Участия в розыгрышах</h2>
            </div>
            <p class="text-sm text-slate-500">Кликните по названию, чтобы отредактировать розыгрыш.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-violet-50/60 ring-1 ring-transparent">
            <div class="grid grid-cols-[80px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span>ID</span>
                <span>Розыгрыш</span>
                <span>Статус</span>
                <span>Дата</span>
                <span>Билеты</span>
            </div>
            <?php if (empty($lotteries)): ?>
                <div class="px-5 py-4 text-sm text-slate-500">Розыгрыши ещё не добавлены.</div>
            <?php endif; ?>
            <?php foreach ($lotteries as $lottery): ?>
                <article class="grid grid-cols-[80px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $lottery['id']; ?></div>
                    <div class="space-y-1">
                        <a class="text-base font-semibold text-slate-900 transition hover:text-violet-600" href="/?page=admin-lottery-edit&id=<?php echo (int) $lottery['id']; ?>">
                            <?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="text-sm text-slate-500"><?php echo htmlspecialchars($lottery['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($lottery['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-700"><?php echo htmlspecialchars($lottery['draw_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm font-semibold text-slate-900"><?php echo (int) $lottery['tickets_paid']; ?>/<?php echo (int) $lottery['tickets_total']; ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Активные лоты</p>
                <h2 class="text-xl font-semibold text-slate-900">Лоты в работе</h2>
            </div>
            <p class="text-sm text-slate-500">Кликните по названию, чтобы отредактировать лот.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span>ID</span>
                <span>Лот</span>
                <span>Статус</span>
                <span>Период</span>
                <span>Текущая ставка</span>
                <span>Блиц / победитель</span>
            </div>
            <?php if (empty($activeLots)): ?>
                <div class="px-5 py-4 text-sm text-slate-500">Активные лоты ещё не добавлены.</div>
            <?php endif; ?>
            <?php foreach ($activeLots as $auction): ?>
                <?php $currentPrice = (float) ($auction['current_price'] ?? 0); ?>
                <article class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $auction['id']; ?></div>
                    <div class="space-y-1">
                        <a class="text-base font-semibold text-slate-900 transition hover:text-rose-600" href="/?page=admin-auction-edit&id=<?php echo (int) $auction['id']; ?>">
                            <?php echo htmlspecialchars($auction['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="text-sm text-slate-500">Блиц: <?php echo !empty($auction['blitz_price']) ? number_format((float) $auction['blitz_price'], 2, '.', ' ') . ' ₽' : '—'; ?></div>
                    </div>
                    <div class="text-sm font-semibold text-rose-700"><?php echo htmlspecialchars($auction['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-700">
                        <?php echo !empty($auction['starts_at']) ? htmlspecialchars($auction['starts_at'], ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                        <?php echo !empty($auction['ends_at']) ? htmlspecialchars($auction['ends_at'], ENT_QUOTES, 'UTF-8') : '—'; ?>
                    </div>
                    <div class="text-sm font-semibold text-slate-900"><?php echo number_format($currentPrice, 2, '.', ' '); ?> ₽</div>
                    <div class="text-sm text-slate-600">
                        <?php echo ($auction['winner_last4'] ?? '----') !== '----' ? 'Победитель: …' . htmlspecialchars($auction['winner_last4'], ENT_QUOTES, 'UTF-8') : 'Победитель не выбран'; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Завершённые лоты</p>
                <h2 class="text-xl font-semibold text-slate-900">Архив акций</h2>
            </div>
            <p class="text-sm text-slate-500">Кликните по названию, чтобы посмотреть итоговую информацию.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <span>ID</span>
                <span>Лот</span>
                <span>Статус</span>
                <span>Период</span>
                <span>Итоговая цена</span>
                <span>Победитель</span>
            </div>
            <?php if (empty($finishedLots)): ?>
                <div class="px-5 py-4 text-sm text-slate-500">Завершённых лотов пока нет.</div>
            <?php endif; ?>
            <?php foreach ($finishedLots as $auction): ?>
                <?php $currentPrice = (float) ($auction['current_price'] ?? 0); ?>
                <article class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                    <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $auction['id']; ?></div>
                    <div class="space-y-1">
                        <a class="text-base font-semibold text-slate-900 transition hover:text-rose-600" href="/?page=admin-auction-view&id=<?php echo (int) $auction['id']; ?>">
                            <?php echo htmlspecialchars($auction['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="text-sm text-slate-500">Блиц: <?php echo !empty($auction['blitz_price']) ? number_format((float) $auction['blitz_price'], 2, '.', ' ') . ' ₽' : '—'; ?></div>
                    </div>
                    <div class="text-sm font-semibold text-rose-700"><?php echo htmlspecialchars($auction['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-700">
                        <?php echo !empty($auction['starts_at']) ? htmlspecialchars($auction['starts_at'], ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                        <?php echo !empty($auction['ends_at']) ? htmlspecialchars($auction['ends_at'], ENT_QUOTES, 'UTF-8') : '—'; ?>
                    </div>
                    <div class="text-sm font-semibold text-slate-900"><?php echo number_format($currentPrice, 2, '.', ' '); ?> ₽</div>
                    <div class="text-sm text-slate-600">
                        <?php echo ($auction['winner_last4'] ?? '----') !== '----' ? '…' . htmlspecialchars($auction['winner_last4'], ENT_QUOTES, 'UTF-8') : 'Победитель не выбран'; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

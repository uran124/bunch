<?php /** @var array $activeLots */ ?>
<?php /** @var array $finishedLots */ ?>
<?php /** @var array $loadErrors */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $lotterySettings = $lotterySettings ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Акции</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Акции', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Список активных и завершённых аукционных лотов для акций.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-auction-create" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Лот аукциона
            </a>
            <a href="#promo-item-form" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">hourglass_top</span>
                Лимитированный товар
            </a>
            <a href="#lottery-form" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">
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
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Создание</p>
                <h2 class="text-xl font-semibold text-slate-900">Новая акция</h2>
            </div>
            <p class="text-sm text-slate-500">Добавьте лот, лимитированный товар или розыгрыш прямо на этой странице.</p>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <form id="promo-item-form" action="/?page=admin-promo-item-save" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-amber-50/60">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-500">Ограниченный товар</p>
                        <h3 class="text-lg font-semibold text-slate-900">Товар по времени или количеству</h3>
                    </div>
                    <span class="material-symbols-rounded text-3xl text-amber-500">hourglass_top</span>
                </div>
                <div class="grid gap-4">
                    <label class="grid gap-2 text-sm text-slate-600">
                        Название
                        <input type="text" name="title" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" required>
                    </label>
                    <label class="grid gap-2 text-sm text-slate-600">
                        Описание
                        <textarea name="description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900"></textarea>
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm text-slate-600">
                            Цена, ₽
                            <input type="number" name="price" min="1" step="0.01" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" required>
                        </label>
                        <label class="grid gap-2 text-sm text-slate-600">
                            Количество
                            <input type="number" name="quantity" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" placeholder="Например, 15">
                        </label>
                    </div>
                    <label class="grid gap-2 text-sm text-slate-600">
                        Окончание акции
                        <input type="datetime-local" name="ends_at" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900">
                        <span class="text-xs text-slate-400">Если дату не указать, акция закончится, когда количество закончится.</span>
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm text-slate-600">
                            Лейбл
                            <input type="text" name="label" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" placeholder="Разовая акция">
                        </label>
                        <label class="grid gap-2 text-sm text-slate-600">
                            Фото (URL)
                            <input type="text" name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900">
                        </label>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300" checked>
                        Сразу показывать в каталоге
                    </label>
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:-translate-y-0.5">
                    <span class="material-symbols-rounded text-base">add</span>
                    Добавить ограниченный товар
                </button>
            </form>

            <form id="lottery-form" action="/?page=admin-lottery-save" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-violet-50/60">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-violet-500">Розыгрыш</p>
                        <h3 class="text-lg font-semibold text-slate-900">Создать товар для лотереи</h3>
                    </div>
                    <span class="material-symbols-rounded text-3xl text-violet-500">celebration</span>
                </div>
                <div class="grid gap-4">
                    <label class="grid gap-2 text-sm text-slate-600">
                        Название
                        <input type="text" name="title" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" required>
                    </label>
                    <label class="grid gap-2 text-sm text-slate-600">
                        Описание приза
                        <textarea name="prize_description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900"></textarea>
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm text-slate-600">
                            Стоимость билета, ₽
                            <input type="number" name="ticket_price" min="0" step="0.01" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" value="0">
                            <span class="text-xs text-slate-400">0 — участие бесплатно.</span>
                        </label>
                        <label class="grid gap-2 text-sm text-slate-600">
                            Количество билетов
                            <input type="number" name="tickets_total" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" required>
                        </label>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm text-slate-600">
                            Дата розыгрыша
                            <input type="datetime-local" name="draw_at" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900">
                        </label>
                        <label class="grid gap-2 text-sm text-slate-600">
                            Статус
                            <select name="status" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900">
                                <option value="active">Активен</option>
                                <option value="finished">Завершён</option>
                            </select>
                        </label>
                    </div>
                    <label class="grid gap-2 text-sm text-slate-600">
                        Фото (URL)
                        <input type="text" name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900">
                    </label>
                    <div class="rounded-xl border border-violet-100 bg-violet-50 px-4 py-3 text-xs text-violet-700">
                        Бесплатные розыгрыши: один билет на пользователя и не более <?php echo (int) ($lotterySettings['freeMonthlyLimit'] ?? 0); ?> участий в месяц.
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">
                    <span class="material-symbols-rounded text-base">add</span>
                    Создать розыгрыш
                </button>
            </form>
        </div>
    </section>

    <section id="promo-settings" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Настройки розыгрышей</p>
                <h2 class="text-xl font-semibold text-slate-900">Ограничения бесплатных розыгрышей</h2>
            </div>
        </div>
        <form action="/?page=admin-promo-settings-save" method="post" class="flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <label class="grid gap-2 text-sm text-slate-600">
                Лимит бесплатных участий на пользователя в месяц
                <input type="number" name="free_lottery_monthly_limit" min="0" step="1" class="w-48 rounded-xl border border-slate-200 px-3 py-2 text-base text-slate-900" value="<?php echo (int) ($lotterySettings['freeMonthlyLimit'] ?? 0); ?>">
                <span class="text-xs text-slate-400">0 — без ограничений.</span>
            </label>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">settings</span>
                Сохранить настройки
            </button>
        </form>
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

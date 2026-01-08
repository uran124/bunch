<?php /** @var array $lotteries */ ?>
<?php /** @var array $auctions */ ?>
<?php /** @var array $promoItems */ ?>
<?php /** @var array $promoCategories */ ?>
<?php /** @var array $loadErrors */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Акции</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Акции', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Акционные товары и спецпредложения без обязательной привязки к поставке. Поддерживаются типы sale, auction, lottery и произвольные.</p>
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

    <?php if (!empty($message)): ?>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo $message === 'saved' ? 'Изменения сохранены.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($loadErrors)): ?>
        <?php
        $loadErrorLabels = [
            'lotteries' => 'лотереи',
            'auctions' => 'аукционы',
            'promoItems' => 'разовые акции',
            'promoCategories' => 'категории акций',
        ];
        $visibleErrors = array_values(array_intersect_key($loadErrorLabels, array_flip($loadErrors)));
        ?>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Не удалось загрузить разделы: <?php echo htmlspecialchars(implode(', ', $visibleErrors), ENT_QUOTES, 'UTF-8'); ?>. Проверьте логи сервера.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Категории акций</p>
            <h2 class="text-xl font-semibold text-slate-900">Управление видимостью</h2>
            <p class="text-sm text-slate-600">Отключенные категории не отображаются в пользовательском разделе «Акции».</p>
        </div>
        <form action="/?page=admin-promo-categories-save" method="post" class="grid gap-3">
            <?php foreach ($promoCategories as $category): ?>
                <?php $isActiveCategory = !empty($category['is_active']); ?>
                <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                    <span><?php echo htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="checkbox" name="categories[<?php echo htmlspecialchars($category['code'], ENT_QUOTES, 'UTF-8'); ?>]" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-200" <?php echo $isActiveCategory ? 'checked' : ''; ?>>
                </label>
            <?php endforeach; ?>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">tune</span>
                Сохранить категории
            </button>
        </form>
    </div>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Новая лотерея</p>
            <h2 class="text-xl font-semibold text-slate-900">Создать лотерею как товар</h2>
            <p class="text-sm text-slate-600">Количество билетов фиксируется при создании, все билеты создаются сразу и видны клиентам.</p>
        </div>
        <form action="/?page=admin-lottery-save" method="post" class="grid gap-3">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название лотереи
                <input name="title" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание приза
                <textarea name="prize_description" rows="2" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена билета, ₽
                    <input name="ticket_price" type="number" step="0.01" min="0" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество билетов
                    <input name="tickets_total" type="number" min="1" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Дата/время розыгрыша
                    <input name="draw_at" type="datetime-local" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Статус
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <option value="active">Активна</option>
                        <option value="sold_out">Билеты распроданы</option>
                        <option value="finished">Розыгрыш завершён</option>
                    </select>
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">confirmation_number</span>
                Создать лотерею
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[70px_1.2fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Лотерея</span>
            <span>Билеты</span>
            <span>Цена билета</span>
            <span>Статус / розыгрыш</span>
        </div>
        <?php if (empty($lotteries)): ?>
            <div class="px-5 py-4 text-sm text-slate-500">Лотереи ещё не созданы.</div>
        <?php endif; ?>
        <?php foreach ($lotteries as $lottery): ?>
            <?php
            $ticketPrice = (float) ($lottery['ticket_price'] ?? 0);
            $ticketsPaid = (int) ($lottery['tickets_paid'] ?? 0);
            $ticketsTotal = (int) ($lottery['tickets_total'] ?? 0);
            $ticketsFree = (int) ($lottery['tickets_free'] ?? max(0, $ticketsTotal - $ticketsPaid));
            ?>
            <article class="grid grid-cols-[70px_1.2fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $lottery['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Фото: <?php echo $lottery['photo'] ? 'есть' : '—'; ?></div>
                </div>
                <div class="text-sm text-slate-700">
                    <?php echo $ticketsPaid; ?>/<?php echo $ticketsTotal; ?>
                    <span class="text-xs text-slate-500">(свободно <?php echo $ticketsFree; ?>)</span>
                </div>
                <div class="text-sm font-semibold text-rose-700"><?php echo number_format($ticketPrice, 2, '.', ' '); ?> ₽</div>
                <div class="space-y-1 text-sm text-slate-600">
                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($lottery['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div><?php echo htmlspecialchars($lottery['draw_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Новый аукцион</p>
            <h2 class="text-xl font-semibold text-slate-900">Создать лот для торгов</h2>
            <p class="text-sm text-slate-600">Укажите шаг ставки и блиц-цену — клиент сможет выкупить лот сразу.</p>
        </div>
        <form action="/?page=admin-auction-save" method="post" class="grid gap-3">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название лота
                <input name="title" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание
                <textarea name="description" rows="2" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"></textarea>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="image" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена в магазине, ₽
                    <input name="store_price" type="number" step="0.01" min="0" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Стартовая цена, ₽
                    <input name="start_price" type="number" step="0.01" min="1" value="1" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Шаг ставки, ₽
                    <input name="bid_step" type="number" step="0.01" min="1" value="10" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Блиц-цена, ₽
                    <input name="blitz_price" type="number" step="0.01" min="0" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Старт торгов
                    <input name="starts_at" type="datetime-local" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Окончание
                    <input name="ends_at" type="datetime-local" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Статус
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <option value="draft">Черновик</option>
                    <option value="active">Активен</option>
                    <option value="finished">Завершён</option>
                    <option value="cancelled">Отменён</option>
                </select>
            </label>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">gavel</span>
                Создать аукцион
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Аукцион</span>
            <span>Статус</span>
            <span>Период</span>
            <span>Текущая ставка</span>
            <span>Блиц / победитель</span>
        </div>
        <?php if (empty($auctions)): ?>
            <div class="px-5 py-4 text-sm text-slate-500">Аукционы ещё не созданы.</div>
        <?php endif; ?>
        <?php foreach ($auctions as $auction): ?>
            <?php $currentPrice = (float) ($auction['current_price'] ?? 0); ?>
            <article class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $auction['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($auction['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500">Блиц: <?php echo $auction['blitz_price'] ? number_format($auction['blitz_price'], 2, '.', ' ') . ' ₽' : '—'; ?></div>
                </div>
                <div class="text-sm font-semibold text-rose-700"><?php echo htmlspecialchars($auction['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm text-slate-700">
                    <?php echo $auction['starts_at'] ? htmlspecialchars($auction['starts_at'], ENT_QUOTES, 'UTF-8') : '—'; ?><br>
                    <?php echo $auction['ends_at'] ? htmlspecialchars($auction['ends_at'], ENT_QUOTES, 'UTF-8') : '—'; ?>
                </div>
                <div class="text-sm font-semibold text-slate-900"><?php echo number_format($currentPrice, 2, '.', ' '); ?> ₽</div>
                <div class="text-sm text-slate-600">
                    <?php echo $auction['winner_last4'] !== '----' ? 'Победитель: …' . $auction['winner_last4'] : 'Победитель не выбран'; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Разовые товары</p>
            <h2 class="text-xl font-semibold text-slate-900">Акции с ограниченным количеством</h2>
            <p class="text-sm text-slate-600">Разовые товары без привязки к поставкам: укажите количество (если не заполнено — товар всего один) и дату окончания акции (если не указано — ограничение только по наличию).</p>
        </div>
        <form action="/?page=admin-promo-item-save" method="post" class="grid gap-3">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название акции
                <input name="title" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание
                <textarea name="description" rows="2" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена, ₽
                    <input name="price" type="number" step="0.01" min="1" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество
                    <input name="quantity" type="number" min="1" placeholder="Если не заполнено — 1 шт" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Действует до
                    <input name="ends_at" type="datetime-local" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Бейдж/метка
                    <input name="label" placeholder="Например, Limited" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_active" checked class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-200">
                Активна
            </label>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">local_offer</span>
                Добавить разовую акцию
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>ID</span>
            <span>Разовая акция</span>
            <span>Количество</span>
            <span>Цена</span>
            <span>Срок / статус</span>
        </div>
        <?php if (empty($promoItems)): ?>
            <div class="px-5 py-4 text-sm text-slate-500">Разовые акции ещё не добавлены.</div>
        <?php endif; ?>
        <?php foreach ($promoItems as $promo): ?>
            <?php
            $quantity = $promo['quantity'] !== null ? (int) $promo['quantity'] : 1;
            $promoPrice = (float) ($promo['price'] ?? 0);
            $promoActive = !empty($promo['is_active']);
            ?>
            <article class="grid grid-cols-[70px_1.4fr_1fr_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900">#<?php echo (int) $promo['id']; ?></div>
                <div class="space-y-1">
                    <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($promo['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-sm text-slate-500"><?php echo $promo['label'] ? htmlspecialchars($promo['label'], ENT_QUOTES, 'UTF-8') : 'Разовая акция'; ?></div>
                </div>
                <div class="text-sm text-slate-700"><?php echo $quantity; ?> шт</div>
                <div class="text-sm font-semibold text-rose-700"><?php echo number_format($promoPrice, 2, '.', ' '); ?> ₽</div>
                <div class="space-y-1 text-sm text-slate-600">
                    <div class="font-semibold text-slate-900"><?php echo $promoActive ? 'Активна' : 'Выключена'; ?></div>
                    <div><?php echo $promo['ends_at'] ? htmlspecialchars($promo['ends_at'], ENT_QUOTES, 'UTF-8') : 'Только по наличию'; ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Прозрачность и доверие</p>
            <h2 class="text-xl font-semibold text-slate-900">Как показываем честность</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Билеты создаются сразу при запуске лотереи, количество фиксировано.</li>
                <li>В пользовательской части виден статус каждого билета и последние 4 цифры телефона.</li>
                <li>Вся история действий по билетам и ставкам хранится в логах.</li>
            </ul>
        </div>
        <div class="space-y-3 rounded-xl border border-dashed border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <div class="flex items-center gap-2 font-semibold text-emerald-900">
                <span class="material-symbols-rounded text-base">verified</span>
                Советы по публикации
            </div>
            <p>После завершения лотереи или аукциона добавьте публичный пост с победителем и ссылкой на запись розыгрыша.</p>
            <div class="flex flex-wrap gap-2">
                <a href="/?page=admin-products" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="material-symbols-rounded text-base">add_circle</span>
                    Перейти к товарам
                </a>
            </div>
        </div>
    </section>
</section>

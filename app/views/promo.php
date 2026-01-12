<?php
/** @var array $pageMeta */
/** @var array $oneTimeItems */
/** @var array $auctionLots */
/** @var array $lotteries */
/** @var array $promoCategories */
$hasPromos = !empty($oneTimeItems);
$hasAuctions = !empty($auctionLots);
$hasLotteries = !empty($lotteries);
$hasAnyPromos = $hasPromos || $hasAuctions || $hasLotteries;
$isAuthenticated = $isAuthenticated ?? false;
$botConnected = $botConnected ?? false;
$botUsername = $botUsername ?? '';
$botLink = $botUsername !== '' ? 'https://t.me/' . $botUsername . '?start=register' : '#';
?>

<section class="space-y-6" data-promo-root data-bot-connected="<?php echo $botConnected ? 'true' : 'false'; ?>" data-bot-link="<?php echo htmlspecialchars($botLink, ENT_QUOTES, 'UTF-8'); ?>" data-authenticated="<?php echo $isAuthenticated ? 'true' : 'false'; ?>">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <h1>
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.08em] text-rose-600">
                    <span class="material-symbols-rounded text-base"></span>
                    Акции и спецпредложения
                </p>
            </h1>
        </div>
    </header>

    <?php if (!$hasAnyPromos): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
            Пока нет активных предложений. Загляните позже — мы регулярно обновляем подборку акций.
        </div>
    <?php endif; ?>

    <?php if ($hasAuctions): ?>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Аукцион</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($auctionLots as $lot): ?>
                    <?php
                    $currentPrice = number_format((float) $lot['current_price'], 0, '.', ' ') . ' ₽';
                    $blitzPrice = $lot['blitz_price'] !== null ? number_format((float) $lot['blitz_price'], 0, '.', ' ') . ' ₽' : null;
                    $bidCount = (int) ($lot['bid_count'] ?? 0);
                    $endsAtIso = null;
                    if (!empty($lot['ends_at'])) {
                        $endsAtIso = (new DateTime($lot['ends_at']))->format(DateTimeInterface::ATOM);
                    }
                    ?>
                    <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-promo-item data-promo-type="auction">
                        <?php if (!empty($lot['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($lot['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                        <?php else: ?>
                            <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <span class="material-symbols-rounded text-3xl">image</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <h3 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($lot['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-sm font-semibold text-slate-700" data-countdown data-countdown-target="<?php echo htmlspecialchars((string) $endsAtIso, ENT_QUOTES, 'UTF-8'); ?>" data-countdown-finished-text="Аукцион завершился">
                                <?php echo $lot['status'] === 'finished' ? 'Аукцион завершился' : 'До завершения —'; ?>
                            </p>
                            <button type="button" class="text-left text-base font-semibold text-rose-700 transition hover:text-rose-800" data-auction-open data-auction-id="<?php echo (int) $lot['id']; ?>">
                                <?php if ($lot['status'] === 'finished' && !empty($lot['winner_last4']) && $lot['winning_amount'] !== null): ?>
                                    Победитель …<?php echo htmlspecialchars($lot['winner_last4'], ENT_QUOTES, 'UTF-8'); ?> <?php echo number_format((float) $lot['winning_amount'], 0, '.', ' '); ?> ₽
                                <?php else: ?>
                                    <?php echo htmlspecialchars($currentPrice, ENT_QUOTES, 'UTF-8'); ?> (<?php echo $bidCount; ?> ставок)
                                <?php endif; ?>
                            </button>
                            <?php if ($blitzPrice): ?>
                                <p class="text-sm text-slate-600">Блиц цена: <?php echo htmlspecialchars($blitzPrice, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <div class="mt-auto grid gap-2 sm:grid-cols-2">
                                <button type="button" data-auction-step data-auction-id="<?php echo (int) $lot['id']; ?>" data-auction-step-value="<?php echo htmlspecialchars((string) ($lot['bid_step'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>" data-requires-bot class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo !$isAuthenticated || $lot['status'] !== 'active' ? 'disabled' : ''; ?>>
                                    + <?php echo number_format((float) $lot['bid_step'], 0, '.', ' '); ?> ₽
                                </button>
                                <button type="button" data-auction-blitz data-auction-id="<?php echo (int) $lot['id']; ?>" data-requires-bot class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo !$isAuthenticated || $lot['status'] !== 'active' ? 'disabled' : ''; ?>>
                                    Блиц
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasPromos): ?>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Товары по акции</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-promo-items>
                <?php foreach ($oneTimeItems as $item): ?>
                    <?php
                    $productId = (int) ($item['product_id'] ?? 0);
                    $endsAtIso = $item['ends_at_iso'] ?? null;
                    ?>
                    <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-promo-item data-promo-type="promo" data-product-card data-product-id="<?php echo $productId; ?>">
                        <?php if (!empty($item['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                        <?php else: ?>
                            <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <span class="material-symbols-rounded text-3xl">image</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <h3 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($endsAtIso)): ?>
                                <p class="text-sm font-semibold text-slate-700" data-countdown data-countdown-target="<?php echo htmlspecialchars((string) $endsAtIso, ENT_QUOTES, 'UTF-8'); ?>" data-countdown-finished-text="Акция завершилась">
                                    До завершения —
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($item['quantity'])): ?>
                                <p class="text-sm text-slate-600">Осталось <?php echo (int) ($item['stock'] ?? 0); ?> шт</p>
                            <?php endif; ?>
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-sm text-slate-400 line-through"><?php echo number_format((float) $item['base_price'], 0, '.', ' '); ?> ₽</span>
                                <span class="text-base font-semibold text-rose-700"><?php echo number_format((float) $item['price'], 0, '.', ' '); ?> ₽</span>
                            </div>
                            <button type="button" data-add-to-cart data-requires-bot class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo !$isAuthenticated ? 'disabled' : ''; ?>>
                                <span class="material-symbols-rounded text-base">shopping_cart</span>
                                <span class="hidden sm:inline">В корзину</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasLotteries): ?>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Розыгрыши</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($lotteries as $lottery): ?>
                    <?php
                    $ticketPrice = (float) $lottery['ticket_price'];
                    $ticketLabel = $ticketPrice > 0 ? number_format($ticketPrice, 0, '.', ' ') . ' ₽' : 'Бесплатно';
                    $endsAtIso = $lottery['draw_at_iso'] ?? null;
                    ?>
                    <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-promo-item data-promo-type="lottery">
                        <?php if (!empty($lottery['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($lottery['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                        <?php else: ?>
                            <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <span class="material-symbols-rounded text-3xl">image</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <h3 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($lottery['prize_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($endsAtIso)): ?>
                                <p class="text-sm font-semibold text-slate-700" data-countdown data-countdown-target="<?php echo htmlspecialchars((string) $endsAtIso, ENT_QUOTES, 'UTF-8'); ?>" data-countdown-finished-text="Розыгрыш завершился">
                                    До завершения —
                                </p>
                            <?php endif; ?>
                            <button type="button" class="text-left text-sm font-semibold text-slate-700 underline decoration-dotted underline-offset-4" data-lottery-open data-lottery-id="<?php echo (int) $lottery['id']; ?>">
                                Список участников
                            </button>
                            <p class="text-sm text-slate-600">Билет: <?php echo htmlspecialchars($ticketLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                            <button type="button" class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" data-lottery-open data-lottery-id="<?php echo (int) $lottery['id']; ?>" data-requires-bot <?php echo !$isAuthenticated ? 'disabled' : ''; ?>>
                                <span class="material-symbols-rounded text-base"></span>
                                <span class="hidden sm:inline">Участвовать</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-lottery-modal>
    <div class="w-full max-w-2xl rounded-2xl bg-white p-4 shadow-2xl shadow-slate-500/20">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-slate-900" data-lottery-title>Розыгрыш</h3>
                <p class="text-sm text-slate-500" data-lottery-subtitle></p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-lottery-close>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="mt-4 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                <span data-lottery-price></span>
                <span data-lottery-availability></span>
            </div>
            <div class="grid gap-2" data-lottery-tickets></div>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-lottery-random>
                Случайный номер
            </button>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-lottery-close>
                    Закрыть
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-lottery-pay>
                    Оплатить
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-auction-modal>
    <div class="w-full max-w-xl rounded-2xl bg-white p-4 shadow-2xl shadow-slate-500/20">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-slate-900" data-auction-title>Аукцион</h3>
                <p class="text-sm text-slate-500" data-auction-subtitle></p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-auction-close>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="mt-4 space-y-3">
            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                <span>Текущая цена: <strong class="text-slate-900" data-auction-current></strong></span>
                <span>Шаг: <strong class="text-slate-900" data-auction-step></strong></span>
            </div>
            <div class="text-sm text-slate-500">Окончание: <span data-auction-ends></span></div>
            <div class="space-y-2" data-auction-bids></div>
        </div>
        <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4">
            <div class="flex items-center gap-2">
                <input type="number" step="1" min="1" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900" data-auction-amount>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-auction-bid>
                    Сделать ставку
                </button>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-auction-blitz>
                Блиц
            </button>
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-auction-close>
                Закрыть
            </button>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-bot-modal>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl shadow-slate-500/20">
        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900">Подключите уведомления от бота</h3>
            <p class="text-sm text-slate-600">Для участия в акции нужно подключить уведомления от нашего бота.</p>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-bot-cancel>
                Отменить
            </button>
            <a href="<?php echo htmlspecialchars($botLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-bot-connect>
                Подключить
            </a>
        </div>
    </div>
</div>

<?php
/** @var array $pageMeta */
/** @var array $oneTimeItems */
/** @var array $auctionLots */
/** @var array $lotteries */
/** @var array $promoCategories */
/** @var bool $canModerateCatalog */
$hasPromos = !empty($oneTimeItems);
$hasAuctions = !empty($auctionLots);
$hasLotteries = !empty($lotteries);
$hasAnyPromos = $hasPromos || $hasAuctions || $hasLotteries;
$isAuthenticated = $isAuthenticated ?? false;
$botConnected = $botConnected ?? false;
$botUsername = $botUsername ?? '';
$currentUserId = class_exists('Auth') ? (int) Auth::userId() : 0;
$botLink = $botUsername !== '' ? 'https://t.me/' . $botUsername . '?start=register' : '#';
$promoEntries = [];

foreach ($auctionLots as $lot) {
    $promoEntries[] = [
        'type' => 'auction',
        'data' => $lot,
    ];
}

foreach ($oneTimeItems as $item) {
    $promoEntries[] = [
        'type' => 'promo',
        'data' => $item,
    ];
}

foreach ($lotteries as $lottery) {
    $promoEntries[] = [
        'type' => 'lottery',
        'data' => $lottery,
    ];
}
?>

<section class="space-y-6" data-promo-root data-bot-connected="<?php echo $botConnected ? 'true' : 'false'; ?>" data-bot-link="<?php echo htmlspecialchars($botLink, ENT_QUOTES, 'UTF-8'); ?>" data-authenticated="<?php echo $isAuthenticated ? 'true' : 'false'; ?>" data-user-id="<?php echo $currentUserId; ?>">
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

    <?php if ($hasAnyPromos): ?>
        <div class="relative flex min-h-[calc(100svh-10rem)] items-center sm:min-h-0" data-promo-items>
            <div class="flex snap-x gap-4 overflow-x-auto px-0 pb-6 pt-1 sm:pb-10 md:px-1" aria-label="Лента акций и предложений">
            <?php foreach ($promoEntries as $entry): ?>
                <?php if ($entry['type'] === 'auction'): ?>
                    <?php
                    $lot = $entry['data'];
                    $currentPrice = number_format((int) floor((float) $lot['current_price']), 0, '.', ' ') . ' ₽';
                    $blitzPrice = $lot['blitz_price'] !== null ? number_format((int) floor((float) $lot['blitz_price']), 0, '.', ' ') . ' ₽' : null;
                    $bidCount = (int) ($lot['bid_count'] ?? 0);
                    $currentBidUserId = (int) ($lot['current_bid_user_id'] ?? 0);
                    $isLeader = $currentUserId > 0 && $currentBidUserId > 0 && $currentBidUserId === $currentUserId;
                    $isFinished = $lot['status'] === 'finished';
                    $isRecentlyFinished = !empty($lot['is_recently_finished']);
                    $showWinner = $lot['status'] === 'finished' && !empty($lot['winner_last4']) && $lot['winning_amount'] !== null;
                    $winningAmount = $lot['winning_amount'] ?? $lot['current_price'];
                    $winnerLabel = !empty($lot['winner_last4']) ? '…' . $lot['winner_last4'] : '—';
                    $endsAtIso = null;
                    if (!empty($lot['ends_at'])) {
                        $endsAtIso = (new DateTime($lot['ends_at']))->format(DateTimeInterface::ATOM);
                    }
                    ?>
                    <article class="flex snap-center shrink-0 w-[82%] max-w-sm flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:w-[360px]" data-promo-item data-promo-type="auction" data-auction-card data-auction-id="<?php echo (int) $lot['id']; ?>">
                        <?php if (!empty($lot['photo'])): ?>
                            <button type="button" class="relative block w-full text-left" data-auction-open data-auction-id="<?php echo (int) $lot['id']; ?>">
                                <img src="<?php echo htmlspecialchars($lot['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                                <?php if ($isRecentlyFinished): ?>
                                    <span class="absolute inset-0 bg-white/60"></span>
                                <?php endif; ?>
                                <span class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Аукцион</span>
                            </button>
                        <?php else: ?>
                            <button type="button" class="relative flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400" data-auction-open data-auction-id="<?php echo (int) $lot['id']; ?>">
                                <span class="material-symbols-rounded text-3xl">image</span>
                                <?php if ($isRecentlyFinished): ?>
                                    <span class="absolute inset-0 bg-white/60"></span>
                                <?php endif; ?>
                                <span class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Аукцион</span>
                            </button>
                        <?php endif; ?>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <button type="button" class="text-left" data-auction-open data-auction-id="<?php echo (int) $lot['id']; ?>">
                                <h3 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="text-xs text-slate-600"><?php echo htmlspecialchars($lot['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </button>
                            <p class="text-center text-2xl font-semibold text-slate-900" data-countdown data-countdown-target="<?php echo htmlspecialchars((string) $endsAtIso, ENT_QUOTES, 'UTF-8'); ?>" data-countdown-finished-text="Аукцион завершился">
                                <?php echo $lot['status'] === 'finished' ? 'Аукцион завершился' : 'До завершения —'; ?>
                            </p>
                            <div class="mt-auto grid grid-cols-2 gap-4<?php echo $isFinished ? ' hidden' : ''; ?>" data-auction-active-block>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold text-rose-600">Блиц цена:</span>
                                    <span class="text-xl font-semibold text-rose-600">
                                        <?php echo $blitzPrice ? htmlspecialchars($blitzPrice, ENT_QUOTES, 'UTF-8') : '—'; ?>
                                    </span>
                                    <button
                                        type="button"
                                        data-auction-blitz
                                        data-auction-id="<?php echo (int) $lot['id']; ?>"
                                        data-auction-title="<?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-auction-blitz-price="<?php echo htmlspecialchars((string) ($lot['blitz_price'] !== null ? (int) floor((float) $lot['blitz_price']) : ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-requires-bot
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-600 bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-700 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        <?php echo $lot['status'] !== 'active' || !$blitzPrice ? 'disabled' : ''; ?>
                                    >
                                        Блиц
                                    </button>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold text-emerald-600">Текущая</span>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 text-left text-xl font-semibold text-emerald-600"
                                        data-auction-open
                                        data-auction-id="<?php echo (int) $lot['id']; ?>"
                                        data-auction-current-label
                                    >
                                        <span data-auction-current-price>
                                            <?php if ($showWinner): ?>
                                                Победитель …<?php echo htmlspecialchars($lot['winner_last4'], ENT_QUOTES, 'UTF-8'); ?> <?php echo number_format((int) floor((float) $lot['winning_amount']), 0, '.', ' '); ?> ₽
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($currentPrice, ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1" data-auction-bid-count<?php echo $showWinner ? ' hidden' : ''; ?>>
                                            <span><?php echo $bidCount; ?></span>
                                            <span class="material-symbols-rounded text-base" data-auction-bid-icon>gavel</span>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        data-auction-step
                                        data-auction-id="<?php echo (int) $lot['id']; ?>"
                                        data-auction-step-value="<?php echo htmlspecialchars((string) ($lot['bid_step'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-requires-bot
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-600 px-3 py-2 text-xs font-semibold shadow-sm transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60 <?php echo $isLeader ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-600 hover:bg-emerald-50'; ?>"
                                        <?php echo $lot['status'] !== 'active' ? 'disabled' : ''; ?>
                                    >
                                        + <?php echo number_format((float) $lot['bid_step'], 0, '.', ' '); ?> ₽
                                    </button>
                                </div>
                            </div>
                            <div class="mt-auto flex flex-col gap-1<?php echo $isFinished ? '' : ' hidden'; ?>" data-auction-finished-block>
                                <span class="text-xs font-semibold text-slate-500">Цена завершения</span>
                                <span class="text-2xl font-semibold text-slate-900" data-auction-finished-price>
                                    <?php echo number_format((int) floor((float) $winningAmount), 0, '.', ' '); ?> ₽
                                </span>
                                <span class="text-sm text-slate-600" data-auction-finished-bids>Количество ставок: <?php echo $bidCount; ?></span>
                                <span class="text-sm text-slate-600" data-auction-finished-winner>Победитель: <?php echo htmlspecialchars($winnerLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </article>
                <?php elseif ($entry['type'] === 'promo'): ?>
                    <?php
                    $item = $entry['data'];
                    $productId = (int) ($item['product_id'] ?? 0);
                    $endsAtIso = $item['ends_at_iso'] ?? null;
                    $productIsActive = !empty($item['is_active']);
                    ?>
                    <article class="flex snap-center shrink-0 w-[82%] max-w-sm flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:w-[360px]" data-promo-item data-promo-type="promo" data-product-card data-product-id="<?php echo $productId; ?>">
                        <?php if (!empty($item['photo'])): ?>
                            <div class="relative">
                                <?php if (!empty($canModerateCatalog) && $productId > 0): ?>
                                    <div class="absolute right-3 top-3 z-10 flex items-center gap-2">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition hover:border-rose-200 hover:text-rose-600" data-product-edit-open aria-label="Редактировать товар">
                                            <span class="material-symbols-rounded text-base">edit</span>
                                        </button>
                                        <form action="/admin-product-toggle" method="post">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <label class="relative inline-flex h-8 w-14 cursor-pointer items-center" aria-label="Активность товара">
                                                <input type="checkbox" name="is_active" class="peer sr-only" onchange="this.form.submit()" <?php echo $productIsActive ? 'checked' : ''; ?>>
                                                <span class="absolute inset-0 rounded-full bg-white/70 transition peer-checked:bg-emerald-500"></span>
                                                <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                            </label>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover <?php echo !$productIsActive ? 'blur-sm grayscale' : ''; ?>">
                                <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Спецпредложение</span>
                                <?php if (!$productIsActive): ?>
                                    <span class="pointer-events-none absolute bottom-3 left-3 rounded-full bg-slate-900/70 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">Неактивен</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="relative flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <?php if (!empty($canModerateCatalog) && $productId > 0): ?>
                                    <div class="absolute right-3 top-3 z-10 flex items-center gap-2">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition hover:border-rose-200 hover:text-rose-600" data-product-edit-open aria-label="Редактировать товар">
                                            <span class="material-symbols-rounded text-base">edit</span>
                                        </button>
                                        <form action="/admin-product-toggle" method="post">
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                            <label class="relative inline-flex h-8 w-14 cursor-pointer items-center" aria-label="Активность товара">
                                                <input type="checkbox" name="is_active" class="peer sr-only" onchange="this.form.submit()" <?php echo $productIsActive ? 'checked' : ''; ?>>
                                                <span class="absolute inset-0 rounded-full bg-white/70 transition peer-checked:bg-emerald-500"></span>
                                                <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                            </label>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <span class="material-symbols-rounded text-3xl">image</span>
                                <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Спецпредложение</span>
                                <?php if (!$productIsActive): ?>
                                    <span class="pointer-events-none absolute bottom-3 left-3 rounded-full bg-slate-900/70 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">Неактивен</span>
                                <?php endif; ?>
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
                                <span class="text-sm text-slate-400 line-through"><?php echo number_format((int) floor((float) $item['base_price']), 0, '.', ' '); ?> ₽</span>
                                <span class="text-base font-semibold text-rose-700"><?php echo number_format((int) floor((float) $item['price']), 0, '.', ' '); ?> ₽</span>
                            </div>
                            <button type="button" data-add-to-cart data-requires-bot class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo !$productIsActive ? 'disabled' : ''; ?>>
                                <span class="material-symbols-rounded text-base">shopping_cart</span>
                                <span class="hidden sm:inline">В корзину</span>
                            </button>
                        </div>
                    </article>
                <?php elseif ($entry['type'] === 'lottery'): ?>
                    <?php
                    $lottery = $entry['data'];
                    $ticketPrice = (int) floor((float) $lottery['ticket_price']);
                    $ticketLabel = $ticketPrice > 0 ? number_format($ticketPrice, 0, '.', ' ') . ' ₽' : 'Бесплатно';
                    $endsAtIso = $lottery['draw_at_iso'] ?? null;
                    $isRecentlyFinished = !empty($lottery['is_recently_finished']);
                    ?>
                    <article class="flex snap-center shrink-0 w-[82%] max-w-sm flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:w-[360px]" data-promo-item data-promo-type="lottery">
                        <?php if (!empty($lottery['photo'])): ?>
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($lottery['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                                <?php if ($isRecentlyFinished): ?>
                                    <span class="absolute inset-0 bg-white/60"></span>
                                <?php endif; ?>
                                <span class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Розыгрыш</span>
                            </div>
                        <?php else: ?>
                            <div class="relative flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <span class="material-symbols-rounded text-3xl">image</span>
                                <?php if ($isRecentlyFinished): ?>
                                    <span class="absolute inset-0 bg-white/60"></span>
                                <?php endif; ?>
                                <span class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow">Розыгрыш</span>
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
                            <button type="button" class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" data-lottery-open data-lottery-id="<?php echo (int) $lottery['id']; ?>" data-requires-bot>
                                <span class="material-symbols-rounded text-base"></span>
                                <span class="hidden sm:inline">Участвовать</span>
                            </button>
                        </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-lottery-modal>
    <div class="w-full max-w-3xl rounded-2xl bg-white p-4 shadow-2xl shadow-slate-500/20">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-slate-900" data-lottery-title>Выбери номер</h3>
                <p class="text-sm text-slate-500" data-lottery-subtitle></p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-lottery-close>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="mt-4 space-y-3">
            <div class="space-y-1 text-sm text-slate-600">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span data-lottery-price></span>
                    <span data-lottery-availability></span>
                </div>
                <span class="hidden text-xs text-slate-500" data-lottery-limit></span>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3">
                <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8" data-lottery-tickets></div>
            </div>
            <div class="flex items-center justify-between text-sm text-slate-600">
                <span data-lottery-selected>Выбран номер: —</span>
                <span class="text-xs text-slate-400">Нажмите на свободный номер, чтобы выбрать.</span>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-lottery-random>
                Случайный номер
            </button>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-lottery-close>
                    Закрыть
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60" data-lottery-select disabled>
                    Выбрать
                </button>
                <button type="button" class="hidden inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-lottery-pay>
                    Оплатить
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-auction-modal>
    <div class="flex w-full max-w-3xl max-h-[90vh] flex-col overflow-hidden rounded-3xl bg-white p-4 shadow-2xl shadow-slate-500/20 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-slate-900" data-auction-title></h3>
                <p class="text-sm text-slate-500" data-auction-subtitle></p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-auction-close>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                        <img class="aspect-square w-full object-cover" src="/assets/images/products/bouquet.svg" alt="" data-auction-photo data-photo-index="<?php echo $i; ?>">
                    </div>
                <?php endfor; ?>
            </div>
            <p class="text-xs text-slate-600" data-auction-description></p>
            <div class="grid grid-cols-3 gap-2 text-sm font-semibold text-slate-700" data-auction-price-block>
                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-slate-500">
                    <div class="text-xs font-semibold text-slate-400">В магазине</div>
                    <div class="mt-1 text-center text-2xl font-semibold text-slate-400 line-through" data-auction-store-price></div>
                </div>
                <div class="rounded-2xl bg-rose-50 px-3 py-2 text-rose-600">
                    <div class="text-xs font-semibold text-rose-500">Блиц</div>
                    <div class="mt-1 text-center text-2xl font-semibold text-rose-600" data-auction-blitz-price></div>
                </div>
                <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-emerald-700">
                    <div class="text-xs font-semibold text-emerald-600">Текущая</div>
                    <div class="mt-1 text-center text-2xl font-semibold text-emerald-600" data-auction-current></div>
                </div>
            </div>
            <div class="text-center text-2xl font-bold text-slate-900 sm:text-3xl" data-auction-countdown></div>
            <div class="grid grid-cols-3 items-center gap-2" data-auction-actions>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-600 bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-700 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60" data-auction-blitz>
                    Выкупить за —
                </button>
                <input type="number" step="1" min="1" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-center text-sm text-slate-900" data-auction-amount>
                <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-auction-bid>
                    Сделать ставку
                </button>
            </div>
            <div class="space-y-2">
                <button type="button" class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-auction-history-toggle>
                    <span>История ставок</span>
                    <span class="material-symbols-rounded text-base">expand_more</span>
                </button>
                <div class="hidden max-h-48 space-y-2 overflow-y-auto pr-1" data-auction-history></div>
            </div>
        </div>
        <div class="mt-4 flex justify-end border-t border-slate-100 pt-4">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-auction-close>
                Закрыть
            </button>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-auction-blitz-confirm>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl shadow-slate-500/20">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.08em] text-rose-500">Подтверждение блиц-выкупа</p>
            <p class="text-base text-slate-700">Вы действительно хотите выкупить лот</p>
            <p class="text-xl font-semibold text-slate-900" data-auction-blitz-title></p>
            <p class="text-base text-slate-700">за</p>
            <p class="text-xl font-semibold text-slate-900" data-auction-blitz-price></p>
            <p class="text-base text-slate-700">?</p>
        </div>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-auction-blitz-cancel>
                Отменить
            </button>
            <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-auction-blitz-confirm>
                Подтвердить
            </button>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-auth-modal>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl shadow-slate-500/20">
        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900">Нужна авторизация</h3>
            <p class="text-sm text-slate-600">Для участия в акциях нужна авторизация.</p>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <a href="/login" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700">
                Вход
            </a>
            <a href="/register" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                Регистрация
            </a>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-bot-modal>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl shadow-slate-500/20">
        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900">Подключите уведомления от бота</h3>
            <p class="text-sm text-slate-600">Для участия в акциях необходимо подключить уведомления от нашего бота.</p>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-bot-cancel>
                Отменить
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-bot-enable>
                Включить
            </button>
        </div>
    </div>
</div>

<?php if (!empty($canModerateCatalog)): ?>
<div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-product-edit-modal>
    <div class="w-full max-w-3xl space-y-4 rounded-3xl bg-white p-4 shadow-2xl shadow-slate-500/30 sm:p-6">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Редактирование товара</p>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-product-edit-cancel>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <form class="space-y-4" data-product-edit-form>
            <input type="hidden" name="product_id" value="">
            <div class="grid grid-cols-3 gap-2">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <button type="button" class="relative overflow-hidden rounded-2xl border border-slate-100 bg-slate-50" data-edit-photo-trigger data-photo-index="<?php echo $i; ?>">
                        <img class="aspect-square w-full object-cover" src="/assets/images/products/bouquet.svg" alt="">
                        <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-900/15 text-white">
                            <span class="material-symbols-rounded text-2xl">photo_camera</span>
                        </span>
                    </button>
                <?php endfor; ?>
                <input type="file" name="photo_primary" accept="image/*" class="hidden" data-edit-photo-input data-photo-index="0">
                <input type="file" name="photo_secondary" accept="image/*" class="hidden" data-edit-photo-input data-photo-index="1">
                <input type="file" name="photo_tertiary" accept="image/*" class="hidden" data-edit-photo-input data-photo-index="2">
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-1 text-sm font-medium text-slate-700">
                    Название
                    <input type="text" name="name" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900" required>
                </label>
                <label class="flex flex-col gap-1 text-sm font-medium text-slate-700">
                    Основная цена, ₽
                    <input type="number" name="base_price" min="0" step="1" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900" required>
                </label>
            </div>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-700">
                Описание
                <textarea name="description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900"></textarea>
            </label>
            <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Цена от количества</p>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700" data-price-tier-add>+ Добавить</button>
                </div>
                <div class="space-y-2" data-price-tiers></div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-product-edit-cancel>Отмена</button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const editModal = document.querySelector('[data-product-edit-modal]');
        const editForm = editModal?.querySelector('[data-product-edit-form]');
        const editTierWrap = editModal?.querySelector('[data-price-tiers]');
        const editPhotoButtons = editModal ? Array.from(editModal.querySelectorAll('[data-edit-photo-trigger]')) : [];
        const editPhotoInputs = editModal ? Array.from(editModal.querySelectorAll('[data-edit-photo-input]')) : [];
        if (!editModal || !editForm) return;

        const createTierRow = (tier = { min_qty: 2, price: 0 }) => {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-[1fr_1fr_auto] items-center gap-2';
            row.innerHTML = `
                <input type="number" min="2" step="1" value="${Number(tier.min_qty || 2)}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" data-tier-min>
                <input type="number" min="0" step="1" value="${Number(tier.price || 0)}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" data-tier-price>
                <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-slate-500" data-tier-remove>
                    <span class="material-symbols-rounded text-base">delete</span>
                </button>
            `;
            row.querySelector('[data-tier-remove]')?.addEventListener('click', () => row.remove());
            return row;
        };

        const closeModal = () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            document.body.style.overflow = '';
        };

        const openModal = async (card) => {
            const productId = Number(card?.dataset.productId || 0);
            if (!productId) return;
            const response = await fetch(`/admin-product-quick-data?product_id=${productId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await response.json();
            if (!response.ok || !payload?.ok || !payload.product) {
                alert(payload?.error || 'Не удалось загрузить товар');
                return;
            }
            const product = payload.product;
            editForm.elements.product_id.value = String(product.id || productId);
            editForm.elements.name.value = product.name || '';
            editForm.elements.description.value = product.description || '';
            editForm.elements.base_price.value = String(Number(product.base_price || 0));
            [product.photo_url, product.photo_url_secondary, product.photo_url_tertiary].forEach((src, index) => {
                const img = editPhotoButtons[index]?.querySelector('img');
                if (img) img.src = src || '/assets/images/products/bouquet.svg';
            });
            if (editTierWrap) {
                editTierWrap.innerHTML = '';
                const tiers = Array.isArray(product.price_tiers) ? product.price_tiers : [];
                (tiers.length ? tiers : [{ min_qty: 2, price: Number(product.base_price || 0) }]).forEach((tier) => {
                    editTierWrap.appendChild(createTierRow(tier));
                });
            }
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        document.querySelectorAll('[data-product-edit-open]').forEach((button) => {
            button.addEventListener('click', async () => {
                await openModal(button.closest('[data-product-card]'));
            });
        });
        editModal.querySelectorAll('[data-product-edit-cancel]').forEach((button) => button.addEventListener('click', closeModal));
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) closeModal();
        });
        editModal.querySelector('[data-price-tier-add]')?.addEventListener('click', () => editTierWrap?.appendChild(createTierRow()));
        editPhotoButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.dataset.photoIndex || 0);
                editPhotoInputs.find((input) => Number(input.dataset.photoIndex || 0) === index)?.click();
            });
        });
        editPhotoInputs.forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;
                const index = Number(input.dataset.photoIndex || 0);
                const img = editPhotoButtons[index]?.querySelector('img');
                if (img) img.src = URL.createObjectURL(file);
            });
        });
        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(editForm);
            const tiers = Array.from(editTierWrap?.querySelectorAll('div') || []).map((row) => ({
                min_qty: Number(row.querySelector('[data-tier-min]')?.value || 0),
                price: Number(row.querySelector('[data-tier-price]')?.value || 0),
            })).filter((tier) => tier.min_qty >= 2);
            formData.set('price_tiers', JSON.stringify(tiers));
            const response = await fetch('/admin-product-quick-save', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok || !payload?.ok) {
                alert(payload?.error || 'Не удалось сохранить товар');
                return;
            }
            window.location.reload();
        });
    })();
</script>
<?php endif; ?>

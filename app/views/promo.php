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
?>

<section class="space-y-3 sm:space-y-6">
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

    <?php if ($hasPromos): ?>
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Разовые акции</h2>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-promo-items>
            <?php foreach ($oneTimeItems as $item): ?>
                <?php $productId = (int) ($item['product_id'] ?? 0); ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-promo-item data-promo-type="promo" data-product-card data-product-id="<?php echo $productId; ?>">
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                    <?php else: ?>
                        <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-1 flex-col space-y-2 p-3 sm:space-y-3 sm:p-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-rose-700">
                                <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                <span class="material-symbols-rounded text-sm">event</span>
                                <?php echo htmlspecialchars($item['period'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-sm font-semibold leading-tight text-slate-900 sm:text-base"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-base font-semibold text-rose-700 sm:text-lg"><?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-xs text-slate-600 sm:text-sm"><?php echo htmlspecialchars($item['stock'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <button type="button" data-add-to-cart <?php echo $productId > 0 ? '' : 'disabled'; ?> class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60 sm:px-4 sm:py-3 sm:text-sm">
                            <span class="material-symbols-rounded text-base">shopping_cart</span>
                            В корзину
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($hasAuctions): ?>
        <div class="flex items-center justify-between pt-2">
            <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Аукционы</h2>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($auctionLots as $lot): ?>
                <?php
                $currentPrice = number_format((float) $lot['current_price'], 0, '.', ' ') . ' ₽';
                $blitzPrice = $lot['blitz_price'] !== null ? number_format((float) $lot['blitz_price'], 0, '.', ' ') . ' ₽' : null;
                ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <?php if (!empty($lot['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($lot['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                    <?php else: ?>
                        <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-1 flex-col space-y-2 p-3 sm:space-y-3 sm:p-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                                Аукцион
                            </span>
                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                <span class="material-symbols-rounded text-sm">schedule</span>
                                <?php echo htmlspecialchars($lot['time_label'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-sm font-semibold leading-tight text-slate-900 sm:text-base"><?php echo htmlspecialchars($lot['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-base font-semibold text-rose-700 sm:text-lg"><?php echo htmlspecialchars($currentPrice, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ($blitzPrice): ?>
                            <p class="text-xs text-slate-600 sm:text-sm">Блиц: <?php echo htmlspecialchars($blitzPrice, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-slate-500 sm:text-sm"><?php echo htmlspecialchars($lot['status_label'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <button type="button" class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 sm:px-4 sm:py-3 sm:text-sm">
                            <span class="material-symbols-rounded text-base">gavel</span>
                            Подробнее
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($hasLotteries): ?>
        <div class="flex items-center justify-between pt-2">
            <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Розыгрыши</h2>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($lotteries as $lottery): ?>
                <?php
                $ticketPrice = (float) $lottery['ticket_price'];
                $ticketLabel = $ticketPrice > 0 ? number_format($ticketPrice, 0, '.', ' ') . ' ₽' : 'Бесплатно';
                $ticketsLeft = max(0, (int) $lottery['tickets_free']);
                ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <?php if (!empty($lottery['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($lottery['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?>" class="aspect-square w-full object-cover">
                    <?php else: ?>
                        <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-1 flex-col space-y-2 p-3 sm:space-y-3 sm:p-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-violet-700">
                                Розыгрыш
                            </span>
                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                <span class="material-symbols-rounded text-sm">event</span>
                                <?php echo htmlspecialchars($lottery['draw_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-sm font-semibold leading-tight text-slate-900 sm:text-base"><?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-base font-semibold text-rose-700 sm:text-lg"><?php echo htmlspecialchars($ticketLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-xs text-slate-600 sm:text-sm">Осталось билетов: <?php echo $ticketsLeft; ?></p>
                        <p class="text-xs text-slate-500 sm:text-sm"><?php echo htmlspecialchars($lottery['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <button type="button" class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 sm:px-4 sm:py-3 sm:text-sm">
                            <span class="material-symbols-rounded text-base">confirmation_number</span>
                            Участвовать
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
    function updateCartIndicator(count) {
        const isActive = Number(count) > 0;
        document.querySelectorAll('[data-cart-indicator]').forEach((indicator) => {
            indicator.dataset.cartActive = isActive ? 'true' : 'false';
        });
    }

    async function addPromoItemToCart(productId) {
        const response = await fetch('/?page=cart-add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, qty: 1, attributes: [] }),
        });

        if (!response.ok) {
            throw new Error('Не удалось добавить товар в корзину');
        }

        const data = await response.json();
        if (!data.ok) {
            throw new Error(data.error || 'Ошибка добавления в корзину');
        }

        updateCartIndicator(data.totals?.count || 0);
    }

    document.querySelectorAll('[data-product-card][data-product-id]').forEach((card) => {
        const productId = Number(card.dataset.productId || 0);
        if (productId <= 0) {
            return;
        }

        card.querySelectorAll('[data-add-to-cart]').forEach((button) => {
            button.addEventListener('click', async () => {
                button.disabled = true;
                button.classList.add('opacity-70');
                try {
                    await addPromoItemToCart(productId);
                } catch (error) {
                    alert(error.message || 'Ошибка добавления в корзину');
                } finally {
                    button.disabled = false;
                    button.classList.remove('opacity-70');
                }
            });
        });
    });
</script>

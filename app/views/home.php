<?php
/** @var array $products */
/** @var array $pageMeta */
?>

<div class="space-y-0 sm:space-y-8">
    <section class="hidden space-y-2">
        <p class="hidden text-xs uppercase tracking-[0.2em] text-slate-500 sm:block">Главная</p>
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Свежая подборка</h1>
            <span class="hidden items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600 sm:inline-flex">
                <span class="material-symbols-rounded text-base">local_florist</span>
                Выбор дня
            </span>
        </div>
        <p class="hidden text-sm text-slate-600 sm:block">Прокручивайте карточки влево и вправо, выбирайте количество и оформление, а затем оформляйте доставку или самовывоз.</p>
    </section>

    <?php if (empty($products)): ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <span class="material-symbols-rounded text-2xl">inventory_2</span>
            </div>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Товары скоро появятся</h2>
            <p class="mt-1 text-sm text-slate-600">Мы готовим витрину. Зайдите позже, чтобы увидеть свежие позиции и новые предложения.</p>
        </div>
    <?php else: ?>
        <div class="relative">
            <div class="flex snap-x gap-4 overflow-x-auto px-0 pb-20 pt-1 md:px-1" aria-label="Лента товаров">
                <?php foreach ($products as $product): ?>
                    <?php
                    $cardId = 'product-card-' . $product['id'];
                    $priceTiersJson = htmlspecialchars(json_encode($product['price_tiers'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $basePrice = number_format((float) $product['price'], 2, '.', '');
                    $height = $product['stem_height_cm'] ?? $product['supply_stem_height_cm'] ?? null;
                    $flower = $product['supply_flower_name'] ?? $product['name'];
                    $variety = $product['supply_variety'] ?? '';
                    $titleParts = [];

                    if ($flower) {
                        $titleParts[] = mb_strtolower($flower, 'UTF-8');
                    }

                    if ($variety) {
                        $titleParts[] = $variety;
                    }

                    if ($height) {
                        $titleParts[] = $height . 'см';
                    }

                    $displayName = $titleParts ? implode(' ', $titleParts) : $product['name'];
                    ?>
                    <article
                        id="<?php echo $cardId; ?>"
                        data-product-id="<?php echo (int) $product['id']; ?>"
                        data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-photo="<?php echo htmlspecialchars($product['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-card
                        data-base-price="<?php echo $basePrice; ?>"
                        data-price-tiers='<?php echo $priceTiersJson; ?>'
                        class="snap-center shrink-0 w-[82%] max-w-xl rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 transition md:w-[396px]"
                    >
                        <div class="relative">
                            <?php if (!empty($product['photo_url'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($product['photo_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="aspect-square w-full rounded-t-3xl object-cover"
                                >
                            <?php else: ?>
                                <div class="flex aspect-square w-full items-center justify-center rounded-t-3xl bg-slate-100 text-slate-400">
                                    <span class="material-symbols-rounded text-4xl">image</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-0 px-2 pb-4 pt-2 md:space-y-6 md:px-5 md:pt-5">
                            <div class="space-y-1 md:space-y-2">
                                <h2 class="text-base font-semibold leading-snug text-slate-900 md:text-2xl"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>
                            </div>

                            <div class="space-y-2 rounded-2xl bg-slate-50 p-2 md:space-y-3 md:p-4">
                                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-700 md:text-sm">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="material-symbols-rounded text-base">stacked_bar_chart</span>
                                        Количество стеблей
                                    </span>
                                    <input
                                        type="number"
                                        min="1"
                                        max="101"
                                        step="1"
                                        value="1"
                                        inputmode="numeric"
                                        data-qty-value
                                        class="w-16 rounded-lg bg-white px-2 py-1.5 text-base font-bold text-slate-900 shadow-inner shadow-rose-100/60 text-center md:text-xl"
                                    >
                                </div>
                                <input
                                    type="range"
                                    min="1"
                                    max="101"
                                    step="1"
                                    value="1"
                                    data-qty
                                    class="range-slider h-1.5 w-full appearance-none rounded-full bg-slate-200 accent-rose-500"
                                >
                                <div class="hidden justify-between text-[11px] font-semibold uppercase tracking-wide text-slate-400 md:flex">
                                    <span>1</span>
                                    <span>50</span>
                                    <span>101</span>
                                </div>
                            </div>

                            <?php if (!empty($product['attributes'])): ?>
                                <div class="space-y-3">
                                    <?php foreach ($product['attributes'] as $attribute): ?>
                                        <div
                                            class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-2 md:p-3"
                                            data-attribute-group
                                            data-attribute-id="<?php echo (int) $attribute['id']; ?>"
                                            data-applies-to="<?php echo htmlspecialchars($attribute['applies_to'] ?? 'stem', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-selected-delta="0"
                                        >
                                            <div class="hidden flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                                                <span class="inline-flex items-center gap-2">
                                                    <span class="material-symbols-rounded text-base text-rose-500">sell</span>
                                                    <?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($attribute['description'])): ?>
                                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($attribute['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                            <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1">
                                                <?php foreach ($attribute['values'] as $value): ?>
                                                    <?php
                                                    $priceDelta = number_format((float) $value['price_delta'], 2, '.', '');
                                                    ?>
                                                    <button
                                                        type="button"
                                                        data-attr-option
                                                        data-attr-id="<?php echo (int) $attribute['id']; ?>"
                                                        data-value-id="<?php echo (int) $value['id']; ?>"
                                                        data-price-delta="<?php echo $priceDelta; ?>"
                                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 md:text-sm"
                                                    aria-label="<?php echo htmlspecialchars($attribute['name'] . ': ' . $value['value'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >
                                                        <span class="text-xs font-semibold text-slate-800 md:text-sm"><?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php if ($priceDelta != '0.00'): ?>
                                                            <span class="text-xs font-semibold text-rose-600">+<?php echo $priceDelta; ?> ₽</span>
                                                        <?php endif; ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between gap-2 rounded-2xl bg-white px-2.5 py-1.5 shadow-sm md:hidden">
                                <span class="text-base font-bold text-rose-600" data-actual-price>—</span>
                                <button type="button" data-add-to-cart class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                                    <span class="material-symbols-rounded text-base">shopping_cart</span>
                                    В корзину
                                </button>
                            </div>

                            <div class="hidden flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-inner shadow-rose-100/60 md:flex">
                                <span class="text-sm font-semibold text-slate-400 line-through" data-base-price-total>—</span>
                                <span class="flex-1 text-center text-2xl font-bold text-rose-600" data-actual-price>—</span>
                                <button type="button" data-add-to-cart class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                                    <span class="material-symbols-rounded text-base">shopping_cart</span>
                                    <span class="hidden sm:inline">В корзину</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .range-slider::-webkit-slider-thumb {
        appearance: none;
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background: #e11d48;
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.25);
        cursor: pointer;
        border: 2px solid white;
        margin-top: -10px;
    }

    .range-slider::-moz-range-thumb {
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background: #e11d48;
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.25);
        cursor: pointer;
        border: 2px solid white;
    }

    .range-slider::-ms-thumb {
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background: #e11d48;
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.25);
        cursor: pointer;
        border: 2px solid white;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .range-slider {
        touch-action: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .attr-active {
        border-color: #fb7185;
        background: #fff1f2;
        color: #be123c;
        box-shadow: inset 0 1px 3px rgba(251, 113, 133, 0.25);
    }
</style>

<script>
    function formatMoney(amount) {
        return amount.toLocaleString('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    }

    function getUnitPrice(basePrice, tiers, quantity) {
        let unitPrice = basePrice;

        tiers.forEach((tier) => {
            if (quantity >= Number(tier.min_qty)) {
                unitPrice = Number(tier.price);
            }
        });

        return unitPrice;
    }

    function clampQuantity(value, min, max) {
        const numeric = Number.isFinite(value) ? value : min;
        return Math.min(Math.max(numeric, min), max);
    }

    function selectDefaultAttributes(card) {
        card.querySelectorAll('[data-attribute-group]').forEach((group) => {
            const firstOption = group.querySelector('[data-attr-option]');
            if (firstOption) {
                activateOption(firstOption);
            }
        });
    }

    function activateOption(button) {
        const group = button.closest('[data-attribute-group]');
        if (!group) return;

        group.querySelectorAll('[data-attr-option]').forEach((option) => {
            option.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-700', 'shadow-md', 'attr-active');
            option.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
        });

        button.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
        button.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700', 'shadow-md', 'attr-active');

        group.dataset.selectedDelta = button.dataset.priceDelta || '0';
        group.dataset.selectedValue = button.dataset.valueId || '';
    }

    function updateCardTotals(card) {
        const basePrice = Number(card.dataset.basePrice || 0);
        const tiers = JSON.parse(card.dataset.priceTiers || '[]');
        const qtyInput = card.querySelector('[data-qty]');
        const qtyValue = card.querySelector('[data-qty-value]');
        const basePriceTargets = card.querySelectorAll('[data-base-price-total]');
        const actualPriceTargets = card.querySelectorAll('[data-actual-price]');

        const minQty = Number(qtyInput?.min || qtyValue?.min || 1);
        const maxQty = Number(qtyInput?.max || qtyValue?.max || 101);
        const quantity = clampQuantity(Number(qtyValue?.value || qtyInput?.value || minQty), minQty, maxQty);
        const unitPrice = getUnitPrice(basePrice, tiers, quantity);

        const deltas = Array.from(card.querySelectorAll('[data-attribute-group]')).reduce(
            (acc, group) => {
                const scope = group.dataset.appliesTo === 'bouquet' ? 'bouquet' : 'stem';
                const delta = Number(group.dataset.selectedDelta || 0);
                if (scope === 'bouquet') {
                    acc.bouquet += delta;
                } else {
                    acc.stem += delta;
                }
                return acc;
            },
            { stem: 0, bouquet: 0 }
        );

        const baseTotal = basePrice * quantity;
        const actualTotal = (unitPrice + deltas.stem) * quantity + deltas.bouquet;

        if (qtyInput) qtyInput.value = quantity.toString();
        if (qtyValue) qtyValue.value = quantity.toString();
        basePriceTargets.forEach((target) => {
            target.textContent = formatMoney(baseTotal);
        });
        actualPriceTargets.forEach((target) => {
            target.textContent = formatMoney(actualTotal);
        });
    }

    function getSelectedAttributes(card) {
        return Array.from(card.querySelectorAll('[data-attribute-group]'))
            .map((group) => group.dataset.selectedValue)
            .filter(Boolean)
            .map((value) => Number(value));
    }

    function updateCartIndicator(count) {
        const isActive = Number(count) > 0;
        document.querySelectorAll('[data-cart-indicator]').forEach((indicator) => {
            indicator.dataset.cartActive = isActive ? 'true' : 'false';
        });
    }

    async function addCardToCart(card) {
        const productId = Number(card.dataset.productId || 0);
        const qtyInput = card.querySelector('[data-qty-value]') || card.querySelector('[data-qty]');
        const qty = Number(qtyInput?.value || 1);
        const attributes = getSelectedAttributes(card);

        const response = await fetch('/?page=cart-add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, qty, attributes }),
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

    document.querySelectorAll('[data-product-card]').forEach((card) => {
        selectDefaultAttributes(card);
        updateCardTotals(card);

        const qtyInput = card.querySelector('[data-qty]');
        const qtyValueInput = card.querySelector('[data-qty-value]');
        if (qtyInput) {
            qtyInput.addEventListener('input', () => {
                const min = Number(qtyInput.min || qtyValueInput?.min || 1);
                const max = Number(qtyInput.max || qtyValueInput?.max || 101);
                const quantity = clampQuantity(Number(qtyInput.value || min), min, max);
                qtyInput.value = quantity.toString();
                if (qtyValueInput) qtyValueInput.value = quantity.toString();
                updateCardTotals(card);
            });
        }

        if (qtyValueInput) {
            qtyValueInput.addEventListener('input', () => {
                const min = Number(qtyValueInput.min || qtyInput?.min || 1);
                const max = Number(qtyValueInput.max || qtyInput?.max || 101);
                const quantity = clampQuantity(Number(qtyValueInput.value || min), min, max);
                qtyValueInput.value = quantity.toString();
                if (qtyInput) qtyInput.value = quantity.toString();
                updateCardTotals(card);
            });
        }

        const qtyIncrease = card.querySelector('[data-qty-increase]');
        if (qtyIncrease && qtyInput) {
            qtyIncrease.addEventListener('click', () => {
                const current = Number(qtyInput.value || 1);
                const next = Math.min(current + 1, Number(qtyInput.max || 101));
                qtyInput.value = next.toString();
                if (qtyValueInput) qtyValueInput.value = next.toString();
                updateCardTotals(card);
            });
        }

        card.querySelectorAll('[data-attr-option]').forEach((option) => {
            option.addEventListener('click', (event) => {
                event.preventDefault();
                activateOption(option);
                updateCardTotals(card);
            });
        });

        card.querySelectorAll('[data-add-to-cart]').forEach((button) => {
            button.addEventListener('click', async () => {
                button.disabled = true;
                button.classList.add('opacity-70');
                try {
                    await addCardToCart(card);
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

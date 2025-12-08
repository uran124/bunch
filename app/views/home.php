<?php
/** @var array $products */
/** @var array $pageMeta */
?>

<div class="space-y-8">
    <section class="space-y-2">
        <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Главная</p>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Свежая подборка</h1>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                <span class="material-symbols-rounded text-base">local_florist</span>
                Выбор дня
            </span>
        </div>
        <p class="text-sm text-slate-600">Прокручивайте карточки влево и вправо, выбирайте количество и оформление, а затем оформляйте доставку или самовывоз.</p>
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
            <div class="absolute inset-y-0 left-0 w-16 bg-gradient-to-r from-slate-50 to-transparent pointer-events-none"></div>
            <div class="absolute inset-y-0 right-0 w-16 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none"></div>

            <div class="flex snap-x gap-4 overflow-x-auto px-2 pb-4 pt-1 md:px-1" aria-label="Лента товаров">
                <?php foreach ($products as $product): ?>
                    <?php
                    $cardId = 'product-card-' . $product['id'];
                    $priceTiersJson = htmlspecialchars(json_encode($product['price_tiers'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $basePrice = number_format((float) $product['price'], 2, '.', '');
                    $height = $product['stem_height_cm'] ?? $product['supply_stem_height_cm'] ?? null;
                    ?>
                    <article
                        id="<?php echo $cardId; ?>"
                        data-product-card
                        data-base-price="<?php echo $basePrice; ?>"
                        data-price-tiers='<?php echo $priceTiersJson; ?>'
                        class="snap-center shrink-0 w-[92%] max-w-xl rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 transition md:w-[480px]"
                    >
                        <div class="relative">
                            <?php if (!empty($product['photo_url'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($product['photo_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="h-64 w-full rounded-t-3xl object-cover"
                                >
                            <?php else: ?>
                                <div class="flex h-64 w-full items-center justify-center rounded-t-3xl bg-slate-100 text-slate-400">
                                    <span class="material-symbols-rounded text-4xl">image</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur">
                                <span class="material-symbols-rounded text-base text-rose-500">swipe</span>
                                Свайпайте влево/вправо
                            </div>
                        </div>

                        <div class="space-y-6 px-5 pb-6 pt-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Товар</p>
                                    <h2 class="text-xl font-bold leading-tight text-slate-900"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="text-sm text-slate-600">
                                        <?php if (!empty($product['supply_variety'])): ?>
                                            <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($product['supply_variety'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($height): ?>
                                            <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                <span class="material-symbols-rounded text-base">straighten</span>
                                                <?php echo htmlspecialchars($height, ENT_QUOTES, 'UTF-8'); ?> см
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-right text-xs font-semibold text-emerald-700">
                                    <p class="flex items-center justify-end gap-1">
                                        <span class="material-symbols-rounded text-base">task_alt</span>
                                        Готов к заказу
                                    </p>
                                    <?php if (!empty($product['supply_country'])): ?>
                                        <p class="mt-1 text-[11px] uppercase tracking-wide text-emerald-600/80">Страна: <?php echo htmlspecialchars($product['supply_country'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="space-y-3 rounded-2xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="material-symbols-rounded text-base">stacked_bar_chart</span>
                                        Количество стеблей
                                    </span>
                                    <span data-qty-value class="rounded-lg bg-white px-3 py-1 text-xs font-bold text-slate-900 shadow-inner">1</span>
                                </div>
                                <input
                                    type="range"
                                    min="1"
                                    max="101"
                                    step="1"
                                    value="1"
                                    data-qty
                                    class="range-slider h-2 w-full appearance-none rounded-full bg-slate-200 accent-rose-500"
                                >
                                <div class="flex justify-between text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                    <span>1</span>
                                    <span>50</span>
                                    <span>101</span>
                                </div>
                            </div>

                            <?php if (!empty($product['attributes'])): ?>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                        <span class="material-symbols-rounded text-base text-rose-500">tune</span>
                                        Атрибуты товара
                                    </div>
                                    <div class="space-y-3">
                                        <?php foreach ($product['attributes'] as $attribute): ?>
                                            <div
                                                class="space-y-2"
                                                data-attribute-group
                                                data-attribute-id="<?php echo (int) $attribute['id']; ?>"
                                                data-selected-delta="0"
                                            >
                                                <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                                    <span><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($attribute['description'])): ?>
                                                        <span class="text-xs font-medium text-slate-400"><?php echo htmlspecialchars($attribute['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1">
                                                    <?php foreach ($attribute['values'] as $value): ?>
                                                        <?php
                                                        $priceDelta = number_format((float) $value['price_delta'], 2, '.', '');
                                                        $valueId = 'attr-' . $attribute['id'] . '-' . $value['id'];
                                                        ?>
                                                        <button
                                                            type="button"
                                                            data-attr-option
                                                            data-attr-id="<?php echo (int) $attribute['id']; ?>"
                                                            data-price-delta="<?php echo $priceDelta; ?>"
                                                            class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                                            aria-label="<?php echo htmlspecialchars($attribute['name'] . ': ' . $value['value'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        >
                                                            <span class="material-symbols-rounded text-base text-slate-400">sell</span>
                                                            <span class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php if ($priceDelta != '0.00'): ?>
                                                                <span class="text-xs font-semibold text-rose-600">+<?php echo $priceDelta; ?> ₽</span>
                                                            <?php else: ?>
                                                                <span class="text-xs font-semibold text-emerald-600">без наценки</span>
                                                            <?php endif; ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="space-y-2 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-rose-50/40 p-4 shadow-inner shadow-rose-100/60">
                                <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="material-symbols-rounded text-base">payments</span>
                                        Обычная цена
                                    </span>
                                    <span data-base-price-total class="text-lg font-bold text-slate-900">—</span>
                                </div>
                                <div class="flex items-center justify-between text-base font-bold text-rose-600">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="material-symbols-rounded text-xl">local_atm</span>
                                        Фактическая цена
                                    </span>
                                    <span data-actual-price class="text-2xl">—</span>
                                </div>
                                <p class="text-xs text-slate-500">Фактическая стоимость учитывает выбранное количество, скидки за объём и наценку атрибутов.</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                                    <span class="material-symbols-rounded text-base">local_shipping</span>
                                    Доставка
                                </button>
                                <button class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-600">
                                    <span class="material-symbols-rounded text-base">storefront</span>
                                    Самовывоз
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

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
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
            option.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-700', 'shadow-md');
            option.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
        });

        button.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
        button.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700', 'shadow-md');

        group.dataset.selectedDelta = button.dataset.priceDelta || '0';
    }

    function updateCardTotals(card) {
        const basePrice = Number(card.dataset.basePrice || 0);
        const tiers = JSON.parse(card.dataset.priceTiers || '[]');
        const qtyInput = card.querySelector('[data-qty]');
        const qtyValue = card.querySelector('[data-qty-value]');
        const basePriceTarget = card.querySelector('[data-base-price-total]');
        const actualPriceTarget = card.querySelector('[data-actual-price]');

        const quantity = Number(qtyInput?.value || 1);
        const unitPrice = getUnitPrice(basePrice, tiers, quantity);

        const attributeDelta = Array.from(card.querySelectorAll('[data-attribute-group]')).reduce((sum, group) => {
            return sum + Number(group.dataset.selectedDelta || 0);
        }, 0);

        const baseTotal = unitPrice * quantity;
        const actualTotal = (unitPrice + attributeDelta) * quantity;

        if (qtyValue) qtyValue.textContent = quantity.toString();
        if (basePriceTarget) basePriceTarget.textContent = formatMoney(baseTotal);
        if (actualPriceTarget) actualPriceTarget.textContent = formatMoney(actualTotal);
    }

    document.querySelectorAll('[data-product-card]').forEach((card) => {
        selectDefaultAttributes(card);
        updateCardTotals(card);

        const qtyInput = card.querySelector('[data-qty]');
        if (qtyInput) {
            qtyInput.addEventListener('input', () => updateCardTotals(card));
        }

        card.querySelectorAll('[data-attr-option]').forEach((option) => {
            option.addEventListener('click', (event) => {
                event.preventDefault();
                activateOption(option);
                updateCardTotals(card);
            });
        });
    });
</script>

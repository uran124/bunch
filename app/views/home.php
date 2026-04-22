<?php
/** @var array $products */
/** @var array $pageMeta */
/** @var bool $isWholesaleUser */
/** @var bool $canModerateCatalog */
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
        <div class="relative flex min-h-[calc(100svh-6rem)] items-center sm:min-h-0 lg:block lg:min-h-0">
            <div class="flex snap-x gap-4 overflow-x-auto px-0 pb-6 pt-1 sm:pb-20 md:px-1 lg:grid lg:grid-cols-[repeat(auto-fit,minmax(350px,1fr))] lg:gap-5 lg:overflow-visible lg:pb-0 lg:pt-0" aria-label="Лента товаров">
                <?php foreach ($products as $product): ?>
                    <?php
                    $cardId = 'product-card-' . $product['id'];
                    $priceTiersJson = htmlspecialchars(json_encode($product['price_tiers'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $basePrice = (int) floor((float) $product['price']);
                    $height = $product['stem_height_cm'] ?? $product['supply_stem_height_cm'] ?? null;
                    $flower = $product['supply_flower_name'] ?? $product['name'];
                    $variety = $product['supply_variety'] ?? '';
                    $titleParts = [];
                    $isSmallWholesale = ($product['product_type'] ?? 'regular') === 'small_wholesale';
                    $qtyLabel = $isSmallWholesale ? 'Количество пачек' : 'Количество стеблей';
                    $packsTotal = (int) ($product['supply_packs_total'] ?? 0);
                    $packsReserved = (int) ($product['supply_packs_reserved'] ?? 0);
                    $stemsPerPack = (int) ($product['supply_stems_per_pack'] ?? 0);
                    $maxQty = $isSmallWholesale && $packsTotal > 0 ? $packsTotal : 101;
                    $availableQty = $isSmallWholesale && $packsTotal > 0 ? max(0, $packsTotal - $packsReserved) : 0;
                    $midQty = $maxQty > 1 ? (int) floor($maxQty / 2) : 1;
                    $salesComment = $isSmallWholesale
                        ? ($stemsPerPack > 0 ? "продажа пачками по {$stemsPerPack} штук" : 'продажа пачками')
                        : 'продажа поштучно';
                    $country = $product['country'] ?? $product['supply_country'] ?? null;
                    $budSize = $product['bud_size_cm'] ?? $product['supply_bud_size_cm'] ?? null;
                    $description = trim((string) ($product['description'] ?? ''));
                    $primaryPhoto = $product['photo_url'] ?? '';
                    $secondaryPhoto = $product['photo_url_secondary'] ?? '';
                    $tertiaryPhoto = $product['photo_url_tertiary'] ?? '';
                    $fallbackPhoto = $primaryPhoto !== '' ? $primaryPhoto : '/assets/images/products/bouquet.svg';
                    $modalPhotos = [
                        $primaryPhoto !== '' ? $primaryPhoto : $fallbackPhoto,
                        $secondaryPhoto !== '' ? $secondaryPhoto : $fallbackPhoto,
                        $tertiaryPhoto !== '' ? $tertiaryPhoto : $fallbackPhoto,
                    ];
                    $modalPhotosJson = htmlspecialchars(json_encode($modalPhotos, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

                    if ($flower) {
                        $titleParts[] = mb_strtolower($flower, 'UTF-8');
                    }

                    if ($variety) {
                        $titleParts[] = $variety;
                    }

                    if ($height) {
                        $titleParts[] = $height . 'см';
                    }

                    $alternateName = trim($product['alt_name'] ?? '');
                    $displayName = $alternateName !== ''
                        ? $alternateName
                        : ($titleParts ? implode(' ', $titleParts) : $product['name']);
                    $productIsActive = (int) ($product['is_active'] ?? 1) === 1;
                    ?>
                    <article
                        id="<?php echo $cardId; ?>"
                        data-product-id="<?php echo (int) $product['id']; ?>"
                        data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-photo="<?php echo htmlspecialchars($product['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-title="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-country="<?php echo htmlspecialchars((string) ($country ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-stem-height="<?php echo htmlspecialchars((string) ($height ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-bud-size="<?php echo htmlspecialchars((string) ($budSize ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-description="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-photos='<?php echo $modalPhotosJson; ?>'
                        data-product-card
                        data-base-price="<?php echo $basePrice; ?>"
                        data-price-tiers='<?php echo $priceTiersJson; ?>'
                        data-max-qty="<?php echo $maxQty; ?>"
                        data-available-qty="<?php echo $availableQty; ?>"
                        class="snap-center shrink-0 flex w-[82%] max-w-xl flex-col rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 transition md:w-[360px] lg:min-w-[350px] lg:w-full lg:max-w-none lg:shrink xl:w-full"
                    >
                        <div class="relative">
                            <?php if (!empty($canModerateCatalog)): ?>
                                <div class="absolute right-3 top-3 z-10 flex items-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-sm transition hover:border-rose-200 hover:text-rose-600" data-product-edit-open aria-label="Редактировать товар">
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </button>
                                    <form action="/admin-product-toggle" method="post">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                        <label class="relative inline-flex h-8 w-14 cursor-pointer items-center" aria-label="Активность товара">
                                            <input type="checkbox" name="is_active" class="peer sr-only" onchange="this.form.submit()" <?php echo $productIsActive ? 'checked' : ''; ?>>
                                            <span class="absolute inset-0 rounded-full bg-white/70 transition peer-checked:bg-emerald-500"></span>
                                            <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                                        </label>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['photo_url'])): ?>
                                <button type="button" class="block w-full" data-product-modal-trigger>
                                    <img
                                        src="<?php echo htmlspecialchars($product['photo_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="aspect-square w-full rounded-t-3xl object-cover <?php echo !$productIsActive ? 'blur-sm grayscale' : ''; ?>"
                                    >
                                </button>
                            <?php else: ?>
                                <button type="button" class="flex w-full items-center justify-center" data-product-modal-trigger>
                                    <div class="flex aspect-square w-full items-center justify-center rounded-t-3xl bg-slate-100 text-slate-400 <?php echo !$productIsActive ? 'blur-sm grayscale' : ''; ?>">
                                        <span class="material-symbols-rounded text-4xl">image</span>
                                    </div>
                                </button>
                            <?php endif; ?>
                            <?php if (!$productIsActive): ?>
                                <span class="pointer-events-none absolute bottom-3 left-3 rounded-full bg-slate-900/70 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">Неактивен</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-1 flex-col space-y-0 px-2 pb-4 pt-2 md:space-y-6 md:px-5 md:pt-5 lg:px-4 lg:pt-4">
                            <div class="space-y-1 md:space-y-2 lg:space-y-1.5">
                                <button type="button" class="text-left" data-product-modal-trigger>
                                    <h2 class="text-base font-semibold leading-snug text-slate-900 md:text-2xl lg:text-xl"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>
                                </button>
                                <p class="text-[11px] font-semibold text-slate-500 md:text-xs lg:text-[11px]"><?php echo htmlspecialchars($salesComment, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>

                            <div class="space-y-2 md:space-y-3">
                                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-700 md:text-sm lg:text-xs">
                                    <input
                                        type="range"
                                        min="1"
                                        max="<?php echo $maxQty; ?>"
                                        step="1"
                                        value="1"
                                        data-qty
                                        class="range-slider mr-3 h-1.5 w-full appearance-none rounded-full bg-slate-200 accent-rose-500"
                                    >
                                    <input
                                        type="number"
                                        min="1"
                                        max="<?php echo $maxQty; ?>"
                                        step="1"
                                        value="1"
                                        inputmode="numeric"
                                        data-qty-value
                                        class="w-16 rounded-lg bg-white px-2 py-1.5 text-base font-bold text-slate-900 shadow-inner shadow-rose-100/60 text-center md:text-xl lg:text-lg"
                                    >
                                </div>
                                <div class="hidden justify-between text-[11px] font-semibold uppercase tracking-wide text-slate-400 md:flex">
                                    <span>1</span>
                                    <span><?php echo $midQty; ?></span>
                                    <span><?php echo $maxQty; ?></span>
                                </div>
                            </div>

                            <?php if (!empty($product['attributes'])): ?>
                                <div class="space-y-2 rounded-2xl bg-slate-50 p-2 md:space-y-3 md:p-3">
                                    <?php foreach ($product['attributes'] as $attribute): ?>
                                        <div
                                            class="space-y-2"
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
                                            <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1" data-desktop-drag-scroll>
                                                <?php foreach ($attribute['values'] as $value): ?>
                                                    <?php
                                                    $priceDelta = (int) floor((float) $value['price_delta']);
                                                    $appliesTo = $attribute['applies_to'] ?? 'stem';
                                                    $showPriceDelta = $priceDelta !== 0 && $appliesTo === 'bouquet';
                                                    ?>
                                            <button
                                                        type="button"
                                                        data-attr-option
                                                        data-attr-id="<?php echo (int) $attribute['id']; ?>"
                                                        data-value-id="<?php echo (int) $value['id']; ?>"
                                                        data-price-delta="<?php echo $priceDelta; ?>"
                                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-rose-50 hover:text-rose-600 hover:shadow-lg hover:shadow-rose-200/70 md:text-sm lg:text-xs"
                                                    aria-label="<?php echo htmlspecialchars($attribute['name'] . ': ' . $value['value'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >
                                                        <span class="text-xs font-semibold text-slate-800 md:text-sm lg:text-xs"><?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php if ($showPriceDelta): ?>
                                                            <span class="text-xs font-semibold text-rose-600">+<?php echo $priceDelta; ?> ₽</span>
                                                        <?php endif; ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto flex items-center justify-between gap-2 rounded-2xl bg-white px-2.5 py-1.5 shadow-sm md:hidden">
                                <span class="text-base font-bold text-rose-600" data-actual-price>—</span>
                                <button type="button" data-add-to-cart class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo !$productIsActive ? 'disabled' : ''; ?>>
                                    <span class="text-base leading-none">+</span>
                                    <span class="material-symbols-rounded text-base">shopping_cart</span>
                                </button>
                            </div>

                            <div class="mt-auto hidden flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-inner shadow-rose-100/60 md:flex lg:p-3">
                                <span class="text-sm font-semibold text-slate-400 line-through" data-base-price-total>—</span>
                                <span class="flex-1 text-center text-2xl font-bold text-rose-600 lg:text-xl" data-actual-price>—</span>
                                <button type="button" data-add-to-cart class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60 lg:px-3.5 lg:py-2.5 lg:text-xs" <?php echo !$productIsActive ? 'disabled' : ''; ?>>
                                    <span class="text-base leading-none">+</span>
                                    <span class="material-symbols-rounded text-base">shopping_cart</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-product-modal>
    <div class="w-full max-w-3xl space-y-4 rounded-3xl bg-white p-4 shadow-2xl shadow-slate-500/30 sm:p-6" data-product-modal-card>
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Карточка товара</p>
            <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-product-modal-close>
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <button type="button" class="overflow-hidden rounded-2xl border border-slate-100 bg-slate-50" data-product-modal-photo data-photo-index="<?php echo $i; ?>">
                        <img class="aspect-square w-full object-cover" src="/assets/images/products/bouquet.svg" alt="">
                    </button>
                <?php endfor; ?>
            </div>
            <div class="text-lg font-semibold text-slate-900 sm:text-2xl" data-product-modal-title></div>
            <div class="space-y-1 rounded-2xl bg-slate-50 p-3 text-sm text-slate-700 sm:p-4">
                <p>Страна: <span class="font-semibold text-slate-900" data-product-modal-country>—</span></p>
                <p>Ростовка: <span class="font-semibold text-slate-900" data-product-modal-stem>—</span> см</p>
                <p>Размер бутона: <span class="font-semibold text-slate-900" data-product-modal-bud>—</span> см</p>
            </div>
            <div class="space-y-2 rounded-2xl border border-slate-100 bg-white p-3 sm:p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Описание товара</p>
                <p class="text-sm text-slate-600" data-product-modal-description></p>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/90 p-4" data-product-viewer>
    <img class="max-h-[90vh] max-w-[90vw] rounded-2xl object-contain shadow-2xl" src="" alt="" data-product-viewer-image>
</div>

<?php if (!empty($canModerateCatalog)): ?>
<div class="fixed inset-0 z-[70] hidden items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur" data-product-edit-modal>
    <div class="my-auto w-full max-w-3xl space-y-4 rounded-3xl bg-white p-4 shadow-2xl shadow-slate-500/30 sm:p-6">
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
            <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-3" data-edit-stem-attributes>
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Ростовки по товару</p>
                </div>
                <div class="space-y-2 text-sm text-slate-700" data-edit-stem-attributes-body>
                    <p class="text-xs text-slate-500">Атрибуты стебля не найдены.</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-product-edit-cancel>Отмена</button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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
        let unitPrice = Math.floor(basePrice);

        tiers.forEach((tier) => {
            if (quantity >= Number(tier.min_qty)) {
                unitPrice = Math.floor(Number(tier.price));
            }
        });

        return unitPrice;
    }

    function clampQuantity(value, min, max) {
        const numeric = Number.isFinite(value) ? value : min;
        return Math.min(Math.max(numeric, min), max);
    }

    function getQuantityMax(card, fallbackMax) {
        const availableLimit = Number(card.dataset.availableQty || 0);
        if (Number.isFinite(availableLimit) && availableLimit > 0) {
            return Math.min(fallbackMax, availableLimit);
        }
        return fallbackMax;
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
            option.classList.remove('bg-rose-50', 'text-rose-700', 'shadow-md', 'attr-active');
            option.classList.add('bg-white', 'text-slate-700');
        });

        button.classList.remove('bg-white', 'text-slate-700');
        button.classList.add('bg-rose-50', 'text-rose-700', 'shadow-md', 'attr-active');

        group.dataset.selectedDelta = button.dataset.priceDelta || '0';
        group.dataset.selectedValue = button.dataset.valueId || '';
    }

    function updateCardTotals(card) {
        const basePrice = Math.floor(Number(card.dataset.basePrice || 0));
        const tiers = JSON.parse(card.dataset.priceTiers || '[]');
        const qtyInput = card.querySelector('[data-qty]');
        const qtyValue = card.querySelector('[data-qty-value]');
        const basePriceTargets = card.querySelectorAll('[data-base-price-total]');
        const actualPriceTargets = card.querySelectorAll('[data-actual-price]');

        const minQty = Number(qtyInput?.min || qtyValue?.min || 1);
        const fallbackMax = Number(qtyInput?.max || qtyValue?.max || 101);
        const maxQty = getQuantityMax(card, fallbackMax);
        const quantity = clampQuantity(Number(qtyValue?.value || qtyInput?.value || minQty), minQty, maxQty);
        const unitPrice = getUnitPrice(basePrice, tiers, quantity);

        const deltas = Array.from(card.querySelectorAll('[data-attribute-group]')).reduce((acc, group) => {
            const scope = group.dataset.appliesTo === 'bouquet' ? 'bouquet' : 'stem';
            const delta = Math.floor(Number(group.dataset.selectedDelta || 0));
            if (scope === 'bouquet') {
                acc += delta;
            }
            return acc;
        }, 0);

        const baseTotal = Math.floor(basePrice * quantity);
        const actualTotal = Math.floor((unitPrice * quantity) + deltas);

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
        document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
            badge.textContent = isActive ? '1' : '0';
            badge.classList.toggle('bg-rose-500', isActive);
            badge.classList.toggle('bg-slate-300', !isActive);
        });
    }

    async function addCardToCart(card) {
        const productId = Number(card.dataset.productId || 0);
        const qtyInput = card.querySelector('[data-qty-value]') || card.querySelector('[data-qty]');
        const qty = Number(qtyInput?.value || 1);
        const attributes = getSelectedAttributes(card);

        const response = await fetch('/cart-add', {
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

    const modal = document.querySelector('[data-product-modal]');
    const modalTitle = modal?.querySelector('[data-product-modal-title]');
    const modalCountry = modal?.querySelector('[data-product-modal-country]');
    const modalStem = modal?.querySelector('[data-product-modal-stem]');
    const modalBud = modal?.querySelector('[data-product-modal-bud]');
    const modalDescription = modal?.querySelector('[data-product-modal-description]');
    const modalPhotos = modal ? Array.from(modal.querySelectorAll('[data-product-modal-photo]')) : [];
    const modalCloseButtons = modal ? Array.from(modal.querySelectorAll('[data-product-modal-close]')) : [];

    const viewer = document.querySelector('[data-product-viewer]');
    const viewerImage = viewer?.querySelector('[data-product-viewer-image]');
    let viewerPhotos = [];
    let viewerIndex = 0;

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function openModal(card) {
        if (!modal || !card) return;
        const title = card.dataset.productTitle || card.dataset.productName || '';
        const country = card.dataset.productCountry || '—';
        const stem = card.dataset.productStemHeight || '—';
        const bud = card.dataset.productBudSize || '—';
        const description = card.dataset.productDescription || 'Описание появится позже.';
        const photos = JSON.parse(card.dataset.productPhotos || '[]');

        viewerPhotos = Array.isArray(photos) && photos.length ? photos : [];
        modalTitle.textContent = title;
        modalCountry.textContent = country !== '' ? country : '—';
        modalStem.textContent = stem !== '' ? stem : '—';
        modalBud.textContent = bud !== '' ? bud : '—';
        modalDescription.textContent = description !== '' ? description : 'Описание появится позже.';

        modalPhotos.forEach((button, index) => {
            const img = button.querySelector('img');
            const src = viewerPhotos[index] || viewerPhotos[0] || '/assets/images/products/bouquet.svg';
            if (img) {
                img.src = src;
                img.alt = title;
            }
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeViewer() {
        if (!viewer) return;
        viewer.classList.add('hidden');
        viewer.classList.remove('flex');
    }

    function renderViewer() {
        if (!viewerImage) return;
        const src = viewerPhotos[viewerIndex];
        if (src) {
            viewerImage.src = src;
        }
    }

    function openViewer(index) {
        if (!viewer || !viewerImage || viewerPhotos.length === 0) return;
        viewerIndex = index;
        renderViewer();
        viewer.classList.remove('hidden');
        viewer.classList.add('flex');
    }

    function shiftViewer(step) {
        if (viewerPhotos.length === 0) return;
        const nextIndex = (viewerIndex + step + viewerPhotos.length) % viewerPhotos.length;
        viewerIndex = nextIndex;
        renderViewer();
    }

    function initDesktopDragScroll() {
        const finePointerMedia = window.matchMedia('(hover: hover) and (pointer: fine)');
        const shouldHandle = () => finePointerMedia.matches;

        document.querySelectorAll('[data-desktop-drag-scroll]').forEach((track) => {
            let isDragging = false;
            let activePointerId = null;
            let startX = 0;
            let lastX = 0;
            let startScrollLeft = 0;
            let movedDistance = 0;
            let dragActivated = false;
            let dragBound = false;
            let isSnapTrack = false;
            let lastMoveTs = 0;
            let velocityX = 0;
            let momentumFrame = null;
            const dragStartThreshold = 6;

            const isInteractiveTarget = (target) => {
                if (!(target instanceof Element)) return false;
                return Boolean(
                    target.closest(
                        'button, a, input, textarea, select, label, [contenteditable="true"], [data-product-modal-trigger], [data-add-to-cart], [data-attr-option], [data-qty], [data-qty-value], [data-no-drag-scroll]'
                    )
                );
            };

            const stopMomentum = () => {
                if (!momentumFrame) return;
                cancelAnimationFrame(momentumFrame);
                momentumFrame = null;
            };

            const runMomentum = () => {
                stopMomentum();
                const minVelocity = 0.08;
                const step = () => {
                    if (Math.abs(velocityX) < minVelocity) {
                        momentumFrame = null;
                        return;
                    }
                    track.scrollLeft -= velocityX * 16;
                    velocityX *= 0.92;
                    momentumFrame = requestAnimationFrame(step);
                };
                momentumFrame = requestAnimationFrame(step);
            };

            const onPointerDown = (event) => {
                if (!shouldHandle()) return;
                if (event.button !== 0) return;
                if (track.scrollWidth <= track.clientWidth) return;
                if (isInteractiveTarget(event.target)) return;

                stopMomentum();
                isDragging = true;
                activePointerId = event.pointerId;
                startX = event.clientX;
                lastX = event.clientX;
                startScrollLeft = track.scrollLeft;
                movedDistance = 0;
                dragActivated = false;
                velocityX = 0;
                lastMoveTs = event.timeStamp;
                isSnapTrack = track.classList.contains('snap-x') || track.hasAttribute('data-home-products-track');
            };

            const onPointerMove = (event) => {
                if (!isDragging) return;
                if (activePointerId !== null && event.pointerId !== activePointerId) return;
                const delta = event.clientX - startX;
                movedDistance = Math.max(movedDistance, Math.abs(delta));

                if (!dragActivated && movedDistance < dragStartThreshold) {
                    return;
                }

                if (!dragActivated) {
                    dragActivated = true;
                    if (isSnapTrack) {
                        track.classList.add('snap-none');
                    }
                    track.classList.add('cursor-grabbing', 'select-none');
                    if (track.setPointerCapture) {
                        track.setPointerCapture(event.pointerId);
                    }
                }

                track.scrollLeft = startScrollLeft - delta;

                const timeDelta = Math.max(1, event.timeStamp - lastMoveTs);
                velocityX = (event.clientX - lastX) / timeDelta;
                lastX = event.clientX;
                lastMoveTs = event.timeStamp;
                event.preventDefault();
            };

            const stopDrag = () => {
                if (!isDragging) return;
                isDragging = false;
                activePointerId = null;
                const shouldContinueMomentum = dragActivated && movedDistance >= dragStartThreshold;
                dragActivated = false;
                track.classList.remove('cursor-grabbing', 'select-none');
                if (isSnapTrack) {
                    track.classList.remove('snap-none');
                }
                if (shouldContinueMomentum) {
                    runMomentum();
                } else {
                    velocityX = 0;
                }
            };

            const preventClickAfterDrag = (event) => {
                if (movedDistance < dragStartThreshold) return;
                event.preventDefault();
                event.stopPropagation();
                movedDistance = 0;
            };

            const bindDesktopInteractions = () => {
                if (!dragBound) {
                    track.addEventListener('pointerdown', onPointerDown);
                    track.addEventListener('pointermove', onPointerMove);
                    window.addEventListener('pointerup', stopDrag);
                    window.addEventListener('pointercancel', stopDrag);
                    track.addEventListener('click', preventClickAfterDrag, true);
                    dragBound = true;
                }
                track.classList.add('cursor-grab');
            };

            const unbindDesktopInteractions = () => {
                stopDrag();
                stopMomentum();
                if (dragBound) {
                    track.removeEventListener('pointerdown', onPointerDown);
                    track.removeEventListener('pointermove', onPointerMove);
                    window.removeEventListener('pointerup', stopDrag);
                    window.removeEventListener('pointercancel', stopDrag);
                    track.removeEventListener('click', preventClickAfterDrag, true);
                    dragBound = false;
                }
                track.classList.remove('cursor-grab');
            };

            const syncByViewport = () => {
                if (shouldHandle()) {
                    bindDesktopInteractions();
                } else {
                    unbindDesktopInteractions();
                }
            };

            syncByViewport();
            if (typeof finePointerMedia.addEventListener === 'function') {
                finePointerMedia.addEventListener('change', syncByViewport);
            } else if (typeof finePointerMedia.addListener === 'function') {
                finePointerMedia.addListener(syncByViewport);
            }
        });
    }

    initDesktopDragScroll();

    document.querySelectorAll('[data-product-card]').forEach((card) => {
        selectDefaultAttributes(card);
        updateCardTotals(card);

        const qtyInput = card.querySelector('[data-qty]');
        const qtyValueInput = card.querySelector('[data-qty-value]');
        if (qtyInput) {
            qtyInput.addEventListener('input', () => {
                const min = Number(qtyInput.min || qtyValueInput?.min || 1);
                const fallbackMax = Number(qtyInput.max || qtyValueInput?.max || 101);
                const max = getQuantityMax(card, fallbackMax);
                const quantity = clampQuantity(Number(qtyInput.value || min), min, max);
                qtyInput.value = quantity.toString();
                if (qtyValueInput) qtyValueInput.value = quantity.toString();
                updateCardTotals(card);
            });
        }

        if (qtyValueInput) {
            qtyValueInput.addEventListener('input', () => {
                const min = Number(qtyValueInput.min || qtyInput?.min || 1);
                const fallbackMax = Number(qtyValueInput.max || qtyInput?.max || 101);
                const max = getQuantityMax(card, fallbackMax);
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
                const fallbackMax = Number(qtyInput.max || 101);
                const max = getQuantityMax(card, fallbackMax);
                const next = Math.min(current + 1, max);
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

        card.querySelectorAll('[data-product-modal-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', () => openModal(card));
        });
    });

    modalCloseButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    modalPhotos.forEach((button) => {
        button.addEventListener('click', () => {
            const index = Number(button.dataset.photoIndex || 0);
            openViewer(index);
        });
    });

    viewer?.addEventListener('click', (event) => {
        if (event.target === viewer || event.target === viewerImage) {
            closeViewer();
        }
    });

    let touchStartX = 0;
    let touchEndX = 0;
    viewer?.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.screenX || 0;
    });

    viewer?.addEventListener('touchend', (event) => {
        touchEndX = event.changedTouches[0]?.screenX || 0;
        const delta = touchEndX - touchStartX;
        if (Math.abs(delta) > 50) {
            shiftViewer(delta < 0 ? 1 : -1);
        }
    });

    <?php if (!empty($canModerateCatalog)): ?>
    const editModal = document.querySelector('[data-product-edit-modal]');
    const editForm = editModal?.querySelector('[data-product-edit-form]');
    const editTierWrap = editModal?.querySelector('[data-price-tiers]');
    const editStemAttributesBody = editModal?.querySelector('[data-edit-stem-attributes-body]');
    const editPhotoButtons = editModal ? Array.from(editModal.querySelectorAll('[data-edit-photo-trigger]')) : [];
    const editPhotoInputs = editModal ? Array.from(editModal.querySelectorAll('[data-edit-photo-input]')) : [];

    const createTierRow = (tier = { min_qty: 1, price: 0 }) => {
        const row = document.createElement('div');
        row.dataset.tierRow = 'true';
        row.className = 'grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-center';
        row.innerHTML = `
            <input type="number" min="1" step="1" value="${Number(tier.min_qty || 1)}" class="min-w-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" data-tier-min placeholder="От количества">
            <input type="number" min="0" step="1" value="${Number(tier.price || 0)}" class="min-w-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" data-tier-price placeholder="Цена, ₽">
            <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-slate-500 sm:self-auto" data-tier-remove>
                <span class="material-symbols-rounded text-base">delete</span>
            </button>
        `;
        row.querySelector('[data-tier-remove]')?.addEventListener('click', () => {
            if (!editTierWrap) {
                row.remove();
                return;
            }
            const rows = editTierWrap.querySelectorAll('[data-tier-row]');
            if (rows.length <= 1) {
                const minInput = row.querySelector('[data-tier-min]');
                const priceInput = row.querySelector('[data-tier-price]');
                if (minInput) minInput.value = '1';
                if (priceInput) {
                    const basePrice = Number(editForm?.elements?.base_price?.value || 0);
                    priceInput.value = String(basePrice > 0 ? basePrice : 1);
                }
                return;
            }
            row.remove();
        });
        return row;
    };

    const closeEditModal = () => {
        if (!editModal) return;
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    const renderStemAttributes = (product) => {
        if (!editStemAttributesBody) return;
        const selectedAttributeIds = new Set((product.attribute_ids || []).map((id) => Number(id)));
        const selectedValueIds = new Set((product.attribute_value_ids || []).map((id) => Number(id)));
        const stemAttributes = Array.isArray(product.stem_attributes) ? product.stem_attributes : [];

        if (!stemAttributes.length) {
            editStemAttributesBody.innerHTML = '<p class="text-xs text-slate-500">Атрибуты стебля не найдены.</p>';
            return;
        }

        editStemAttributesBody.innerHTML = stemAttributes.map((attribute) => {
            const attributeId = Number(attribute.id || 0);
            const checked = selectedAttributeIds.has(attributeId) ? 'checked' : '';
            const values = Array.isArray(attribute.values) ? attribute.values : [];
            const valuesHtml = values.map((value) => {
                const valueId = Number(value.id || 0);
                const valueChecked = selectedValueIds.has(valueId) ? 'checked' : '';
                return `
                    <label class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                        <input type="checkbox" value="${valueId}" data-edit-stem-value-id="${valueId}" data-parent-attribute-id="${attributeId}" class="h-3.5 w-3.5 rounded border-slate-300 text-rose-600" ${valueChecked}>
                        ${String(value.value || '')}
                    </label>
                `;
            }).join('');

            return `
                <div class="rounded-xl border border-slate-200 bg-white p-2.5" data-edit-stem-attribute-card data-attribute-id="${attributeId}">
                    <label class="flex items-center justify-between gap-2 text-xs font-semibold text-slate-700">
                        <span>${String(attribute.name || '')}</span>
                        <input type="checkbox" value="${attributeId}" data-edit-stem-attribute-id="${attributeId}" class="h-4 w-4 rounded border-slate-300 text-rose-600" ${checked}>
                    </label>
                    <div class="mt-2 flex flex-wrap gap-1.5">${valuesHtml || '<span class="text-xs text-slate-500">Нет активных значений</span>'}</div>
                </div>
            `;
        }).join('');

        editStemAttributesBody.querySelectorAll('[data-edit-stem-attribute-card]').forEach((card) => {
            const toggle = card.querySelector('[data-edit-stem-attribute-id]');
            const values = card.querySelectorAll('[data-edit-stem-value-id]');
            const sync = () => {
                values.forEach((value) => {
                    value.disabled = !toggle?.checked;
                    if (!toggle?.checked) {
                        value.checked = false;
                    }
                });
            };
            toggle?.addEventListener('change', sync);
            sync();
        });
    };

    const openEditModal = async (card) => {
        if (!editModal || !editForm || !card) return;
        const productId = Number(card.dataset.productId || 0);
        if (!productId) return;
        const response = await fetch(`/admin-product-quick-data?product_id=${productId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const payload = await response.json();
        if (!response.ok || !payload?.ok || !payload.product) {
            alert(payload?.error || 'Не удалось загрузить карточку товара');
            return;
        }
        const product = payload.product;
        editForm.elements.product_id.value = String(product.id || productId);
        editForm.elements.name.value = product.name || '';
        editForm.elements.description.value = product.description || '';
        editForm.elements.base_price.value = String(Number(product.base_price || 0));
        const photos = [product.photo_url, product.photo_url_secondary, product.photo_url_tertiary];
        editPhotoButtons.forEach((button, index) => {
            const img = button.querySelector('img');
            if (img) img.src = photos[index] || '/assets/images/products/bouquet.svg';
        });
        if (editTierWrap) {
            editTierWrap.innerHTML = '';
            const tiers = Array.isArray(product.price_tiers) ? product.price_tiers : [];
            (tiers.length ? tiers : [{ min_qty: 1, price: Number(product.base_price || 0) }]).forEach((tier) => {
                editTierWrap.appendChild(createTierRow(tier));
            });
        }
        renderStemAttributes(product);
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    editModal?.querySelectorAll('[data-product-edit-cancel]').forEach((button) => {
        button.addEventListener('click', closeEditModal);
    });
    editModal?.addEventListener('click', (event) => {
        if (event.target === editModal) closeEditModal();
    });
    editModal?.querySelector('[data-price-tier-add]')?.addEventListener('click', () => {
        editTierWrap?.appendChild(createTierRow());
    });
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
            const button = editPhotoButtons.find((item) => Number(item.dataset.photoIndex || 0) === index);
            const img = button?.querySelector('img');
            if (img) img.src = URL.createObjectURL(file);
        });
    });
    document.querySelectorAll('[data-product-edit-open]').forEach((button) => {
        button.addEventListener('click', async () => {
            const card = button.closest('[data-product-card]');
            await openEditModal(card);
        });
    });
    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const formData = new FormData(editForm);
            const basePrice = Number(editForm.elements.base_price?.value || 0);
            const tiers = Array.from(editTierWrap?.querySelectorAll('[data-tier-row]') || []).map((row) => ({
                min_qty: Number(row.querySelector('[data-tier-min]')?.value || 0),
                price: Number(row.querySelector('[data-tier-price]')?.value || 0),
            })).filter((tier) => tier.min_qty >= 1 && tier.price > 0);
            if (!tiers.length && basePrice > 0) {
                tiers.push({ min_qty: 1, price: basePrice });
            }
            formData.set('price_tiers', JSON.stringify(tiers));

            const selectedStemAttributeIds = Array.from(editModal?.querySelectorAll('[data-edit-stem-attribute-id]:checked') || []).map((input) => Number(input.value));
            const selectedStemValueIds = Array.from(editModal?.querySelectorAll('[data-edit-stem-value-id]:checked') || []).map((input) => Number(input.value));
            formData.delete('attribute_ids[]');
            formData.delete('attribute_value_ids[]');
            selectedStemAttributeIds.forEach((id) => formData.append('attribute_ids[]', String(id)));
            selectedStemValueIds.forEach((id) => formData.append('attribute_value_ids[]', String(id)));

            const response = await fetch('/admin-product-quick-save', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const raw = await response.text();
            const payload = JSON.parse(raw);
            if (!response.ok || !payload?.ok || !payload.product) {
                alert(payload?.error || 'Не удалось сохранить товар');
                return;
            }
            window.location.reload();
        } catch (error) {
            alert('Ошибка при сохранении товара. Проверьте поле "Цена от количества".');
        }
    });
    <?php endif; ?>
</script>

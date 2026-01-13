<?php
/** @var array $products */
?>

<section class="space-y-4 sm:space-y-6">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Опт</p>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Коробки под предзаказ</h1>
        <p class="text-sm text-slate-600">Выбирайте позиции под оптовые заявки и фиксируйте нужное количество коробок.</p>
    </header>

    <?php if (empty($products)): ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <span class="material-symbols-rounded text-2xl">inventory_2</span>
            </div>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Оптовые позиции готовятся</h2>
            <p class="mt-1 text-sm text-slate-600">Мы скоро добавим коробки для предзаказа. Проверьте список позже.</p>
        </div>
    <?php else: ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($products as $product): ?>
                <?php
                $cardId = 'wholesale-card-' . $product['id'];
                $priceTiersJson = htmlspecialchars(json_encode($product['price_tiers'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                $basePrice = (int) floor((float) $product['price']);
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
                    class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-slate-200/70"
                >
                    <div class="relative">
                        <?php if (!empty($product['photo_url'])): ?>
                            <img
                                src="<?php echo htmlspecialchars($product['photo_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="aspect-square w-full object-cover"
                            >
                        <?php else: ?>
                            <div class="flex aspect-square w-full items-center justify-center bg-slate-100 text-slate-400">
                                <span class="material-symbols-rounded text-4xl">image</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 px-4 pb-4 pt-3">
                        <h2 class="text-base font-semibold leading-snug text-slate-900 sm:text-lg"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>

                        <div class="space-y-2 rounded-2xl bg-slate-50 p-3">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                <span class="inline-flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base">inventory</span>
                                    Количество коробок
                                </span>
                                <input
                                    type="number"
                                    min="1"
                                    max="50"
                                    step="1"
                                    value="1"
                                    inputmode="numeric"
                                    data-qty-value
                                    class="w-16 rounded-lg bg-white px-2 py-1.5 text-base font-bold text-slate-900 shadow-inner shadow-rose-100/60 text-center"
                                >
                            </div>
                            <input
                                type="range"
                                min="1"
                                max="50"
                                step="1"
                                value="1"
                                data-qty
                                class="range-slider h-1.5 w-full appearance-none rounded-full bg-slate-200 accent-rose-500"
                            >
                            <div class="flex justify-between text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                <span>1</span>
                                <span>25</span>
                                <span>50</span>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 rounded-2xl bg-white px-2.5 py-2 shadow-sm">
                            <span class="text-base font-bold text-rose-600" data-actual-price>—</span>
                            <button type="button" data-add-to-cart class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                                <span class="material-symbols-rounded text-base">shopping_cart</span>
                                В корзину
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

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
</style>

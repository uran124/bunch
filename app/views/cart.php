<?php /** @var array $items */ ?>
<?php /** @var array $totals */ ?>
<?php /** @var array $accessories */ ?>

<?php $isEmpty = empty($items); ?>

<div class="space-y-6">
    <section class="space-y-2">
        <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Корзина</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Готовим заказ к оформлению</h1>
        <p class="text-sm text-slate-600">Проверьте позиции, выберите доставку или самовывоз и перейдите к подтверждению.</p>
    </section>

    <?php if ($isEmpty): ?>
        <div class="space-y-4 rounded-3xl border border-dashed border-slate-200 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <span class="material-symbols-rounded text-2xl">shopping_cart</span>
            </div>
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-slate-900">Корзина пуста</h2>
                <p class="text-sm text-slate-600">Добавьте букет на главной странице, чтобы перейти к оформлению.</p>
            </div>
            <a href="/?page=home" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                <span class="material-symbols-rounded text-base">add_shopping_cart</span>
                Перейти в каталог
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <?php
                    $selectedAttributeIds = [];
                    foreach ($item['attributes'] as $attr) {
                        $selectedAttributeIds[(int) $attr['attribute_id']] = (int) $attr['value_id'];
                    }
                    $availableAttributes = $productAttributes[$item['product_id']] ?? [];
                    ?>
                    <?php
                    $priorityAttributes = [1, 3, 8];
                    $previewAttributes = [];
                    foreach ($item['attributes'] as $attr) {
                        if (in_array((int) $attr['attribute_id'], $priorityAttributes, true)) {
                            $previewAttributes[(int) $attr['attribute_id']] = $attr['label'] . ': ' . $attr['value'];
                        }
                    }
                    if (empty($previewAttributes)) {
                        foreach (array_slice($item['attributes'], 0, 3) as $attr) {
                            $previewAttributes[] = $attr['label'] . ': ' . $attr['value'];
                        }
                    }

                    $attributeTableData = array_map(static function ($attribute) use ($selectedAttributeIds) {
                        return [
                            'id' => (int) $attribute['id'],
                            'name' => $attribute['name'],
                            'applies_to' => $attribute['applies_to'],
                            'selected' => $selectedAttributeIds[(int) $attribute['id']] ?? null,
                            'values' => array_map(static function ($value) {
                                return [
                                    'id' => (int) $value['id'],
                                    'value' => $value['value'],
                                    'price_delta' => (float) ($value['price_delta'] ?? 0),
                                ];
                            }, $attribute['values'] ?? []),
                        ];
                    }, $availableAttributes);
                    ?>
                    <article
                        class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg"
                        data-cart-item
                        data-item-key="<?php echo htmlspecialchars($item['key'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-id="<?php echo (int) $item['product_id']; ?>"
                        data-selected-attributes="<?php echo htmlspecialchars(json_encode(array_values($selectedAttributeIds), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="h-28 w-28 overflow-hidden rounded-xl bg-slate-100 shadow-inner">
                                <?php if (!empty($item['photo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['photo_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center text-slate-400">
                                        <span class="material-symbols-rounded text-xl">image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <a
                                            href="#"
                                            class="group flex-1 space-y-1 rounded-xl border border-transparent p-1 transition hover:border-rose-100 hover:bg-rose-50/40"
                                            data-attribute-modal-trigger
                                            data-attribute-title="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-attribute-data="<?php echo htmlspecialchars(json_encode($attributeTableData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <p class="text-base font-semibold leading-tight text-slate-900">
                                                <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($item['stem_height_cm'])): ?>
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">· <?php echo (int) $item['stem_height_cm']; ?> см</span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="flex flex-col gap-0.5 text-xs text-slate-600" data-attribute-preview>
                                                <?php foreach ($previewAttributes as $line): ?>
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-200"></span>
                                                        <?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <p class="text-[11px] font-semibold text-rose-600 underline underline-offset-2">Все атрибуты</p>
                                        </a>
                                        <div class="shrink-0 text-right sm:min-w-[150px]">
                                            <p class="text-lg font-bold text-rose-600" data-line-total><?php echo number_format((float) ($item['line_total'] ?? 0), 0, '.', ' '); ?> ₽</p>
                                            <p class="text-[11px] font-semibold text-slate-400">С учетом атрибутов</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2" data-quantity-control>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:text-rose-600" data-qty-decrease>
                                            <span class="material-symbols-rounded text-base">remove</span>
                                        </button>
                                        <input
                                            type="number"
                                            min="1"
                                            step="1"
                                            value="<?php echo (int) $item['qty']; ?>"
                                            class="h-9 w-16 rounded-lg border border-slate-200 bg-white text-center text-sm font-semibold text-slate-800 shadow-inner focus:border-rose-300 focus:outline-none"
                                            data-qty-input
                                        >
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:text-rose-600" data-qty-increase>
                                            <span class="material-symbols-rounded text-base">add</span>
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-rose-100 bg-white text-rose-600 shadow-sm shadow-rose-100 transition hover:-translate-y-0.5 hover:bg-rose-50"
                                        data-remove-item
                                    >
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>

                <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Добавить к заказу</h3>
                            <p class="text-xs text-slate-500">Сопутствующие товары сразу после основного состава.</p>
                        </div>
                    </div>
                    <?php if (empty($accessories)): ?>
                        <p class="text-sm text-slate-500">Каталог сопутствующих товаров пуст.</p>
                    <?php else: ?>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <?php foreach ($accessories as $product): ?>
                                <article class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 shadow-inner">
                                    <div class="h-14 w-14 overflow-hidden rounded-lg bg-white">
                                        <?php if (!empty($product['photo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['photo_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                                                <span class="material-symbols-rounded text-lg">image</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo number_format((float) $product['price'], 0, '.', ' '); ?> ₽</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm shadow-rose-100 transition hover:-translate-y-0.5"
                                        data-add-accessory
                                        data-product-id="<?php echo (int) $product['id']; ?>"
                                    >
                                        <span class="material-symbols-rounded text-base">add</span>
                                        В корзину
                                    </button>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php
                $today = date('Y-m-d');
                $hasSavedAddresses = !empty($addresses);
                $primaryAddress = $hasSavedAddresses ? $addresses[0] : null;
                ?>
                <section
                    class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    data-order-flow
                    data-addresses="<?php echo htmlspecialchars(json_encode($addresses, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                    data-primary-address="<?php echo htmlspecialchars($primaryAddress['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-delivery-zones="<?php echo htmlspecialchars(json_encode($deliveryZones, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                    data-delivery-pricing-version="<?php echo htmlspecialchars($deliveryPricingVersion ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-dadata-config="<?php echo htmlspecialchars(json_encode($dadataConfig ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                    data-test-addresses="<?php echo htmlspecialchars(json_encode($testAddresses ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <div class="flex flex-col gap-1">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Способ получения</p>
                        <h3 class="text-lg font-semibold text-slate-900">Самовывоз или доставка</h3>
                        <p class="text-sm text-slate-500">Заполните дату и время. Для доставки укажите адрес и получателя.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-order-mode="pickup" class="order-mode-btn flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm">
                            <span class="material-symbols-rounded text-base">storefront</span>
                            Самовывоз
                        </button>
                        <button type="button" data-order-mode="delivery" class="order-mode-btn flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                            <span class="material-symbols-rounded text-base">local_shipping</span>
                            Доставка
                        </button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2" data-schedule-fields>
                        <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                            Дата
                            <input type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" data-order-date>
                        </label>
                        <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                            Время
                            <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" placeholder="Ближайшее" data-order-time>
                        </label>
                    </div>

                    <div class="space-y-3 rounded-xl border border-dashed border-rose-100 bg-rose-50/50 p-3" data-delivery-extra hidden>
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">
                            <span class="material-symbols-rounded text-base">location_on</span>
                            Параметры доставки
                        </div>

                        <?php if ($hasSavedAddresses): ?>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <label class="flex flex-1 flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Сохраненные адреса
                                    <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" data-address-select>
                                        <?php foreach ($addresses as $address): ?>
                                            <option
                                                value="<?php echo (int) ($address['raw']['id'] ?? 0); ?>"
                                                data-address-text="<?php echo htmlspecialchars($address['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo !empty($address['is_primary']) ? 'selected' : ''; ?>
                                            >
                                                <?php echo htmlspecialchars($address['label'] . ' — ' . $address['address'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-address-new>
                                    <span class="material-symbols-rounded text-base">add_location_alt</span>
                                    Другой адрес
                                </button>
                            </div>
                        <?php endif; ?>

                        <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                            Адрес доставки
                            <input
                                type="text"
                                placeholder="Город, улица, дом"
                                value="<?php echo htmlspecialchars($primaryAddress['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none"
                                data-address-input
                            >
                        </label>

                        <div class="rounded-xl border border-slate-100 bg-white px-3 py-2 text-xs text-slate-600" data-delivery-pricing-hint>
                            Введите адрес, чтобы получить подсказку DaData, геокодировать точку и определить зону доставки.
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Получатель</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="recipient-btn inline-flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 shadow-sm" data-recipient-mode="self">
                                    <span class="material-symbols-rounded text-base">person</span>
                                    Получаю я
                                </button>
                                <button type="button" class="recipient-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm" data-recipient-mode="other">
                                    <span class="material-symbols-rounded text-base">group</span>
                                    Получает другой человек
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2" data-recipient-extra hidden>
                            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                Имя получателя
                                <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" placeholder="Для кого букет?" data-recipient-name>
                            </label>
                            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                Телефон получателя
                                <input type="tel" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" placeholder="+7 (999) 123-45-67" data-recipient-phone>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Пожелания к заказу</p>
                        <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                            Комментарий
                            <textarea rows="3" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-rose-300 focus:outline-none" placeholder="Подъезд, домофон, пожелания по букету" data-order-comment></textarea>
                        </label>
                    </div>
                </section>
            </div>

            <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-lg font-bold text-rose-600">
                        <span>Сумма заказа</span>
                        <span data-cart-total><?php echo number_format((float) ($totals['total'] ?? 0), 0, '.', ' '); ?> ₽</span>
                    </div>
                    <p class="text-xs text-slate-500">Учитываем количество стеблей, выбранные атрибуты и сопутствующие позиции.</p>
                </div>

                <div class="space-y-3 rounded-xl bg-slate-50 p-3">
                    <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                        <span class="inline-flex items-center gap-2">
                            <span class="material-symbols-rounded text-base">inventory_2</span>
                            Позиции
                        </span>
                        <span data-cart-count-static><?php echo (int) ($totals['count'] ?? 0); ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                        <span class="inline-flex items-center gap-2">
                            <span class="material-symbols-rounded text-base">sell</span>
                            Промокод
                        </span>
                        <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Добавить</button>
                    </div>
                </div>

                <button class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-submit-order>
                    <span class="material-symbols-rounded text-base">done_all</span>
                    Оформить заказ
                </button>
            </aside>
        </div>

        <div class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40 px-4 py-6" data-attribute-modal>
            <div class="w-full max-w-xl rounded-2xl bg-white p-4 shadow-2xl shadow-slate-500/20" data-attribute-modal-card>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Атрибуты товара</p>
                        <h3 class="text-lg font-semibold text-slate-900" data-attribute-modal-title>Параметры</h3>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-attribute-modal-close>
                        <span class="material-symbols-rounded text-base">close</span>
                    </button>
                </div>

                <div class="space-y-2 py-3" data-attribute-modal-body></div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3" data-attribute-modal-actions>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-700" data-attribute-modal-close>
                        Отмена
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-attribute-modal-apply>
                        <span class="material-symbols-rounded text-base">done_all</span>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

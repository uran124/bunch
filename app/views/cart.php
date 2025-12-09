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
                    <article
                        class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg"
                        data-cart-item
                        data-item-key="<?php echo htmlspecialchars($item['key'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-product-id="<?php echo (int) $item['product_id']; ?>"
                    >
                        <div class="flex gap-3">
                            <div class="h-24 w-24 overflow-hidden rounded-xl bg-slate-100 shadow-inner">
                                <?php if (!empty($item['photo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['photo_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center text-slate-400">
                                        <span class="material-symbols-rounded text-xl">image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <h2 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                        <p class="text-xs uppercase tracking-wide text-slate-400" data-qty-label><?php echo htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8'); ?> стеблей</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-rose-600" data-line-total><?php echo number_format((float) ($item['line_total'] ?? 0), 0, '.', ' '); ?> ₽</p>
                                        <p class="text-[11px] font-semibold text-slate-400">С учетом атрибутов</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2">
                                    <div class="flex items-center gap-2" data-quantity-control>
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
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm shadow-rose-100 transition hover:-translate-y-0.5 hover:bg-rose-50"
                                        data-remove-item
                                    >
                                        <span class="material-symbols-rounded text-base">delete</span>
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <span class="material-symbols-rounded text-base text-emerald-500">tune</span>
                                    Настройте атрибуты
                                </div>
                                <?php if (empty($availableAttributes)): ?>
                                    <span class="text-xs font-semibold text-slate-400">Без дополнительных параметров</span>
                                <?php endif; ?>
                            </div>

                            <?php foreach ($availableAttributes as $attribute): ?>
                                <?php $selectedValueId = $selectedAttributeIds[(int) $attribute['id']] ?? null; ?>
                                <div class="space-y-1" data-attribute-group data-attribute-id="<?php echo (int) $attribute['id']; ?>">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($attribute['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <span class="text-[11px] font-semibold text-slate-400"><?php echo htmlspecialchars($attribute['applies_to'] === 'bouquet' ? 'к букету' : 'к стеблю', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($attribute['values'] as $value): ?>
                                            <?php
                                            $isSelected = (int) $value['id'] === (int) $selectedValueId;
                                            $priceDelta = (float) ($value['price_delta'] ?? 0);
                                            $priceLabel = $priceDelta > 0
                                                ? '+ ' . number_format($priceDelta, 0, '.', ' ') . ' ₽'
                                                : ($priceDelta < 0 ? '− ' . number_format(abs($priceDelta), 0, '.', ' ') . ' ₽' : '');
                                            ?>
                                            <button
                                                type="button"
                                                class="attribute-option inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs font-semibold transition <?php echo $isSelected ? 'border-rose-200 bg-white text-rose-700 shadow-sm shadow-rose-100 attribute-selected' : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:text-rose-700'; ?>"
                                                data-attribute-option
                                                data-attribute-id="<?php echo (int) $attribute['id']; ?>"
                                                data-value-id="<?php echo (int) $value['id']; ?>"
                                                data-selected="<?php echo $isSelected ? 'true' : 'false'; ?>"
                                            >
                                                <span><?php echo htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php if ($priceLabel !== ''): ?>
                                                    <span class="text-[11px] font-semibold text-rose-500"><?php echo $priceLabel; ?></span>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

                <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-order-flow>
                    <div class="flex flex-col gap-1">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Способ получения</p>
                        <h3 class="text-lg font-semibold text-slate-900">Выберите самовывоз или доставку</h3>
                        <p class="text-sm text-slate-500">Откройте окно настроек, чтобы уточнить дату, время и адрес.</p>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <button type="button" data-order-mode="pickup" class="order-mode-btn flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-left text-sm font-semibold text-slate-800 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-rounded text-base">storefront</span>
                                <div>
                                    <p>Самовывоз</p>
                                    <span class="block text-xs font-semibold text-slate-500" data-order-summary="pickup">Сегодня · Ближайшее</span>
                                </div>
                            </div>
                            <span class="material-symbols-rounded text-base text-slate-400">edit</span>
                        </button>

                        <button type="button" data-order-mode="delivery" class="order-mode-btn flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-left text-sm font-semibold text-slate-800 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-rounded text-base">local_shipping</span>
                                <div>
                                    <p>Доставка</p>
                                    <span class="block text-xs font-semibold text-slate-500" data-order-summary="delivery">Сегодня · Ближайшее</span>
                                </div>
                            </div>
                            <span class="material-symbols-rounded text-base text-slate-400">edit</span>
                        </button>
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

                <button class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                    <span class="material-symbols-rounded text-base">lock</span>
                    Перейти к оформлению
                </button>
            </aside>
        </div>

        <template id="order-template-pickup">
            <div class="space-y-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Дата самовывоза
                        <input type="date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-pickup-date>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Время
                        <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-pickup-time placeholder="Ближайшее">
                    </label>
                </div>
                <p class="text-xs text-slate-500">По умолчанию: Сегодня и ближайшее доступное время.</p>
            </div>
        </template>

        <template id="order-template-delivery">
            <div class="space-y-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Дата доставки
                        <input type="date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-delivery-date>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Время
                        <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-delivery-time placeholder="Ближайшее">
                    </label>
                </div>
                <?php if (!empty($addresses)): ?>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Сохраненные адреса
                        <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-delivery-saved>
                            <option value="">Выберите адрес из списка</option>
                            <?php foreach ($addresses as $address): ?>
                                <option
                                    value="<?php echo htmlspecialchars($address['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-raw="<?php echo htmlspecialchars(json_encode($address['raw'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    <?php echo htmlspecialchars($address['label'] . ' — ' . $address['address'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                    Адрес доставки
                    <input type="text" placeholder="Город, улица, дом" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" data-delivery-address>
                </label>

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

                <div class="grid gap-3 sm:grid-cols-2" data-recipient-extra hidden>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Имя получателя
                        <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="Для кого букет?" data-recipient-name>
                    </label>
                    <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                        Телефон получателя
                        <input type="tel" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="+7 (999) 123-45-67" data-recipient-phone>
                    </label>
                </div>
                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700" data-recipient-extra hidden>
                    Текст для записки
                    <textarea rows="2" maxlength="100" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="До 100 символов" data-recipient-note></textarea>
                </label>
            </div>
        </template>

        <div class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40 px-4 py-6" data-order-modal>
            <div class="w-full max-w-xl rounded-2xl bg-white p-4 shadow-2xl shadow-slate-500/20" data-order-modal-card>
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Настройки</p>
                        <h3 class="text-lg font-semibold text-slate-900" data-order-modal-title>Самовывоз</h3>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-rose-200 hover:text-rose-600" data-order-modal-close>
                        <span class="material-symbols-rounded text-base">close</span>
                    </button>
                </div>

                <div class="space-y-4 py-3" data-order-modal-body></div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-200" data-order-modal-close>Отмена</button>
                    <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700" data-order-modal-apply>Готово</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

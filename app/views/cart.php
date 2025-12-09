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
            <div class="space-y-3">
                <?php foreach ($items as $item): ?>
                    <article class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                        <div class="h-20 w-20 overflow-hidden rounded-xl bg-slate-100">
                            <?php if (!empty($item['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['photo_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <span class="material-symbols-rounded text-xl">image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8'); ?> стеблей</p>
                                </div>
                                <span class="text-base font-bold text-rose-600"><?php echo number_format((float) ($item['line_total'] ?? 0), 0, '.', ' '); ?> ₽</span>
                            </div>
                            <?php if (!empty($item['attributes'])): ?>
                                <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                    <?php foreach ($item['attributes'] as $attr): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-1">
                                            <span class="material-symbols-rounded text-base text-emerald-500">task_alt</span>
                                            <?php echo htmlspecialchars($attr['label'], ENT_QUOTES, 'UTF-8'); ?>:
                                            <?php echo htmlspecialchars($attr['value'], ENT_QUOTES, 'UTF-8'); ?> ·
                                            <?php echo htmlspecialchars($attr['scope'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-rose-600">
                                <button class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-slate-700 hover:border-rose-200 hover:text-rose-600" disabled>
                                    <span class="material-symbols-rounded text-base">edit</span>
                                    Изменить
                                </button>
                                <button class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-2 py-1" disabled>
                                    <span class="material-symbols-rounded text-base">delete</span>
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-order-flow>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-order-mode="pickup" class="order-mode-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-200 hover:text-rose-600">
                            <span class="material-symbols-rounded text-base">storefront</span>
                            Самовывоз
                        </button>
                        <button type="button" data-order-mode="delivery" class="order-mode-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-200 hover:text-rose-600">
                            <span class="material-symbols-rounded text-base">local_shipping</span>
                            Доставка
                        </button>
                        <button type="button" data-order-mode="subscription" class="order-mode-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-rose-200 hover:text-rose-600">
                            <span class="material-symbols-rounded text-base">autorenew</span>
                            Оформить подписку
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div data-order-section="pickup" class="space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Дата самовывоза
                                    <input type="date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Время
                                    <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                            </div>
                            <p class="text-xs text-slate-500">Заберите заказ в магазине Bunch flowers в выбранный интервал.</p>
                        </div>

                        <div data-order-section="delivery" class="hidden space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Дата доставки
                                    <input type="date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Время
                                    <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                            </div>
                            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                Адрес доставки
                                <input type="text" placeholder="Город, улица, дом, подъезд" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Имя получателя
                                    <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="Кому доставить букет?">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Телефон получателя
                                    <input type="tel" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="+7 (999) 123-45-67">
                                </label>
                            </div>
                        </div>

                        <div data-order-section="subscription" class="hidden space-y-3">
                            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                Интервал доставки
                                <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                    <option value="7">Каждые 7 дней</option>
                                    <option value="14">Каждые 14 дней</option>
                                    <option value="30">Каждый месяц</option>
                                </select>
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Дата первой доставки
                                    <input type="date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Время
                                    <input type="time" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                                </label>
                            </div>
                            <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                Адрес для подписки
                                <input type="text" placeholder="Город, улица, дом" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Имя получателя
                                    <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="Для кого подписка?">
                                </label>
                                <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                                    Телефон получателя
                                    <input type="tel" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="+7 (999) 123-45-67">
                                </label>
                            </div>
                        </div>

                        <label class="flex flex-col gap-1 text-sm font-semibold text-slate-700">
                            Комментарий к заказу
                            <textarea rows="3" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm" placeholder="Напишите важные нюансы: подъезд, домофон, пожелания по букету"></textarea>
                        </label>
                    </div>
                </section>

                <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Сопутствующие товары</h3>
                            <p class="text-xs text-slate-500">Можно добавить к заказу прямо отсюда.</p>
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
                        <span><?php echo (int) ($totals['count'] ?? 0); ?></span>
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
    <?php endif; ?>
</div>

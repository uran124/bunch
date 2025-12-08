<?php /** @var array $items */ ?>

<div class="space-y-6">
    <section class="space-y-2">
        <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Корзина</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Готовим заказ к оформлению</h1>
        <p class="text-sm text-slate-600">Проверьте позиции, выберите доставку или самовывоз и перейдите к подтверждению.</p>
    </section>

    <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <div class="space-y-3">
            <?php foreach ($items as $item): ?>
                <article class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="h-20 w-20 overflow-hidden rounded-xl bg-slate-100">
                        <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h2 class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8'); ?> стеблей</p>
                            </div>
                            <span class="text-base font-bold text-rose-600"><?php echo number_format($item['price'] * $item['qty'], 0, '.', ' '); ?> ₽</span>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                            <?php foreach ($item['attributes'] as $attr): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-1">
                                    <span class="material-symbols-rounded text-base text-emerald-500">task_alt</span>
                                    <?php echo htmlspecialchars($attr['label'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars($attr['value'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($attr['scope'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold text-rose-600">
                            <button class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-slate-700 hover:border-rose-200 hover:text-rose-600">
                                <span class="material-symbols-rounded text-base">edit</span>
                                Изменить
                            </button>
                            <button class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-2 py-1">
                                <span class="material-symbols-rounded text-base">delete</span>
                                Удалить
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                    <span>Итого без скидок</span>
                    <span class="line-through text-slate-400">8 250 ₽</span>
                </div>
                <div class="flex items-center justify-between text-lg font-bold text-rose-600">
                    <span>Фактическая сумма</span>
                    <span>7 450 ₽</span>
                </div>
                <p class="text-xs text-slate-500">Сумма учитывает скидки по объёму и атрибуты, привязанные к стеблям и букету.</p>
            </div>

            <div class="space-y-3 rounded-xl bg-slate-50 p-3">
                <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                    <span class="inline-flex items-center gap-2">
                        <span class="material-symbols-rounded text-base">local_shipping</span>
                        Доставка
                    </span>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Настроить</button>
                </div>
                <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                    <span class="inline-flex items-center gap-2">
                        <span class="material-symbols-rounded text-base">storefront</span>
                        Самовывоз
                    </span>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Настроить</button>
                </div>
                <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                    <span class="inline-flex items-center gap-2">
                        <span class="material-symbols-rounded text-base">sell</span>
                        Промокод
                    </span>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Применить</button>
                </div>
            </div>

            <button class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700">
                <span class="material-symbols-rounded text-base">lock</span>
                Перейти к оформлению
            </button>
        </aside>
    </div>
</div>

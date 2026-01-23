<?php /** @var array $levels */ ?>
<?php /** @var array $products */ ?>
<?php /** @var array $promoItems */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Кешбек</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Кешбек', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Тюльпанчики, уровни начисления и правила списания по товарам.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/admin"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <?php
        $tone = $message === 'saved' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-rose-50 text-rose-700 ring-rose-100';
        $label = $message === 'saved' ? 'Данные сохранены.' : 'Не удалось сохранить изменения.';
        ?>
        <div class="rounded-2xl px-4 py-3 text-sm font-semibold ring-1 <?php echo $tone; ?>">
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Уровни кешбека</p>
                <h2 class="text-lg font-semibold text-slate-900">Проценты начисления</h2>
            </div>
        </div>

        <div class="space-y-4">
            <?php foreach ($levels as $level): ?>
                <form class="grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 sm:grid-cols-[1.4fr_repeat(4,minmax(0,1fr))_auto] sm:items-end" action="/admin-cashback-level-save" method="post">
                    <input type="hidden" name="id" value="<?php echo (int) $level['id']; ?>">
                    <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                        Название уровня
                        <input
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                            type="text"
                            name="name"
                            value="<?php echo htmlspecialchars($level['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </label>
                    <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                        Поштучно, %
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_single" value="<?php echo htmlspecialchars((string) ($level['percent_single'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                        Пачками, %
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_pack" value="<?php echo htmlspecialchars((string) ($level['percent_pack'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                        Коробками, %
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_box" value="<?php echo htmlspecialchars((string) ($level['percent_box'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                        Акции, %
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_promo" value="<?php echo htmlspecialchars((string) ($level['percent_promo'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-700 sm:text-sm">
                        <span class="material-symbols-rounded text-base">save</span>
                        Сохранить
                    </button>
                </form>
            <?php endforeach; ?>

            <form class="grid gap-3 rounded-xl border border-dashed border-rose-200 bg-white p-3 sm:grid-cols-[1.4fr_repeat(4,minmax(0,1fr))_auto] sm:items-end" action="/admin-cashback-level-save" method="post">
                <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                    Новый уровень
                    <input
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        type="text"
                        name="name"
                        placeholder="Например: Золотой"
                        required
                    >
                </label>
                <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                    Поштучно, %
                    <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_single" value="0">
                </label>
                <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                    Пачками, %
                    <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_pack" value="0">
                </label>
                <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                    Коробками, %
                    <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_box" value="0">
                </label>
                <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600">
                    Акции, %
                    <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" type="number" step="0.1" min="0" name="percent_promo" value="0">
                </label>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 sm:text-sm">
                    <span class="material-symbols-rounded text-base">add</span>
                    Добавить
                </button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Товары</p>
            <h2 class="text-lg font-semibold text-slate-900">Где можно тратить и зарабатывать</h2>
        </div>
        <div class="space-y-3">
            <?php if (empty($products)): ?>
                <p class="text-sm text-slate-500">Нет активных товаров.</p>
            <?php else: ?>
                <?php
                $productTypeLabels = [
                    'regular' => 'Поштучно',
                    'small_wholesale' => 'Пачками',
                    'wholesale_box' => 'Коробками',
                    'promo' => 'Промо',
                    'auction' => 'Аукцион',
                    'lottery' => 'Лотерея',
                ];
                ?>
                <?php foreach ($products as $product): ?>
                    <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3" data-cashback-row data-entity="product" data-id="<?php echo (int) $product['id']; ?>">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($product['name'] ?? 'Без названия', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-500">
                                <?php echo htmlspecialchars($productTypeLabels[$product['product_type']] ?? $product['product_type'], ENT_QUOTES, 'UTF-8'); ?> ·
                                <?php echo htmlspecialchars($product['category'] ?? 'main', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <span>Тратить</span>
                                <span class="relative inline-flex h-7 w-12 cursor-pointer items-center" aria-label="Можно тратить тюльпанчики">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        data-cashback-toggle
                                        data-cashback-spend
                                        data-state="<?php echo (int) ($product['allow_tulip_spend'] ?? 1); ?>"
                                        <?php echo (int) ($product['allow_tulip_spend'] ?? 1) === 1 ? 'checked' : ''; ?>
                                    >
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <span>Зарабатывать</span>
                                <span class="relative inline-flex h-7 w-12 cursor-pointer items-center" aria-label="Начислять тюльпанчики">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        data-cashback-toggle
                                        data-cashback-earn
                                        data-state="<?php echo (int) ($product['allow_tulip_earn'] ?? 1); ?>"
                                        <?php echo (int) ($product['allow_tulip_earn'] ?? 1) === 1 ? 'checked' : ''; ?>
                                    >
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Акционные товары</p>
            <h2 class="text-lg font-semibold text-slate-900">Правила тюльпанчиков в акциях</h2>
        </div>
        <div class="space-y-3">
            <?php if (empty($promoItems)): ?>
                <p class="text-sm text-slate-500">Акционных товаров нет.</p>
            <?php else: ?>
                <?php foreach ($promoItems as $promo): ?>
                    <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3" data-cashback-row data-entity="promo" data-id="<?php echo (int) $promo['id']; ?>">
                        <div>
                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($promo['title'] ?? 'Акция', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-500">Цена: <?php echo number_format((int) ($promo['price'] ?? 0), 0, '.', ' '); ?> ₽</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <span>Тратить</span>
                                <span class="relative inline-flex h-7 w-12 cursor-pointer items-center" aria-label="Можно тратить тюльпанчики">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        data-cashback-toggle
                                        data-cashback-spend
                                        data-state="<?php echo (int) ($promo['allow_tulip_spend'] ?? 1); ?>"
                                        <?php echo (int) ($promo['allow_tulip_spend'] ?? 1) === 1 ? 'checked' : ''; ?>
                                    >
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <span>Зарабатывать</span>
                                <span class="relative inline-flex h-7 w-12 cursor-pointer items-center" aria-label="Начислять тюльпанчики">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        data-cashback-toggle
                                        data-cashback-earn
                                        data-state="<?php echo (int) ($promo['allow_tulip_earn'] ?? 1); ?>"
                                        <?php echo (int) ($promo['allow_tulip_earn'] ?? 1) === 1 ? 'checked' : ''; ?>
                                    >
                                    <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</section>

<script>
    document.querySelectorAll('[data-cashback-toggle]').forEach((toggle) => {
        toggle.addEventListener('change', async () => {
            const row = toggle.closest('[data-cashback-row]');
            if (!row) return;

            const entity = row.dataset.entity;
            const id = Number(row.dataset.id || 0);
            const spendToggle = row.querySelector('[data-cashback-spend]');
            const earnToggle = row.querySelector('[data-cashback-earn]');
            if (!spendToggle || !earnToggle || !id) return;

            const previousSpend = spendToggle.dataset.state === '1';
            const previousEarn = earnToggle.dataset.state === '1';
            const allowSpend = spendToggle.checked;
            const allowEarn = earnToggle.checked;
            spendToggle.disabled = true;
            earnToggle.disabled = true;

            const endpoint = entity === 'promo' ? '/admin-cashback-promo-toggle' : '/admin-cashback-product-toggle';
            const payload = entity === 'promo'
                ? { promoId: id, allowSpend, allowEarn }
                : { productId: id, allowSpend, allowEarn };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    spendToggle.checked = previousSpend;
                    earnToggle.checked = previousEarn;
                } else {
                    spendToggle.dataset.state = allowSpend ? '1' : '0';
                    earnToggle.dataset.state = allowEarn ? '1' : '0';
                }
            } catch (error) {
                spendToggle.checked = previousSpend;
                earnToggle.checked = previousEarn;
                spendToggle.dataset.state = previousSpend ? '1' : '0';
                earnToggle.dataset.state = previousEarn ? '1' : '0';
            } finally {
                spendToggle.disabled = false;
                earnToggle.disabled = false;
            }
        });
    });
</script>

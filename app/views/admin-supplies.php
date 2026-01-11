<?php /** @var array $supplies */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="space-y-3">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Каталог · Поставки</p>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-supply-standing" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">add_circle</span>
                Добавить стендинг
            </a>
            <a href="/?page=admin-supply-single" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">calendar_add_on</span>
                Разовая поставка
            </a>
            <a href="/?page=admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <?php if (!empty($message) && $message === 'created'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="material-symbols-rounded text-lg">check_circle</span>
            <div>
                <p class="font-semibold">Поставка сохранена.</p>
                <p>Карточка попадёт в список запланированных поставок со следующей датой поставки.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'card-activated'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="material-symbols-rounded text-lg">deployed_code</span>
            <div>
                <p class="font-semibold">Карточка активирована.</p>
                <p>Позиция доступна на витрине или в мелкооптовом блоке.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'card-deactivated'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <span class="material-symbols-rounded text-lg">do_not_disturb_on</span>
            <div>
                <p class="font-semibold">Карточка деактивирована.</p>
                <p>Товар скрыт с витрины или из мелкооптового списка.</p>
            </div>
        </div>
    <?php elseif (!empty($message) && $message === 'notfound'): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <span class="material-symbols-rounded text-lg">error</span>
            <div>
                <p class="font-semibold">Поставка не найдена.</p>
                <p>Обновите список и попробуйте снова.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Список ближайших поставок</p>
            <h2 class="text-lg font-semibold text-slate-900">План по датам и доступным коробкам</h2>
        </div>
        <?php
        $standingSupplies = array_values(array_filter($supplies, static fn(array $supply): bool => !empty($supply['is_standing'])));
        $singleSupplies = array_values(array_filter($supplies, static fn(array $supply): bool => empty($supply['is_standing'])));
        $formatDate = static function (?string $date): string {
            if (!$date) {
                return '—';
            }

            try {
                return (new DateTime($date))->format('d.m');
            } catch (Exception $e) {
                return '—';
            }
        };
        ?>
        <div class="divide-y divide-slate-100">
            <section class="px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Стендинги</h3>
                <?php if (empty($standingSupplies)): ?>
                    <div class="mt-2 text-sm text-slate-600">Добавьте стендинг, чтобы увидеть расписание.</div>
                <?php else: ?>
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Позиция</th>
                                    <th class="px-4 py-3 text-center">Количество</th>
                                    <th class="px-4 py-3 text-center">Остаток</th>
                                    <th class="px-4 py-3 text-center">Поставка</th>
                                    <th class="px-4 py-3 text-right">Иконки</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($standingSupplies as $supply): ?>
                                    <?php
                                    $title = trim(($supply['flower_name'] ?? '') . ' ' . ($supply['variety'] ?? ''));
                                    $packsAvailable = (int) ($supply['packs_available'] ?? 0);
                                    $boxesTotal = (int) ($supply['boxes_total'] ?? 0);
                                    $packsPerBox = (int) ($supply['packs_per_box'] ?? 0);
                                    $stemsPerPack = (int) ($supply['stems_per_pack'] ?? 0);
                                    $totalStems = $boxesTotal * $packsPerBox * $stemsPerPack;
                                    $freeStems = $packsAvailable * $stemsPerPack;
                                    $quantityFormula = $boxesTotal . '×' . $packsPerBox . '×' . $stemsPerPack;
                                    $lastDeliveryLabel = $formatDate($supply['actual_delivery_date'] ?? null);
                                    $nextDeliveryLabel = $formatDate($supply['next_delivery'] ?? null);
                                    $hasProduct = !empty($supply['has_product_card']);
                                    $hasWholesale = !empty($supply['has_wholesale_card']);
                                    $hasBox = !empty($supply['has_box_card']);
                                    ?>
                                    <tr id="supply-<?php echo (int) $supply['id']; ?>" class="hover:bg-slate-50/60">
                                        <td class="px-4 py-3 text-left">
                                            <div class="flex flex-col gap-1">
                                                <a href="/?page=admin-supply-edit&id=<?php echo (int) $supply['id']; ?>" class="font-semibold text-slate-900 underline-offset-4 hover:text-emerald-700 hover:underline">
                                                    <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                                <span class="text-xs text-slate-500"><?php echo (int) ($supply['stem_height_cm'] ?? 0); ?> см · <?php echo htmlspecialchars($quantityFormula, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                            <?php echo (int) $totalStems; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                            <?php echo (int) $freeStems; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">
                                            <?php echo htmlspecialchars($lastDeliveryLabel, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($nextDeliveryLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2 text-sm font-semibold">
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="retail">
                                                    <input type="hidden" name="activate" value="<?php echo $hasProduct ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasProduct ? 'Деактивировать розничную карточку' : 'Активировать розничную карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasProduct ? 'text-emerald-500' : 'text-slate-300'; ?>">local_florist</span>
                                                    </button>
                                                </form>
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="wholesale">
                                                    <input type="hidden" name="activate" value="<?php echo $hasWholesale ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasWholesale ? 'Деактивировать мелкооптовую карточку' : 'Активировать мелкооптовую карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasWholesale ? 'text-emerald-500' : 'text-slate-300'; ?>">deployed_code</span>
                                                    </button>
                                                </form>
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="box">
                                                    <input type="hidden" name="activate" value="<?php echo $hasBox ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasBox ? 'Деактивировать коробочную карточку' : 'Активировать коробочную карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasBox ? 'text-emerald-500' : 'text-slate-300'; ?>">inventory_2</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
            <section class="px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Разовые поставки</h3>
                <?php if (empty($singleSupplies)): ?>
                    <div class="mt-2 text-sm text-slate-600">Добавьте разовую поставку, чтобы увидеть расписание.</div>
                <?php else: ?>
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Позиция</th>
                                    <th class="px-4 py-3 text-center">Количество</th>
                                    <th class="px-4 py-3 text-center">Остаток</th>
                                    <th class="px-4 py-3 text-center">Поставка</th>
                                    <th class="px-4 py-3 text-right">Иконки</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($singleSupplies as $supply): ?>
                                    <?php
                                    $title = trim(($supply['flower_name'] ?? '') . ' ' . ($supply['variety'] ?? ''));
                                    $packsAvailable = (int) ($supply['packs_available'] ?? 0);
                                    $boxesTotal = (int) ($supply['boxes_total'] ?? 0);
                                    $packsPerBox = (int) ($supply['packs_per_box'] ?? 0);
                                    $stemsPerPack = (int) ($supply['stems_per_pack'] ?? 0);
                                    $totalStems = $boxesTotal * $packsPerBox * $stemsPerPack;
                                    $freeStems = $packsAvailable * $stemsPerPack;
                                    $quantityFormula = $boxesTotal . '×' . $packsPerBox . '×' . $stemsPerPack;
                                    $lastDeliveryLabel = $formatDate($supply['actual_delivery_date'] ?? null);
                                    $nextDeliveryLabel = $formatDate($supply['next_delivery'] ?? null);
                                    $hasProduct = !empty($supply['has_product_card']);
                                    $hasWholesale = !empty($supply['has_wholesale_card']);
                                    $hasBox = !empty($supply['has_box_card']);
                                    ?>
                                    <tr id="supply-<?php echo (int) $supply['id']; ?>" class="hover:bg-slate-50/60">
                                        <td class="px-4 py-3 text-left">
                                            <div class="flex flex-col gap-1">
                                                <a href="/?page=admin-supply-edit&id=<?php echo (int) $supply['id']; ?>" class="font-semibold text-slate-900 underline-offset-4 hover:text-emerald-700 hover:underline">
                                                    <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                                <span class="text-xs text-slate-500"><?php echo (int) ($supply['stem_height_cm'] ?? 0); ?> см · <?php echo htmlspecialchars($quantityFormula, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                            <?php echo (int) $totalStems; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                            <?php echo (int) $freeStems; ?>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">
                                            <?php echo htmlspecialchars($lastDeliveryLabel, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($nextDeliveryLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2 text-sm font-semibold">
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="retail">
                                                    <input type="hidden" name="activate" value="<?php echo $hasProduct ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasProduct ? 'Деактивировать розничную карточку' : 'Активировать розничную карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasProduct ? 'text-emerald-500' : 'text-slate-300'; ?>">local_florist</span>
                                                    </button>
                                                </form>
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="wholesale">
                                                    <input type="hidden" name="activate" value="<?php echo $hasWholesale ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasWholesale ? 'Деактивировать мелкооптовую карточку' : 'Активировать мелкооптовую карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasWholesale ? 'text-emerald-500' : 'text-slate-300'; ?>">deployed_code</span>
                                                    </button>
                                                </form>
                                                <form action="/?page=admin-supply-toggle-card" method="post">
                                                    <input type="hidden" name="supply_id" value="<?php echo (int) $supply['id']; ?>">
                                                    <input type="hidden" name="card_type" value="box">
                                                    <input type="hidden" name="activate" value="<?php echo $hasBox ? '0' : '1'; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-2 text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700" title="<?php echo $hasBox ? 'Деактивировать коробочную карточку' : 'Активировать коробочную карточку'; ?>">
                                                        <span class="material-symbols-rounded text-base <?php echo $hasBox ? 'text-emerald-500' : 'text-slate-300'; ?>">inventory_2</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

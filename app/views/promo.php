<?php
/** @var array $pageMeta */
/** @var array $oneTimeItems */
/** @var array $promoCategories */
$hasPromos = !empty($oneTimeItems);
?>

<section class="space-y-4 sm:space-y-6">
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-r from-rose-50 via-white to-emerald-50 p-6 shadow-sm sm:p-8">
        <div class="space-y-3 sm:max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-rose-500">Акции и спецпредложения</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Разовые акции и лимитированные предложения</h1>
            <p class="text-sm leading-relaxed text-slate-600 sm:text-base">
                Здесь собираем предложения с ограниченным количеством и сезонные спеццены.
                Успейте забрать редкие позиции, пока они доступны.
            </p>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                    <span class="material-symbols-rounded text-base">verified</span>
                    Прозрачные условия
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-100">
                    <span class="material-symbols-rounded text-base">hourglass_empty</span>
                    Лимиты по времени и количеству
                </span>
            </div>
        </div>
        <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
            <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-rose-200 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-40 w-40 rounded-full bg-emerald-200 blur-3xl"></div>
        </div>
    </div>
    <?php if (!$hasPromos): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
            Пока нет активных предложений. Загляните позже — мы регулярно обновляем подборку акций.
        </div>
    <?php endif; ?>

    <?php if ($hasPromos): ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-promo-items>
            <?php foreach ($oneTimeItems as $item): ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-promo-item data-promo-type="promo">
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-32 w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-32 w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-1 flex-col space-y-3 p-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-rose-700">
                                <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                <span class="material-symbols-rounded text-sm">event</span>
                                <?php echo htmlspecialchars($item['period'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-base font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-lg font-semibold text-rose-700"><?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-sm text-slate-600"><?php echo htmlspecialchars($item['stock'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <button class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                            <span class="material-symbols-rounded text-base">add_shopping_cart</span>
                            Забронировать
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

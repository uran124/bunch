<?php
/** @var array $pageMeta */
/** @var array $auctions */
/** @var array $lotteries */
/** @var array $oneTimeItems */
?>

<section class="space-y-4 sm:space-y-6">
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-r from-rose-50 via-white to-emerald-50 p-6 shadow-sm sm:p-8">
        <div class="space-y-3 sm:max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-rose-500">Акции и спецпредложения</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Аукционы, лотереи и разовые товары</h1>
            <p class="text-sm leading-relaxed text-slate-600 sm:text-base">
                Следите за редкими поставками, участвуйте в аукционах и розыгрышах, успевайте забрать последние стебли по спеццене.
                Мы фиксируем время, лимиты и прозрачные правила участия.
            </p>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                    <span class="material-symbols-rounded text-base">verified</span>
                    Честные условия торгов
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

    <div class="space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Аукционы</p>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Поднимаем ставки за редкие позиции</h2>
            </div>
            <span class="hidden items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 sm:inline-flex">
                <span class="material-symbols-rounded text-base">gavel</span>
                Live-формат
            </span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($auctions as $auction): ?>
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <?php if (!empty($auction['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($auction['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($auction['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-44 w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-44 w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="space-y-3 p-4">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide">
                            <span class="inline-flex items-center gap-2 text-rose-600">
                                <span class="material-symbols-rounded text-base">rocket_launch</span>
                                <?php echo htmlspecialchars($auction['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-700">
                                <span class="material-symbols-rounded text-sm text-emerald-600">schedule</span>
                                <?php echo htmlspecialchars($auction['time'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($auction['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-800">
                            <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-3 py-1 text-rose-700">
                                <?php echo htmlspecialchars($auction['startPrice'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-3 py-1 text-emerald-700">
                                <?php echo htmlspecialchars($auction['currentBid'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5">
                            <span class="material-symbols-rounded text-base">how_to_vote</span>
                            Сделать ставку
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Лотереи</p>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Розыгрыши с прозрачными условиями</h2>
            </div>
            <span class="hidden items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 sm:inline-flex">
                <span class="material-symbols-rounded text-base">confirmation_number</span>
                Случайный выбор победителя
            </span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($lotteries as $lottery): ?>
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <?php if (!empty($lottery['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($lottery['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-44 w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-44 w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="material-symbols-rounded text-3xl">image</span>
                        </div>
                    <?php endif; ?>
                    <div class="space-y-3 p-4">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1">
                                <span class="material-symbols-rounded text-sm text-rose-500">groups</span>
                                <?php echo htmlspecialchars($lottery['spots'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                <span class="material-symbols-rounded text-sm">schedule</span>
                                <?php echo htmlspecialchars($lottery['draw'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold leading-tight text-slate-900"><?php echo htmlspecialchars($lottery['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-sm font-semibold text-rose-700"><?php echo htmlspecialchars($lottery['entry'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:-translate-y-0.5 hover:bg-rose-100">
                            <span class="material-symbols-rounded text-base">confirmation_number</span>
                            Купить билет
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="rounded-3xl border border-amber-100 bg-amber-50/60 p-5 text-sm text-slate-700 shadow-sm sm:p-6">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-amber-700">
                <span class="material-symbols-rounded text-base">verified</span>
                Прозрачная лотерея
            </div>
            <p class="mt-3 text-base font-semibold text-slate-900">
                👉 Лотерея = товар с фиксированным пулом билетов + публичное состояние каждого билета + неизменяемая история.
            </p>
            <p class="mt-2 text-sm text-slate-600">
                Если клиент в любой момент может увидеть все билеты, понять кто и когда занял билет и убедиться, что номера не меняются задним числом — доверие возникает автоматически, без объяснений.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-slate-600">
                <li class="flex gap-2">
                    <span class="material-symbols-rounded text-base text-amber-600">confirmation_number</span>
                    Все билеты создаются заранее, количество фиксируется при создании и не меняется.
                </li>
                <li class="flex gap-2">
                    <span class="material-symbols-rounded text-base text-amber-600">visibility</span>
                    Публично видно состояние каждого билета: свободен или занят, только последние 4 цифры телефона.
                </li>
                <li class="flex gap-2">
                    <span class="material-symbols-rounded text-base text-amber-600">history</span>
                    История действий по билетам хранится неизменно и позволяет доказать порядок событий.
                </li>
            </ul>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-700">
                «Все билеты создаются заранее. Вы видите, какие номера свободны и какие уже куплены — с последними 4 цифрами телефона участников.»
            </p>
        </div>
    </div>

    <div class="space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Разовые товары</p>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Акции с ограниченным количеством</h2>
            </div>
            <span class="hidden items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 sm:inline-flex">
                <span class="material-symbols-rounded text-base">local_offer</span>
                Подготовили цены ниже обычных
            </span>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <?php foreach ($oneTimeItems as $item): ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
    </div>
</section>

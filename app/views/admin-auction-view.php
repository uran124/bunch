<?php /** @var array $lot */ ?>
<?php /** @var array $bids */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<?php
$finalPrice = $lot['winning_amount'] ?? $lot['current_price'];
$winnerLabel = ($lot['winner_last4'] ?? '----') !== '----' ? '…' . htmlspecialchars($lot['winner_last4'], ENT_QUOTES, 'UTF-8') : 'Победитель не выбран';
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Завершённый лот</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Завершённый лот', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Просмотр итоговой информации, участников и победителя.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К списку лотов
            </a>
        </div>
    </header>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Лот #<?php echo (int) $lot['id']; ?></p>
            <h2 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($lot['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($lot['description'] ?? 'Описание отсутствует', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="grid gap-3 text-sm text-slate-600">
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="font-semibold text-slate-700">Статус</span>
                <span><?php echo htmlspecialchars($lot['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="font-semibold text-slate-700">Победитель</span>
                <span><?php echo $winnerLabel; ?></span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="font-semibold text-slate-700">Итоговая цена</span>
                <span><?php echo number_format((float) $finalPrice, 2, '.', ' '); ?> ₽</span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="font-semibold text-slate-700">Период</span>
                <span><?php echo !empty($lot['starts_at']) ? htmlspecialchars($lot['starts_at'], ENT_QUOTES, 'UTF-8') : '—'; ?> · <?php echo !empty($lot['ends_at']) ? htmlspecialchars($lot['ends_at'], ENT_QUOTES, 'UTF-8') : '—'; ?></span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                <span class="font-semibold text-slate-700">Блиц-цена</span>
                <span><?php echo $lot['blitz_price'] !== null ? number_format((float) $lot['blitz_price'], 2, '.', ' ') . ' ₽' : '—'; ?></span>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-5 py-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Участники</p>
                <h3 class="text-lg font-semibold text-slate-900">Ставки по лоту</h3>
            </div>
        </div>
        <div class="grid grid-cols-[120px_1fr_1fr] gap-4 border-b border-slate-100 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Ставка</span>
            <span>Участник</span>
            <span>Дата</span>
        </div>
        <?php if (empty($bids)): ?>
            <div class="px-5 py-4 text-sm text-slate-500">Ставок по лоту ещё не было.</div>
        <?php endif; ?>
        <?php foreach ($bids as $bid): ?>
            <article class="grid grid-cols-[120px_1fr_1fr] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="text-sm font-semibold text-slate-900"><?php echo number_format((float) $bid['amount'], 2, '.', ' '); ?> ₽</div>
                <div class="text-sm text-slate-700">…<?php echo htmlspecialchars($bid['phone_last4'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-sm text-slate-500"><?php echo htmlspecialchars($bid['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

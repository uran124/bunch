<?php
/** @var array $status */
/** @var string $statusKey */
/** @var int $orderId */
/** @var string|null $orderNumber */
/** @var string $returnLink */
/** @var string $returnLabel */

$styles = [
    'result' => [
        'card' => 'border-sky-200 bg-sky-50',
        'text' => 'text-sky-700',
        'icon' => 'text-sky-600',
        'button' => 'bg-sky-600 shadow-sky-200 hover:bg-sky-700',
    ],
    'success' => [
        'card' => 'border-emerald-200 bg-emerald-50',
        'text' => 'text-emerald-700',
        'icon' => 'text-emerald-600',
        'button' => 'bg-emerald-600 shadow-emerald-200 hover:bg-emerald-700',
    ],
    'fail' => [
        'card' => 'border-rose-200 bg-rose-50',
        'text' => 'text-rose-700',
        'icon' => 'text-rose-600',
        'button' => 'bg-rose-600 shadow-rose-200 hover:bg-rose-700',
    ],
];

$style = $styles[$statusKey] ?? $styles['result'];
?>

<section class="mx-auto flex w-full max-w-2xl flex-col gap-6">
    <div class="rounded-3xl border bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl <?php echo htmlspecialchars($style['card'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="material-symbols-rounded text-2xl <?php echo htmlspecialchars($style['icon'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($status['icon'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-slate-900"><?php echo htmlspecialchars($status['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if (!empty($orderNumber)): ?>
                    <p class="text-sm text-slate-500">Заказ <?php echo htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($status['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a
            href="<?php echo htmlspecialchars($returnLink, ENT_QUOTES, 'UTF-8'); ?>"
            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 <?php echo htmlspecialchars($style['button'], ENT_QUOTES, 'UTF-8'); ?>"
        >
            <span class="material-symbols-rounded text-base">arrow_forward</span>
            <?php echo htmlspecialchars($returnLabel, ENT_QUOTES, 'UTF-8'); ?>
        </a>

        <?php if ($orderId > 0 && Auth::check()): ?>
            <a
                href="<?php echo htmlspecialchars('/order-payment?id=' . $orderId, ENT_QUOTES, 'UTF-8'); ?>"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">payments</span>
                Повторить оплату
            </a>
        <?php endif; ?>
    </div>
</section>

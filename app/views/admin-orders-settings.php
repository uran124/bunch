<?php /** @var array $statusOptions */ ?>
<?php /** @var array $enabledStatuses */ ?>
<?php /** @var array $paymentMethodOptions */ ?>
<?php /** @var array $enabledPaymentMethods */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $requiredStatuses = ['new', 'confirmed', 'completed', 'cancelled', 'returned']; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Заказы · Настройки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Настройки заказов', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Включайте и отключайте промежуточные статусы цепочки заказа, а также способы оплаты, которые доступны на сайте.</p>
        </div>
        <a href="/admin" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В админку
        </a>
    </header>

    <?php if (($message ?? null) === 'saved'): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Настройки заказов сохранены.</div>
    <?php endif; ?>

    <form method="post" action="/admin-orders-settings" class="grid gap-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Статусы заказа</h2>
                    <p class="mt-1 text-sm text-slate-500">Отключённые статусы не будут доступны для ручного выбора в админке. Уже существующие заказы с таким статусом продолжат отображаться корректно.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-100">
                    <span class="material-symbols-rounded text-base">checklist</span>
                    Цепочка заказа
                </span>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <?php $isRequired = in_array($value, $requiredStatuses, true); ?>
                    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-base text-emerald-500">check_circle</span>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="text-xs font-normal text-slate-400"><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <input type="checkbox" name="statuses[]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($value, $enabledStatuses, true) ? 'checked' : ''; ?> <?php echo $isRequired ? 'disabled' : ''; ?> class="h-5 w-5 rounded border-slate-300 text-rose-500 focus:ring-rose-200">
                        <?php if ($isRequired): ?>
                            <input type="hidden" name="statuses[]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-slate-900">Способы оплаты</h2>
                <p class="mt-1 text-sm text-slate-500">Статус оплаты хранится отдельно: 0 — не оплачен, 1 — наличными при получении, 2 — оплачен картой на сайте.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <?php foreach ($paymentMethodOptions as $value => $label): ?>
                    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800">
                        <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="checkbox" name="payment_methods[]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($value, $enabledPaymentMethods, true) ? 'checked' : ''; ?> class="h-5 w-5 rounded border-slate-300 text-rose-500 focus:ring-rose-200">
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600 hover:shadow-md">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить настройки
            </button>
        </div>
    </form>
</section>

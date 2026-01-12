<?php $pageMeta = $pageMeta ?? []; ?>
<?php $lotterySettings = $lotterySettings ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Розыгрыш</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Розыгрыш', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Создайте призовой товар и параметры розыгрыша для клиентов.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Назад к акциям
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo $message === 'saved' ? 'Розыгрыш создан.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-violet-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-violet-500">Розыгрыш</p>
            <h2 class="text-xl font-semibold text-slate-900">Создать приз</h2>
            <p class="text-sm text-slate-600">Укажите цену билета и количество участников, чтобы запустить лотерею.</p>
        </div>
        <form action="/?page=admin-lottery-save" method="post" class="grid gap-3">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название
                <input type="text" name="title" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание приза
                <textarea name="prize_description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200"></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Стоимость билета, ₽
                    <input type="number" name="ticket_price" min="0" step="0.01" value="0" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                    <span class="text-xs font-normal text-slate-400">0 — участие бесплатно.</span>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество билетов
                    <input type="number" name="tickets_total" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Дата розыгрыша
                    <input type="datetime-local" name="draw_at" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Статус
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                        <option value="active">Активен</option>
                        <option value="finished">Завершён</option>
                    </select>
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input type="text" name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
            </label>
            <div class="rounded-xl border border-violet-100 bg-violet-50 px-4 py-3 text-xs text-violet-700">
                Бесплатные розыгрыши: один билет на пользователя и не более <?php echo (int) ($lotterySettings['freeMonthlyLimit'] ?? 0); ?> участий в месяц.
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">celebration</span>
                Создать розыгрыш
            </button>
        </form>
    </div>
</section>

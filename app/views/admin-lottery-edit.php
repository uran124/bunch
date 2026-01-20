<?php /** @var array $lottery */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $lotterySettings = $lotterySettings ?? []; ?>
<?php
$drawAtValue = '';
if (!empty($lottery['draw_at'])) {
    try {
        $drawAtValue = (new DateTime($lottery['draw_at']))->format('Y-m-d\\TH:i');
    } catch (Exception $e) {
        $drawAtValue = $lottery['draw_at'];
    }
}
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Редактирование</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Редактирование розыгрыша', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Обновите параметры розыгрыша и призового товара.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Назад к акциям
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="rounded-2xl border <?php echo $message === 'saved' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-rose-100 bg-rose-50 text-rose-700'; ?> px-4 py-3 text-sm">
            <?php echo $message === 'saved' ? 'Изменения сохранены.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-violet-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-violet-500">Розыгрыш</p>
            <h2 class="text-xl font-semibold text-slate-900">Редактировать приз</h2>
            <p class="text-sm text-slate-600">Укажите цену билета и количество участников, чтобы управлять розыгрышем.</p>
        </div>
        <form action="/admin-lottery-update" method="post" class="grid gap-3">
            <input type="hidden" name="id" value="<?php echo (int) ($lottery['id'] ?? 0); ?>">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название
                <input type="text" name="title" value="<?php echo htmlspecialchars($lottery['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание приза
                <textarea name="prize_description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200"><?php echo htmlspecialchars($lottery['prize_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Стоимость билета, ₽
                    <input type="number" name="ticket_price" min="0" step="1" value="<?php echo htmlspecialchars((string) ($lottery['ticket_price'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                    <span class="text-xs font-normal text-slate-400">0 — участие бесплатно.</span>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество билетов
                    <input type="number" name="tickets_total" min="1" step="1" value="<?php echo htmlspecialchars((string) ($lottery['tickets_total'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Дата розыгрыша
                    <input type="datetime-local" name="draw_at" value="<?php echo htmlspecialchars($drawAtValue, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Статус
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
                        <option value="active" <?php echo ($lottery['status_raw'] ?? '') === 'active' ? 'selected' : ''; ?>>Активен</option>
                        <option value="finished" <?php echo ($lottery['status_raw'] ?? '') === 'finished' ? 'selected' : ''; ?>>Завершён</option>
                    </select>
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input type="text" name="photo_url" value="<?php echo htmlspecialchars($lottery['photo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-200">
            </label>
            <div class="rounded-xl border border-violet-100 bg-violet-50 px-4 py-3 text-xs text-violet-700">
                Бесплатные розыгрыши: один билет на пользователя и не более <?php echo (int) ($lotterySettings['freeMonthlyLimit'] ?? 0); ?> участий в месяц.
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить изменения
            </button>
        </form>
    </div>
</section>

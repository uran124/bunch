<?php /** @var array $lot */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php
$formatDatetime = static function (?string $value): string {
    if (!$value) {
        return '';
    }

    return str_replace(' ', 'T', substr($value, 0, 16));
};
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Редактирование</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Редактирование лота', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Обновите данные лота, чтобы изменить параметры акции.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К списку лотов
            </a>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo $message === 'saved' ? 'Изменения сохранены.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Лот #<?php echo (int) $lot['id']; ?></p>
            <h2 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($lot['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="text-sm text-slate-600">Текущий статус: <?php echo htmlspecialchars($lot['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <form action="/admin-auction-update" method="post" class="grid gap-3">
            <input type="hidden" name="id" value="<?php echo (int) $lot['id']; ?>">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название лота
                <input name="title" required value="<?php echo htmlspecialchars($lot['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание
                <textarea name="description" rows="2" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"><?php echo htmlspecialchars($lot['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Фото (URL)
                <input name="image" value="<?php echo htmlspecialchars($lot['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена в магазине, ₽
                    <input name="store_price" type="number" step="1" min="0" value="<?php echo htmlspecialchars((string) ($lot['store_price'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Стартовая цена, ₽
                    <input name="start_price" type="number" step="1" min="1" value="<?php echo htmlspecialchars((string) ($lot['start_price'] ?? 1), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Шаг ставки, ₽
                    <input name="bid_step" type="number" step="1" min="1" value="<?php echo htmlspecialchars((string) ($lot['bid_step'] ?? 1), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Блиц-цена, ₽
                    <input name="blitz_price" type="number" step="1" min="0" value="<?php echo htmlspecialchars((string) ($lot['blitz_price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Старт торгов
                    <input name="starts_at" type="datetime-local" value="<?php echo htmlspecialchars($formatDatetime($lot['starts_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Окончание
                    <input name="ends_at" type="datetime-local" value="<?php echo htmlspecialchars($formatDatetime($lot['ends_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Статус
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
                    <?php
                    $statusOptions = [
                        'draft' => 'Черновик',
                        'active' => 'Активен',
                        'finished' => 'Завершён',
                        'cancelled' => 'Отменён',
                    ];
                    $currentStatus = $lot['status'] ?? 'draft';
                    ?>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $currentStatus === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить изменения
            </button>
        </form>
    </div>
</section>

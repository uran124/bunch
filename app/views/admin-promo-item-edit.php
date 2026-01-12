<?php /** @var array $promoItem */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php
$endsAtValue = '';
if (!empty($promoItem['ends_at'])) {
    try {
        $endsAtValue = (new DateTime($promoItem['ends_at']))->format('Y-m-d\\TH:i');
    } catch (Exception $e) {
        $endsAtValue = $promoItem['ends_at'];
    }
}
?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Редактирование</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Редактирование акции', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Обновите параметры лимитированного товара.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/?page=admin-promos" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-amber-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-500">Ограниченный товар</p>
            <h2 class="text-xl font-semibold text-slate-900">Редактировать предложение</h2>
            <p class="text-sm text-slate-600">Настройте цену, лимит и дату окончания, чтобы контролировать доступность.</p>
        </div>
        <form action="/?page=admin-promo-item-update" method="post" class="grid gap-3">
            <input type="hidden" name="id" value="<?php echo (int) ($promoItem['id'] ?? 0); ?>">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название
                <input type="text" name="title" value="<?php echo htmlspecialchars($promoItem['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание
                <textarea name="description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200"><?php echo htmlspecialchars($promoItem['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Базовая цена, ₽
                    <input type="number" name="base_price" min="1" step="0.01" value="<?php echo htmlspecialchars((string) ($promoItem['base_price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена по акции, ₽
                    <input type="number" name="price" min="1" step="0.01" value="<?php echo htmlspecialchars((string) ($promoItem['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество
                    <input type="number" name="quantity" min="1" step="1" value="<?php echo htmlspecialchars((string) ($promoItem['quantity'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Например, 15">
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Окончание акции
                <input type="datetime-local" name="ends_at" value="<?php echo htmlspecialchars($endsAtValue, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200">
                <span class="text-xs font-normal text-slate-400">Если дату не указать, акция закончится, когда количество закончится.</span>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Лейбл
                    <input type="text" name="label" value="<?php echo htmlspecialchars($promoItem['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Разовая акция">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Фото (URL)
                    <input type="text" name="photo_url" value="<?php echo htmlspecialchars($promoItem['photo_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200">
                </label>
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300" <?php echo !empty($promoItem['is_active']) ? 'checked' : ''; ?>>
                Показывать в каталоге
            </label>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить изменения
            </button>
        </form>
    </div>
</section>

<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Акции · Лимитированный товар</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Лимитированный товар', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-3xl text-base text-slate-500">Добавьте товар, который будет доступен ограниченное время или в фиксированном количестве.</p>
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
            <?php echo $message === 'saved' ? 'Товар добавлен.' : 'Проверьте корректность данных и попробуйте снова.'; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-amber-50/60 ring-1 ring-transparent lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-500">Ограниченный товар</p>
            <h2 class="text-xl font-semibold text-slate-900">Создать предложение</h2>
            <p class="text-sm text-slate-600">Настройте цену, лимит и дату окончания, чтобы контролировать доступность.</p>
        </div>
        <form action="/?page=admin-promo-item-save" method="post" class="grid gap-3">
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Название
                <input type="text" name="title" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
            </label>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Описание
                <textarea name="description" rows="3" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Базовая цена, ₽
                    <input type="number" name="base_price" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Цена по акции, ₽
                    <input type="number" name="price" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" required>
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Количество
                    <input type="number" name="quantity" min="1" step="1" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Например, 15">
                </label>
            </div>
            <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                Окончание акции
                <input type="datetime-local" name="ends_at" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200">
                <span class="text-xs font-normal text-slate-400">Если дату не указать, акция закончится, когда количество закончится.</span>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Лейбл
                    <input type="text" name="label" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Разовая акция">
                </label>
                <label class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    Фото (URL)
                    <input type="text" name="photo_url" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-200">
                </label>
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300" checked>
                Сразу показывать в каталоге
            </label>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:-translate-y-0.5">
                <span class="material-symbols-rounded text-base">add</span>
                Добавить ограниченный товар
            </button>
        </form>
    </div>
</section>

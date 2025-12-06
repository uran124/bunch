<?php /** @var array $users */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Рассылки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Создать группу', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500">Соберите активных пользователей в группу и отправляйте рассылки через телеграм-бота.</p>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">bolt</span>
                Уведомления уходят моментально
            </div>
        </div>
        <a
            href="/?page=admin-users"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <span class="material-symbols-rounded text-base">arrow_back</span>
            К пользователям
        </a>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Название группы</span>
                <input
                    type="text"
                    value="VIP клиенты / TG"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
                <span class="text-xs text-slate-500">Можно менять в любой момент.</span>
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Дата последнего заказа с</span>
                <input
                    id="group-date-from"
                    type="date"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Дата последнего заказа до</span>
                <input
                    id="group-date-to"
                    type="date"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Телефон</span>
                <input
                    id="group-phone-filter"
                    type="search"
                    placeholder="Например, 900 или 55"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
                <span class="text-xs text-slate-500">Фильтр сокращает список мгновенно.</span>
            </label>
        </div>

        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-rose-500">smart_toy</span>
                Рассылки отправляются через телеграм-бота. Добавляйте только активных пользователей.
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить группу
            </button>
        </div>

        <div id="group-user-list" class="divide-y divide-slate-100">
            <?php foreach ($users as $user): ?>
                <article
                    class="grid gap-3 py-4 sm:grid-cols-[1.4fr_1fr_auto] sm:items-center"
                    data-phone="<?php echo htmlspecialchars(preg_replace('/\D+/', '', $user['phone']), ENT_QUOTES, 'UTF-8'); ?>"
                    data-last-order="<?php echo htmlspecialchars($user['lastOrder'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <div class="space-y-1">
                        <div class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-sm text-slate-500">Телефон: <?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-xs text-slate-400">Последний заказ: <?php echo htmlspecialchars($user['lastOrderText'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                            <span class="material-symbols-rounded text-base text-emerald-500">event_available</span>
                            Доставок: <?php echo (int) $user['deliveries']; ?>
                        </span>
                        <?php if ($user['active']): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                <span class="material-symbols-rounded text-base">verified</span>
                                Активен
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                                <span class="material-symbols-rounded text-base">schedule</span>
                                Не активен
                            </span>
                        <?php endif; ?>
                    </div>
                    <label class="relative inline-flex h-10 w-24 cursor-pointer items-center gap-2 rounded-lg bg-slate-50 px-2 ring-1 ring-slate-200">
                        <input type="checkbox" class="peer sr-only" <?php echo $user['active'] ? 'checked' : ''; ?>>
                        <span class="text-xs font-semibold text-slate-600">Добавить</span>
                        <span class="ml-auto inline-flex h-8 w-14 items-center rounded-full bg-slate-200">
                            <span class="ml-1 h-7 w-7 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                        </span>
                    </label>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const groupPhoneInput = document.getElementById('group-phone-filter');
    const groupDateFromInput = document.getElementById('group-date-from');
    const groupDateToInput = document.getElementById('group-date-to');
    const groupUserList = document.getElementById('group-user-list');

    function filterGroupUsers() {
        const phoneDigits = (groupPhoneInput.value || '').replace(/\D+/g, '');
        const dateFrom = groupDateFromInput.value ? new Date(groupDateFromInput.value) : null;
        const dateTo = groupDateToInput.value ? new Date(groupDateToInput.value) : null;

        groupUserList.querySelectorAll('article').forEach((card) => {
            const userPhone = card.dataset.phone || '';
            const lastOrderRaw = card.dataset.lastOrder || '';
            const lastOrder = lastOrderRaw ? new Date(lastOrderRaw) : null;
            const hasValidDate = lastOrder && !Number.isNaN(lastOrder.getTime());

            const phoneMatches = phoneDigits === '' || userPhone.includes(phoneDigits);
            const afterFrom = !dateFrom || !hasValidDate || lastOrder >= dateFrom;
            const beforeTo = !dateTo || !hasValidDate || lastOrder <= dateTo;

            card.style.display = phoneMatches && afterFrom && beforeTo ? '' : 'none';
        });
    }

    [groupPhoneInput, groupDateFromInput, groupDateToInput].forEach((input) => {
        input.addEventListener('input', filterGroupUsers);
        input.addEventListener('change', filterGroupUsers);
    });
</script>

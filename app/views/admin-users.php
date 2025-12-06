<?php /** @var array $users */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Пользователи</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Пользователи', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-base text-slate-500">Поиск по телефону в режиме реального времени и фильтр по дате последнего заказа.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin-group-create"
                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl"
            >
                <span class="material-symbols-rounded text-base">group_add</span>
                Создать группу
            </a>
            <a
                href="/?page=admin"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                В панель
            </a>
        </div>
    </header>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Телефон</span>
                <input
                    id="user-phone-filter"
                    type="search"
                    placeholder="Например, 900 или 55"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
                <span class="text-xs text-slate-500">Список автоматически сокращается по совпадению цифр.</span>
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Дата последнего заказа с</span>
                <input
                    id="user-date-from"
                    type="date"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-slate-700">Дата последнего заказа до</span>
                <input
                    id="user-date-to"
                    type="date"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                >
            </label>
            <div class="flex items-end">
                <div class="w-full rounded-xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <span class="material-symbols-rounded mr-2 align-middle text-base">auto_awesome</span>
                    Фильтры работают без перезагрузки страницы.
                </div>
            </div>
        </div>

        <div id="user-list" class="divide-y divide-slate-100">
            <?php foreach ($users as $user): ?>
                <article
                    class="grid gap-3 py-4 sm:grid-cols-[1.4fr_1fr_auto] sm:items-center"
                    data-phone="<?php echo htmlspecialchars(preg_replace('/\D+/', '', $user['phone']), ENT_QUOTES, 'UTF-8'); ?>"
                    data-last-order="<?php echo htmlspecialchars($user['lastOrder'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <div class="space-y-1">
                        <a
                            href="/?page=admin-user&id=<?php echo (int) $user['id']; ?>"
                            class="text-base font-semibold text-rose-700 hover:text-rose-800"
                        >
                            <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="text-sm text-slate-500">Телефон: <?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-xs text-slate-400">Последний заказ: <?php echo htmlspecialchars($user['lastOrder'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                            <span class="material-symbols-rounded text-base text-emerald-500">event_available</span>
                            Доставок: 12
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-700 ring-1 ring-rose-200">
                            <span class="material-symbols-rounded text-base">notifications_active</span>
                            Рассылки: TG бот
                        </span>
                    </div>
                    <label class="relative inline-flex h-10 w-16 cursor-pointer items-center">
                        <input type="checkbox" class="peer sr-only" <?php echo $user['active'] ? 'checked' : ''; ?>>
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500"></span>
                        <span class="absolute left-1 top-1 h-8 w-8 rounded-full bg-white shadow-sm transition peer-checked:translate-x-6 peer-checked:shadow-md"></span>
                        <span class="sr-only">Активность</span>
                    </label>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const phoneInput = document.getElementById('user-phone-filter');
    const dateFromInput = document.getElementById('user-date-from');
    const dateToInput = document.getElementById('user-date-to');
    const userList = document.getElementById('user-list');

    function filterUsers() {
        const phoneDigits = (phoneInput.value || '').replace(/\D+/g, '');
        const dateFrom = dateFromInput.value ? new Date(dateFromInput.value) : null;
        const dateTo = dateToInput.value ? new Date(dateToInput.value) : null;

        userList.querySelectorAll('article').forEach((card) => {
            const userPhone = card.dataset.phone || '';
            const lastOrder = new Date(card.dataset.lastOrder);

            const phoneMatches = phoneDigits === '' || userPhone.includes(phoneDigits);
            const afterFrom = !dateFrom || lastOrder >= dateFrom;
            const beforeTo = !dateTo || lastOrder <= dateTo;

            card.style.display = phoneMatches && afterFrom && beforeTo ? '' : 'none';
        });
    }

    [phoneInput, dateFromInput, dateToInput].forEach((input) => {
        input.addEventListener('input', filterUsers);
        input.addEventListener('change', filterUsers);
    });
</script>

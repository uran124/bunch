<?php /** @var array $groups */ ?>
<?php /** @var array $messages */ ?>
<?php /** @var int $totalPages */ ?>
<?php /** @var int $currentPage */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $message = $message ?? null; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Рассылки</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Рассылки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-sm text-slate-500">Создайте текст и выберите группы получателей.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin-group-create"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">group_add</span>
                Группы
            </a>
            <a
                href="/?page=admin-users"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К пользователям
            </a>
        </div>
    </header>

    <?php if ($message === 'created'): ?>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">Рассылка сохранена.</div>
    <?php elseif ($message === 'body-required'): ?>
        <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">Добавьте текст сообщения.</div>
    <?php endif; ?>

    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
        <form method="post" action="/?page=admin-broadcast" class="grid gap-3 lg:grid-cols-[1fr_1fr]">
            <div class="space-y-3">
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Сообщение</span>
                    <textarea
                        name="body"
                        rows="5"
                        placeholder="Например: Дарим промокод на новые букеты"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    ></textarea>
                </label>
            </div>
            <div class="space-y-3">
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Группы для отправки</span>
                    <div class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <?php foreach ($groups as $group): ?>
                            <label class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200">
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-slate-500">Участников: <?php echo (int) $group['members']; ?></p>
                                </div>
                                <input
                                    type="checkbox"
                                    name="groups[]"
                                    value="<?php echo (int) $group['id']; ?>"
                                    class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                                    <?php echo $group['is_system'] ? 'checked' : ''; ?>
                                >
                            </label>
                        <?php endforeach; ?>
                    </div>
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-semibold text-slate-700">Дата отправки</span>
                        <input
                            type="date"
                            name="send_date"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        >
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-semibold text-slate-700">Время отправки</span>
                        <input
                            type="time"
                            name="send_time"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        >
                    </label>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="material-symbols-rounded text-base">schedule_send</span>
                    Создать
                </button>
            </div>
        </form>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">История</p>
                <h2 class="text-lg font-semibold text-slate-900">Запланированные и отправленные сообщения</h2>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                <span class="material-symbols-rounded text-base text-emerald-500">calendar_month</span>
                По 20 сообщений на страницу
            </div>
        </header>

        <div class="divide-y divide-slate-100">
            <?php foreach ($messages as $messageRow): ?>
                <article class="grid gap-3 py-4 md:grid-cols-[1.2fr_1fr_auto] md:items-center">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-slate-900">Сообщение #<?php echo (int) $messageRow['id']; ?></p>
                        <p class="text-xs text-slate-500">Создано: <?php echo htmlspecialchars($messageRow['createdAt'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-sm text-slate-700 line-clamp-2"><?php echo nl2br(htmlspecialchars($messageRow['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($messageRow['groups'] as $groupName): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                    <span class="material-symbols-rounded text-base text-rose-500">group</span>
                                    <?php echo htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            <span class="material-symbols-rounded text-base text-indigo-500">schedule</span>
                            Отправка: <?php echo htmlspecialchars($messageRow['sendAt'] ?: 'Сразу', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            <span class="material-symbols-rounded text-base text-emerald-500">diversity_3</span>
                            Получателей: <?php echo (int) $messageRow['recipients']; ?>
                        </span>
                    </div>
                    <div class="flex justify-end">
                        <?php if ($messageRow['status'] === 'scheduled'): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                <span class="material-symbols-rounded text-base">upcoming</span>
                                Запланировано
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                <span class="material-symbols-rounded text-base">check_circle</span>
                                Отправлено
                            </span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                <span>Страница <?php echo (int) $currentPage; ?> из <?php echo (int) $totalPages; ?></span>
                <div class="flex items-center gap-2">
                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <a
                            href="/?page=admin-broadcast&p=<?php echo $page; ?>"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border <?php echo $page === $currentPage ? 'border-rose-300 bg-rose-50 text-rose-700' : 'border-slate-200 bg-white text-slate-700'; ?> text-sm font-semibold shadow-sm"
                        >
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</section>

<section class="grid gap-4 sm:gap-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.08em] text-emerald-600">
                <span class="material-symbols-rounded text-base">event</span>
                Календарь
            </p>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Ваш календарь значимых дат</h1>
            <p class="text-xs text-slate-600 sm:text-sm">Настраивайте напоминания о важных датах и событиях.</p>
        </div>
        <a
            href="/account"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700 sm:text-sm"
        >
            <span class="material-symbols-rounded text-base">arrow_back</span>
            В личный кабинет
        </a>
    </header>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6" id="birthday-reminders">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Напоминания</p>
                <h2 class="text-base font-semibold text-slate-900 sm:text-lg">Напоминания о значимых днях</h2>
                <p class="text-xs text-slate-600 sm:text-sm">Планируйте поздравления заранее и не пропускайте поводы.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100 sm:text-sm"
                    data-birthday-reminder-add
                >
                    <span class="material-symbols-rounded text-base">calendar_add_on</span>
                    Добавить дату
                </button>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700" data-birthday-reminder-count>
                    <span class="material-symbols-rounded text-base">celebration</span>
                    <span data-birthday-reminder-count-value><?php echo count($birthdayReminders); ?></span>
                </span>
            </div>
        </div>

        <div class="mt-4 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3 sm:p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Напоминать заранее</p>
                        <p class="text-[11px] text-slate-500 sm:text-xs">Выберите, за сколько дней предупредить.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <?php foreach ($birthdayReminderDays as $day): ?>
                            <label class="relative">
                                <input
                                    type="radio"
                                    name="birthday-reminder-days"
                                    value="<?php echo (int) $day; ?>"
                                    class="peer sr-only"
                                    <?php echo $day === $birthdayReminderLeadDays ? 'checked' : ''; ?>
                                >
                                <span class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition peer-checked:border-emerald-200 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                    <?php echo (int) $day; ?> дн.
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Напоминания о значимых днях</p>
                        <p class="text-[11px] text-slate-500 sm:text-xs">Открывайте карточку, чтобы изменить данные получателя.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700" data-birthday-reminder-count>
                        <span class="material-symbols-rounded text-sm">celebration</span>
                        <span data-birthday-reminder-count-value><?php echo count($birthdayReminders); ?></span>
                    </span>
                </div>
                <?php $hasReminders = !empty($birthdayReminders); ?>
                <div class="mt-3 grid gap-2" data-birthday-reminder-list>
                    <div class="grid grid-cols-3 gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                        <span>Получатель</span>
                        <span>Повод</span>
                        <span>Дата</span>
                    </div>
                    <?php if ($hasReminders): ?>
                        <?php foreach ($birthdayReminders as $reminder): ?>
                    <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 sm:text-sm" data-birthday-reminder-row="<?php echo (int) $reminder['id']; ?>">
                        <?php
                                $dataAttrs = sprintf(
                                    'data-birthday-reminder-edit data-birthday-reminder-id="%s" data-birthday-reminder-recipient="%s" data-birthday-reminder-occasion="%s" data-birthday-reminder-date="%s"',
                                    htmlspecialchars((string) $reminder['id'], ENT_QUOTES, 'UTF-8'),
                                    htmlspecialchars($reminder['recipient'], ENT_QUOTES, 'UTF-8'),
                                    htmlspecialchars($reminder['occasion'], ENT_QUOTES, 'UTF-8'),
                                    htmlspecialchars($reminder['date_raw'], ENT_QUOTES, 'UTF-8')
                                );
                                ?>
                                <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                    <?php echo htmlspecialchars($reminder['recipient'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                    <?php echo htmlspecialchars($reminder['occasion'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <a href="#" class="underline decoration-emerald-100 decoration-2 underline-offset-4 hover:text-emerald-700" <?php echo $dataAttrs; ?>>
                                    <?php echo htmlspecialchars($reminder['date'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <p class="text-sm text-slate-500 <?php echo $hasReminders ? 'hidden' : ''; ?>" data-birthday-reminder-empty>
                        Пока нет добавленных получателей.
                    </p>
                </div>
                <p class="mt-2 hidden text-xs font-semibold text-emerald-700" data-birthday-reminder-status></p>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur" data-birthday-reminder-modal>
        <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Напоминания</p>
                    <h3 class="text-xl font-semibold text-slate-900" data-birthday-reminder-title>Редактировать напоминание</h3>
                    <p class="text-sm text-slate-600">Обновите данные получателя или повод.</p>
                </div>
                <button type="button" class="rounded-full p-1 text-slate-500 hover:bg-slate-100" data-birthday-reminder-close>
                    <span class="material-symbols-rounded text-xl">close</span>
                </button>
            </div>
            <form class="mt-5 space-y-4" data-birthday-reminder-form>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-recipient">Получатель</label>
                    <input id="birthday-recipient" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="recipient">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-occasion">Повод</label>
                    <input id="birthday-occasion" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="occasion">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.06em] text-slate-500" for="birthday-date">Дата</label>
                    <input id="birthday-date" type="date" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-300 focus:bg-white focus:outline-none" data-birthday-reminder-field="date">
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100" data-birthday-reminder-delete>
                        <span class="material-symbols-rounded text-base">delete</span>
                        Удалить
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-500" data-birthday-reminder-save>
                        <span class="material-symbols-rounded text-base">save</span>
                        <span data-birthday-reminder-submit>Сохранить</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

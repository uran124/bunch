<section class="grid gap-4 sm:gap-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.08em] text-rose-600">
                <span class="material-symbols-rounded text-base">notifications</span>
                Уведомления
            </p>
            <h1 class="text-lg font-semibold text-slate-900 sm:text-2xl">История сообщений от бота</h1>
        </div>
        <a
            class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            href="/account"
        >
            <span class="material-symbols-rounded text-base">account_circle</span>
            Профиль
        </a>
    </header>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <?php if (empty($notifications)): ?>
            <p class="text-sm text-slate-500">Пока нет уведомлений от Telegram-бота.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_reverse($notifications) as $notification): ?>
                    <?php
                    $createdAt = $notification['created_at'] ?? null;
                    $formattedTime = '—';
                    if ($createdAt) {
                        try {
                            $formattedTime = (new DateTime($createdAt))->format('d.m.Y H:i');
                        } catch (Throwable $e) {
                            $formattedTime = $createdAt;
                        }
                    }
                    ?>
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-sm text-slate-700"><?php echo htmlspecialchars($notification['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mt-2 text-xs font-semibold text-slate-400"><?php echo htmlspecialchars($formattedTime, ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

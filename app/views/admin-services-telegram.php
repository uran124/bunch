<?php /** @var array $pageMeta */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $settings = $settings ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Настройка сервисов</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Телеграм бот', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-sm text-slate-500">
                <?php echo htmlspecialchars($pageMeta['description'] ?? 'Настраиваем подключение к Telegram и параметры webhook.', ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/?page=admin"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К разделам
            </a>
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">shield_lock</span>
                Данные хранятся в базе
            </span>
        </div>
    </header>

    <?php if (($status ?? '') === 'saved') : ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            Настройки телеграм-бота сохранены.
        </div>
    <?php endif; ?>

    <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <form method="post" action="/?page=admin-services-telegram" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Telegram bot</p>
                <h2 class="text-xl font-semibold text-slate-900">Доступы и webhook</h2>
                <p class="text-sm text-slate-500">Заполните токен, username и секрет для проверки запросов.</p>
            </div>

            <div class="grid gap-3 text-sm">
                <label class="space-y-1">
                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Bot token</span>
                    <input
                        type="text"
                        name="bot_token"
                        value="<?php echo htmlspecialchars($settings['botToken'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="123456:AA..."
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                    >
                </label>
                <label class="space-y-1">
                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Bot username</span>
                    <input
                        type="text"
                        name="bot_username"
                        value="<?php echo htmlspecialchars($settings['botUsername'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="@bunchflowersBot"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                    >
                </label>
                <label class="space-y-1">
                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Webhook secret</span>
                    <input
                        type="text"
                        name="webhook_secret"
                        value="<?php echo htmlspecialchars($settings['webhookSecret'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="secret"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                    >
                </label>
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow"
            >
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить настройки
            </button>
        </form>

        <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Подсказка</p>
                <h3 class="text-lg font-semibold text-slate-900">Где взять данные</h3>
                <p class="text-sm text-slate-500">Токен выдаёт BotFather, username отображается в профиле бота, а secret используется в webhook URL.</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-800">Webhook URL</p>
                <p class="mt-2 break-all text-xs text-slate-500">
                    https://bunchflowers.ru/bot/webhook.php?secret=<?php echo htmlspecialchars($settings['webhookSecret'] ?? 'secret', ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>
        </aside>
    </section>
</section>

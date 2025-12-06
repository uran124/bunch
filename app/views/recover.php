<section class="relative overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-sky-50 via-white to-rose-50"></div>
    <div class="absolute -left-24 -top-16 h-56 w-56 rounded-full bg-sky-100 opacity-50 blur-3xl"></div>
    <div class="absolute -right-16 bottom-0 h-64 w-64 rounded-full bg-rose-100 opacity-50 blur-3xl"></div>

    <div class="flex flex-col gap-8 px-5 py-8 sm:px-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                    <span class="material-symbols-rounded text-base">lock_reset</span>
                    <span>Восстановление</span>
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Сброс PIN-кода</h1>
                    <p class="text-sm text-slate-600">Получите одноразовый код от бота, подтвердите его и задайте новый PIN.</p>
                </div>
            </div>
            <?php if (!empty($botUsername)): ?>
                <a
                    href="https://t.me/<?php echo htmlspecialchars($botUsername, ENT_QUOTES, 'UTF-8'); ?>"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:shadow-xl"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <span class="material-symbols-rounded text-base">smart_toy</span>
                    <span>Открыть бота</span>
                </a>
            <?php endif; ?>
        </header>

        <?php if (!empty($successMessage)): ?>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-base">check_circle</span>
                    <p><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-base">error</span>
                    <div class="space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($stage ?? 'code') === 'code'): ?>
            <form method="POST" action="/?page=recover" class="grid gap-5">
                <input type="hidden" name="step" value="verify_code">
                <div class="grid gap-1.5">
                    <label for="code" class="text-sm font-semibold text-slate-800">Код из Telegram (5 цифр)</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="5"
                        pattern="\d{5}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                        placeholder="12345"
                        required
                        inputmode="numeric"
                    >
                </div>
                <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-700 shadow-inner shadow-slate-100">
                    <div class="flex items-center gap-2 font-semibold text-slate-900">
                        <span class="material-symbols-rounded text-base text-rose-500">info</span>
                        Как получить код
                    </div>
                    <ol class="grid gap-2 pl-4 text-sm list-decimal marker:text-rose-500">
                        <li>Откройте чат с ботом и нажмите «Восстановить PIN».</li>
                        <li>Бот проверит ваш чат и пришлёт одноразовый код.</li>
                        <li>Введите код здесь, чтобы сменить PIN.</li>
                    </ol>
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-rose-500 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-sky-200 transition hover:shadow-xl hover:shadow-rose-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
                >
                    <span class="material-symbols-rounded text-base">key</span>
                    Подтвердить код
                </button>
            </form>
        <?php else: ?>
            <form method="POST" action="/?page=recover" class="grid gap-5">
                <input type="hidden" name="step" value="update_pin">
                <div class="grid gap-1.5">
                    <label for="pin" class="text-sm font-semibold text-slate-800">Новый PIN (4 цифры)</label>
                    <input
                        type="password"
                        id="pin"
                        name="pin"
                        maxlength="4"
                        minlength="4"
                        pattern="\d{4}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                        placeholder="••••"
                        required
                        inputmode="numeric"
                    >
                </div>
                <div class="grid gap-1.5">
                    <label for="pin_confirm" class="text-sm font-semibold text-slate-800">Повторите PIN</label>
                    <input
                        type="password"
                        id="pin_confirm"
                        name="pin_confirm"
                        maxlength="4"
                        minlength="4"
                        pattern="\d{4}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                        placeholder="••••"
                        required
                        inputmode="numeric"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-rose-500 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-sky-200 transition hover:shadow-xl hover:shadow-rose-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
                >
                    <span class="material-symbols-rounded text-base">restart_alt</span>
                    Сохранить новый PIN
                </button>
                <p class="text-center text-sm text-slate-600">
                    Помните свой код? <a class="font-semibold text-rose-600 hover:text-rose-700" href="/?page=login">Войти</a>
                </p>
            </form>
        <?php endif; ?>
    </div>
</section>

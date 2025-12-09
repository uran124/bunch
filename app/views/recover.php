<section class="relative overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-sky-50 via-white to-rose-50"></div>
    <div class="absolute -left-24 -top-16 h-56 w-56 rounded-full bg-sky-100 opacity-50 blur-3xl"></div>
    <div class="absolute -right-16 bottom-0 h-64 w-64 rounded-full bg-rose-100 opacity-50 blur-3xl"></div>

    <div class="flex flex-col gap-8 px-5 py-8 sm:px-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                    <span class="material-symbols-rounded text-base">lock_reset</span>
                    <span>Сброс PIN-кода</span>
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Получите код и смените PIN</h1>
                </div>
            </div>
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
            <form method="POST" action="/?page=recover" class="grid gap-4 rounded-2xl border border-slate-200 bg-white/70 px-4 py-4 shadow-inner shadow-slate-100">
                <input type="hidden" name="step" value="request_code">
                <div class="grid gap-1.5">
                    <label for="phone" class="text-sm font-semibold text-slate-800">Номер телефона</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($prefillPhone ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                        placeholder="+7 (999) 123-45-67"
                        inputmode="tel"
                        autocomplete="tel"
                        required
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-rose-500 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-sky-200 transition hover:shadow-xl hover:shadow-rose-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
                >
                    Получить одноразовый код
                </button>
            </form>

            <form id="code-form" method="POST" action="/?page=recover" class="grid gap-4">
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
                        autocomplete="one-time-code"
                    >
                    <p class="text-xs text-slate-500">Подтверждение сработает автоматически после ввода последней цифры.</p>
                </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const codeInput = document.getElementById('code');
            const form = document.getElementById('code-form');
            if (!codeInput || !form) {
                return;
            }

            let submitted = false;

            const submitIfComplete = () => {
                const digits = (codeInput.value || '').replace(/\D+/g, '').slice(0, 5);
                codeInput.value = digits;

                if (digits.length === 5 && !submitted) {
                    submitted = true;
                    form.submit();
                }
            };

            codeInput.addEventListener('input', submitIfComplete);
            codeInput.addEventListener('paste', (event) => {
                event.preventDefault();
                const text = (event.clipboardData || window.clipboardData).getData('text');
                codeInput.value = text;
                submitIfComplete();
            });
        });
    </script>
</section>

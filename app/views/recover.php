<section class="relative w-full max-w-[420px] overflow-hidden rounded-3xl bg-gradient-to-br from-white via-rose-50 to-white shadow-2xl">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(225,29,72,0.08),transparent_50%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(244,63,94,0.08),transparent_50%)] pointer-events-none"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent opacity-50 pointer-events-none"></div>

    <div class="flex flex-col gap-6 px-4 py-6 sm:px-5 sm:py-7">
        <header class="flex flex-col gap-2 text-center">
            <div class="flex items-center justify-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-600 to-rose-700 flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <span class="material-symbols-rounded text-xl text-white">lock_reset</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Восстановление</h1>
            </div>
        </header>

        <?php if (!empty($successMessage)): ?>
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 backdrop-blur-sm px-4 py-3 text-sm text-emerald-700">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-lg text-emerald-600">check_circle</span>
                    <p><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="rounded-2xl border border-rose-300 bg-rose-50 backdrop-blur-sm px-4 py-3 text-sm text-rose-700">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-lg text-rose-600">error</span>
                    <div class="space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($stage ?? 'code') === 'code'): ?>
            <form method="POST" action="/recover" class="grid gap-5">
                <input type="hidden" name="step" value="request_code">
                <div class="flex justify-center">
                    <div class="w-full max-w-xs">
                        <div class="group relative rounded-2xl border border-rose-200 bg-white/90 backdrop-blur-sm transition focus-within:border-rose-600 focus-within:shadow-lg focus-within:shadow-rose-500/20">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-rose-600 text-lg">phone</span>
                            </div>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($prefillPhone ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full bg-transparent pl-10 pr-3 py-3.5 text-base font-semibold text-slate-900 outline-none placeholder:text-slate-400"
                                placeholder="+7"
                                required
                                inputmode="tel"
                                autocomplete="tel"
                                autofocus
                            >
                        </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <div class="w-full max-w-xs">
                        <button
                            type="submit"
                            class="w-full group relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 px-5 py-3.5 text-center text-sm font-semibold text-white shadow-xl shadow-rose-500/25 transition hover:shadow-2xl hover:shadow-rose-500/40 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-700 to-rose-800 opacity-0 group-hover:opacity-100 transition"></div>
                            <span class="relative flex items-center justify-center gap-2">
                                <span class="material-symbols-rounded text-lg">send</span>
                                Получить код
                            </span>
                        </button>
                    </div>
                </div>
            </form>

            <form id="code-form" method="POST" action="/recover" class="grid gap-5">
                <input type="hidden" name="step" value="verify_code">
                <div class="flex justify-center">
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="5"
                        pattern="\d{5}"
                        class="w-44 rounded-2xl border border-rose-200 bg-white/90 backdrop-blur-sm px-3 py-3 text-center text-2xl font-bold tracking-[0.35em] text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105"
                        placeholder="• • • • •"
                        required
                        inputmode="numeric"
                        autocomplete="one-time-code"
                    >
                </div>
            </form>

            <div class="flex justify-center">
                <div class="grid grid-cols-2 gap-2.5 w-full max-w-xs">
                    <a href="/login" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 hover:border-rose-300">
                        <span class="material-symbols-rounded text-sm">login</span>
                        Вход
                    </a>
                    <a href="/register" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 hover:border-rose-300">
                        <span class="material-symbols-rounded text-sm">how_to_reg</span>
                        Регистрация
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="/recover" class="grid gap-5">
                <input type="hidden" name="step" value="update_pin">
                <div class="flex flex-col items-center gap-2">
                    <label class="text-xs font-medium text-slate-700">Новый PIN</label>
                    <div class="grid grid-cols-4 gap-2.5 w-56">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Первая цифра PIN" name="pin_1">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Вторая цифра PIN" name="pin_2">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Третья цифра PIN" name="pin_3">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Четвертая цифра PIN" name="pin_4">
                    </div>
                    <input type="hidden" name="pin" id="pin" required minlength="4" maxlength="4">
                </div>
                
                <div class="flex flex-col items-center gap-2">
                    <label class="text-xs font-medium text-slate-700">Подтвердите PIN</label>
                    <div class="grid grid-cols-4 gap-2.5 w-56">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Первая цифра подтверждения" name="pin_confirm_1">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Вторая цифра подтверждения" name="pin_confirm_2">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Третья цифра подтверждения" name="pin_confirm_3">
                        <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Четвертая цифра подтверждения" name="pin_confirm_4">
                    </div>
                    <input type="hidden" name="pin_confirm" id="pin_confirm" required minlength="4" maxlength="4">
                </div>

                <div class="flex justify-center">
                    <div class="w-full max-w-xs">
                        <button
                            type="submit"
                            class="w-full group relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 px-5 py-3.5 text-center text-sm font-semibold text-white shadow-xl shadow-rose-500/25 transition hover:shadow-2xl hover:shadow-rose-500/40 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-700 to-rose-800 opacity-0 group-hover:opacity-100 transition"></div>
                            <span class="relative flex items-center justify-center gap-2">
                                <span class="material-symbols-rounded text-lg">check_circle</span>
                                Сохранить PIN
                            </span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="flex justify-center">
                <div class="grid grid-cols-2 gap-2.5 w-full max-w-xs">
                    <a href="/login" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 hover:border-rose-300">
                        <span class="material-symbols-rounded text-sm">login</span>
                        Вход
                    </a>
                    <a href="/register" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 hover:border-rose-300">
                        <span class="material-symbols-rounded text-sm">how_to_reg</span>
                        Регистрация
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const phoneInput = document.getElementById('phone');
        const codeInput = document.getElementById('code');
        const form = document.getElementById('code-form');

        // Phone formatting with locked +7
        if (phoneInput) {
            const formatPhone = (value) => {
                const digits = value.replace(/\D/g, '');
                const withoutPrefix = digits.startsWith('7') ? digits.slice(1) : digits;
                const limited = withoutPrefix.slice(0, 10);
                const parts = [];

                if (limited.length > 0) parts.push(limited.slice(0, 3));
                if (limited.length > 3) parts.push(limited.slice(3, 6));
                if (limited.length > 6) parts.push(limited.slice(6, 8));
                if (limited.length > 8) parts.push(limited.slice(8, 10));

                let formatted = '+7 ';
                if (parts.length > 0) formatted += parts[0];
                if (parts.length > 1) formatted += ' ' + parts[1];
                if (parts.length > 2) formatted += '-' + parts[2];
                if (parts.length > 3) formatted += '-' + parts[3];

                return { formatted, digits: limited };
            };

            const setPhoneValue = (value) => {
                const { formatted } = formatPhone(value);
                phoneInput.value = formatted;
            };

            phoneInput.addEventListener('focus', () => {
                if (!phoneInput.value.trim()) {
                    setPhoneValue('+7 ');
                }
                requestAnimationFrame(() => {
                    phoneInput.setSelectionRange(phoneInput.value.length, phoneInput.value.length);
                });
            });

            phoneInput.addEventListener('input', () => {
                setPhoneValue(phoneInput.value);
            });

            phoneInput.addEventListener('keydown', (event) => {
                if (phoneInput.selectionStart <= 3 && ['Backspace', 'Delete'].includes(event.key)) {
                    event.preventDefault();
                }
            });

            phoneInput.focus();
        }

        // Auto-submit 5-digit code
        if (codeInput && form) {
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
        }

        // PIN fields logic
        const pinInputs = Array.from(document.querySelectorAll('input[name^="pin_"]:not([name^="pin_confirm"])'));
        const pinConfirmInputs = Array.from(document.querySelectorAll('input[name^="pin_confirm_"]'));
        const hiddenPin = document.getElementById('pin');
        const hiddenPinConfirm = document.getElementById('pin_confirm');

        const setupPinFields = (inputs, hiddenField) => {
            inputs.forEach((input, index) => {
                input.value = '';
                
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 1);
                    if (input.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    if (hiddenField) {
                        hiddenField.value = inputs.map(inp => inp.value).join('');
                    }
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });
        };

        if (pinInputs.length > 0) setupPinFields(pinInputs, hiddenPin);
        if (pinConfirmInputs.length > 0) setupPinFields(pinConfirmInputs, hiddenPinConfirm);
    });
</script>

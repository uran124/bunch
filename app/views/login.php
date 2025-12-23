<section class="relative w-full max-w-[420px] overflow-hidden rounded-3xl bg-gradient-to-br from-white via-rose-50 to-white shadow-2xl">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(225,29,72,0.08),transparent_50%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(244,63,94,0.08),transparent_50%)] pointer-events-none"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent opacity-50 pointer-events-none"></div>

    <div class="flex flex-col gap-7 px-5 py-8 sm:px-7 sm:py-10">
        <header class="flex flex-col gap-3 text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-600 to-rose-700 flex items-center justify-center shadow-lg shadow-rose-500/30">
                <span class="material-symbols-rounded text-2xl text-white">lock</span>
            </div>
            <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Добро пожаловать</h1>
                <p class="text-sm text-slate-600">Войдите в свой аккаунт Bunch</p>
            </div>
        </header>

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

        <form method="POST" action="/?page=login" class="grid gap-5" id="login-form">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700 pl-1">Номер телефона</label>
                <div class="group relative rounded-2xl border border-rose-200 bg-white/90 backdrop-blur-sm transition focus-within:border-rose-600 focus-within:shadow-lg focus-within:shadow-rose-500/20">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-rose-600 text-lg">phone</span>
                    </div>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="w-full bg-transparent pl-11 pr-4 py-3.5 text-base font-semibold text-slate-900 outline-none placeholder:text-slate-400"
                        placeholder="+7 ___ ___-__-__"
                        required
                        inputmode="tel"
                        autocomplete="tel"
                        autofocus
                    >
                </div>
            </div>
            <div class="grid gap-3">
                <label class="text-sm font-medium text-slate-700 pl-1 flex items-center gap-2">
                    <span class="material-symbols-rounded text-base">key</span>
                    <span>PIN-код</span>
                </label>
                <div class="grid grid-cols-4 gap-2.5" id="pin-inputs">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Первая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Вторая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Третья цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" class="w-full aspect-square rounded-xl border border-rose-200 bg-white/90 backdrop-blur-sm text-center text-xl font-bold text-slate-900 outline-none transition focus:border-rose-600 focus:shadow-lg focus:shadow-rose-500/20 focus:scale-105" aria-label="Четвертая цифра PIN">
                </div>
                <input type="hidden" name="pin" id="pin" required minlength="4" maxlength="4">
            </div>
            <div class="grid gap-3 pt-1.5">
                <a href="/?page=register" class="group relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 px-6 py-3.5 text-center text-sm font-semibold text-white shadow-xl shadow-rose-500/25 transition hover:shadow-2xl hover:shadow-rose-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <div class="absolute inset-0 bg-gradient-to-r from-rose-700 to-rose-800 opacity-0 group-hover:opacity-100 transition"></div>
                    <span class="relative flex items-center justify-center gap-2">
                        <span class="material-symbols-rounded text-xl">how_to_reg</span>
                        Создать аккаунт
                    </span>
                </a>
                <a href="/?page=recover" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white/90 backdrop-blur-sm px-6 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 hover:border-rose-300 hover:text-rose-800">
                    <span class="material-symbols-rounded text-base">lock_open</span>
                    Забыли PIN?
                </a>
            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const phoneInput = document.getElementById('phone');
        const pinInputs = Array.from(document.querySelectorAll('#pin-inputs input'));
        const hiddenPin = document.getElementById('pin');
        const form = document.getElementById('login-form');

        const lockPinInputs = (shouldLock, shouldClear = false) => {
            pinInputs.forEach((input) => {
                if (shouldLock) {
                    input.setAttribute('readonly', 'readonly');
                    input.value = '';
                } else {
                    input.removeAttribute('readonly');
                    if (shouldClear) {
                        input.value = '';
                    }
                }
                input.setAttribute('autocomplete', 'one-time-code');
            });
            if (shouldLock || shouldClear) {
                hiddenPin.value = '';
            }
        };

        const focusPhoneInput = () => {
            if (!phoneInput) {
                return;
            }
            phoneInput.focus();
            requestAnimationFrame(() => {
                phoneInput.setSelectionRange(phoneInput.value.length, phoneInput.value.length);
            });
        };

        lockPinInputs(true);
        setTimeout(() => {
            focusPhoneInput();
        }, 0);

        let lastPhoneDigits = '';

        const formatPhone = (value) => {
            const digits = value.replace(/\D/g, '');
            const withoutPrefix = digits.startsWith('7') ? digits.slice(1) : digits;
            const limited = withoutPrefix.slice(0, 10);
            const parts = [];

            if (limited.length > 0) {
                parts.push(limited.slice(0, 3));
            }
            if (limited.length > 3) {
                parts.push(limited.slice(3, 6));
            }
            if (limited.length > 6) {
                parts.push(limited.slice(6, 8));
            }
            if (limited.length > 8) {
                parts.push(limited.slice(8, 10));
            }

            let formatted = '+7 ';
            if (parts.length > 0) {
                formatted += parts[0];
            }
            if (parts.length > 1) {
                formatted += ' ' + parts[1];
            }
            if (parts.length > 2) {
                formatted += '-' + parts[2];
            }
            if (parts.length > 3) {
                formatted += '-' + parts[3];
            }

            return { formatted, digits: limited };
        };

const setPhoneValue = (value) => {
            const { formatted, digits } = formatPhone(value);
            phoneInput.value = formatted;

            const shouldMoveToPin = digits.length === 10 && lastPhoneDigits.length < 10;
            const shouldLockPin = digits.length < 10 && lastPhoneDigits.length === 10;
            lastPhoneDigits = digits;

            if (shouldMoveToPin) {
                requestAnimationFrame(() => {
                    lockPinInputs(false, true);
                    pinInputs[0].focus();
                });
            } else if (shouldLockPin) {
                lockPinInputs(true);
            }
        };

        phoneInput.addEventListener('focus', () => {
            if (!phoneInput.value.trim()) {
                setPhoneValue('+7 ');
            }
            focusPhoneInput();
        });

        phoneInput.addEventListener('focus', () => {
            if (!phoneInput.value.trim()) {
                setPhoneValue('+7 ');
            }
            focusPhoneInput();
        });

        phoneInput.addEventListener('input', () => {
            setPhoneValue(phoneInput.value);
        });

        phoneInput.addEventListener('keydown', (event) => {
            if (phoneInput.selectionStart <= 3 && ['Backspace', 'Delete'].includes(event.key)) {
                event.preventDefault();
            }
        });

        const updateHiddenPin = () => {
            hiddenPin.value = pinInputs.map((input) => input.value).join('');
            if (hiddenPin.value.length === 4) {
                form.requestSubmit();
            }
        };

        pinInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 1);
                if (input.value && index < pinInputs.length - 1) {
                    pinInputs[index + 1].focus();
                }
                updateHiddenPin();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !input.value && index > 0) {
                    pinInputs[index - 1].focus();
                }
            });
        });
    });
</script>

<section class="relative overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-rose-50 via-white to-sky-50"></div>
    <div class="absolute -left-24 -top-24 h-56 w-56 rounded-full bg-rose-100 opacity-40 blur-3xl"></div>
    <div class="absolute -right-20 -bottom-24 h-64 w-64 rounded-full bg-sky-100 opacity-60 blur-3xl"></div>

    <div class="flex flex-col gap-8 px-5 py-8 sm:px-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                    <span class="material-symbols-rounded text-base">lock</span>
                    <span>Вход в панель</span>
                </div>
            </div>
        </header>

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

        <form method="POST" action="/?page=login" class="grid gap-5" id="login-form">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-inner shadow-slate-100">
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    class="w-full bg-transparent text-lg font-semibold text-slate-900 outline-none placeholder:text-slate-400"
                    placeholder="+7 ___ ___-__-__"
                    required
                    inputmode="tel"
                    autocomplete="tel"
                >
            </div>
            <div class="grid gap-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <span class="material-symbols-rounded text-base">key</span>
                    <span>PIN</span>
                </div>
                <div class="grid grid-cols-4 gap-3" id="pin-inputs">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Первая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Вторая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Третья цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Четвертая цифра PIN">
                </div>
                <input type="hidden" name="pin" id="pin" required minlength="4" maxlength="4">
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm font-semibold">
                <a href="/?page=recover" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                    <span class="material-symbols-rounded text-base">lock_open</span>
                    Забыли PIN?
                </a>
                <a href="/?page=register" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-800 transition hover:border-rose-300 hover:bg-rose-50">
                    <span class="material-symbols-rounded text-base">how_to_reg</span>
                    Регистрация
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

            if (digits.length === 10) {
                pinInputs[0].focus();
            }
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

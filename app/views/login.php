<section class="relative w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-rose-100">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-rose-50 via-white to-rose-100 pointer-events-none"></div>
    <div class="absolute -left-24 -top-24 h-56 w-56 rounded-full bg-rose-200 opacity-30 blur-3xl pointer-events-none"></div>
    <div class="absolute -right-20 -bottom-24 h-64 w-64 rounded-full bg-rose-100 opacity-50 blur-3xl pointer-events-none"></div>

    <div class="flex flex-col gap-8 px-5 py-8 sm:px-8">
        <header class="flex flex-col gap-2 text-center">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900"><span class="material-symbols-rounded text-base">lock</span> Добро пожаловать в Bunch</h1>
                
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

        <form method="POST" action="/?page=login" class="grid gap-6" id="login-form">
            <div class="grid gap-1.5">
                <div class="rounded-2xl border border-rose-200 bg-white/80 px-4 py-3 shadow-inner shadow-rose-100">
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="w-full bg-transparent text-lg font-semibold text-slate-900 outline-none placeholder:text-slate-400"
                        placeholder="+7"
                        required
                        inputmode="tel"
                        autocomplete="tel"
                        autofocus
                    >
                </div>
            </div>
            <div class="grid gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="material-symbols-rounded text-base">key</span>
                        <span>PIN</span>
                    </div>
                    
                </div>
                <div class="grid grid-cols-4 gap-3" id="pin-inputs">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-rose-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-rose-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Первая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-rose-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-rose-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Вторая цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-rose-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-rose-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Третья цифра PIN">
                    <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="w-full rounded-xl border border-rose-200 bg-white py-3 text-center text-xl font-semibold text-slate-900 shadow-inner shadow-rose-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100" aria-label="Четвертая цифра PIN">
                </div>
                <input type="hidden" name="pin" id="pin" required minlength="4" maxlength="4">
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm font-semibold">
                <a href="/?page=recover" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 px-4 py-3 text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="material-symbols-rounded text-base">lock_open</span>
                    Забыли PIN?
                </a>
                <a href="/?page=register" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-3 text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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

        if (phoneInput) {
            phoneInput.focus();
        }

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

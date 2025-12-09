<section class="relative overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-emerald-50 via-white to-rose-50"></div>
    <div class="absolute -left-24 top-10 h-56 w-56 rounded-full bg-emerald-100 opacity-40 blur-3xl"></div>
    <div class="absolute -right-16 bottom-0 h-64 w-64 rounded-full bg-rose-100 opacity-50 blur-3xl"></div>

    <div class="flex flex-col gap-8 px-5 py-8 sm:px-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                    <span class="material-symbols-rounded text-base">verified_user</span>
                    <span>Регистрация</span>
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
            <form method="POST" action="/?page=register" class="grid gap-5">
                <input type="hidden" name="step" value="verify_code">
                <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-700 shadow-inner shadow-slate-100">
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="https://t.me/<?php echo htmlspecialchars($botUsername ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100"
                        >
                            <span class="material-symbols-rounded text-base">send</span>
                            Получить код в Telegram
                        </a>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-700">
                        <div class="flex items-center gap-2 font-semibold text-slate-900">
                            <span class="material-symbols-rounded text-base text-rose-500">info</span>
                            Как получить код
                        </div>
                        <ol class="grid gap-2 pl-4 list-decimal marker:text-rose-500">
                            <li>Нажмите «Перейти к боту» и запустите бота кнопкой /start.</li>
                        </ol>
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <label for="code" class="text-sm font-semibold text-slate-800">Код из Telegram</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="5"
                        pattern="\d{5}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        placeholder="12345"
                        required
                        inputmode="numeric"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-200 transition hover:shadow-xl hover:shadow-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500"
                >
                    <span class="material-symbols-rounded text-base">key</span>
                    Подтвердить код
                </button>
            </form>
        <?php else: ?>
            <form method="POST" action="/?page=register" class="grid gap-5">
                <input type="hidden" name="step" value="complete_registration">
                <div class="grid gap-1.5">
                    <label for="name" class="text-sm font-semibold text-slate-800">Имя (из Telegram, можно изменить)</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        placeholder="Как к вам обращаться"
                        required
                        value="<?php echo htmlspecialchars($prefillName ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
                <div class="grid gap-1.5">
                    <label for="phone" class="text-sm font-semibold text-slate-800">Телефон</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        placeholder="+7 (999) 123-45-67"
                        required
                        inputmode="tel"
                        value="<?php echo htmlspecialchars($prefillPhone ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
                <div class="grid gap-1.5">
                    <label for="email" class="text-sm font-semibold text-slate-800">Электронная почта (необязательно)</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        placeholder="you@example.com"
                        value="<?php echo htmlspecialchars($prefillEmail ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
                <div class="grid gap-1.5">
                    <label class="text-sm font-semibold text-slate-800">PIN для входа (4 цифры)</label>
                    <div class="grid grid-cols-4 gap-3">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <input
                                type="password"
                                name="pin_<?php echo $i; ?>"
                                inputmode="numeric"
                                pattern="\d"
                                maxlength="1"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-lg font-semibold text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                aria-label="Цифра PIN <?php echo $i; ?>"
                                required
                            >
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs text-slate-500">Каждую цифру вводите в отдельное поле.</p>
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-200 transition hover:shadow-xl hover:shadow-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500"
                >
                    <span class="material-symbols-rounded text-base">check_circle</span>
                    Завершить регистрацию
                </button>
            </form>
            <script>
                const pinInputs = Array.from(document.querySelectorAll('input[name^="pin_"]'));

                pinInputs.forEach((input, index) => {
                    input.addEventListener('input', () => {
                        input.value = input.value.replace(/\D+/g, '').slice(0, 1);

                        if (input.value && index < pinInputs.length - 1) {
                            pinInputs[index + 1].focus();
                        }
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Backspace' && !input.value && index > 0) {
                            pinInputs[index - 1].focus();
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</section>

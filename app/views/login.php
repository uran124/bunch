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
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">С возвращением!</h1>
                    <p class="text-sm text-slate-600">Используйте номер телефона и PIN-код из Telegram, чтобы войти.</p>
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

        <form method="POST" action="/?page=login" class="grid gap-5">
            <div class="grid gap-1.5">
                <label for="phone" class="text-sm font-semibold text-slate-800">Телефон</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                    placeholder="+7 (999) 123-45-67"
                    required
                    inputmode="tel"
                >
            </div>
            <div class="grid gap-1.5">
                <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                    <label for="pin">PIN</label>
                    <a href="/?page=recover" class="text-rose-600 hover:text-rose-700">Забыли PIN?</a>
                </div>
                <input
                    type="password"
                    id="pin"
                    name="pin"
                    maxlength="4"
                    minlength="4"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-900 shadow-inner shadow-slate-100 outline-none transition focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                    placeholder="••••"
                    required
                    inputmode="numeric"
                >
            </div>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-rose-200 transition hover:shadow-xl hover:shadow-rose-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
            >
                <span class="material-symbols-rounded text-base">login</span>
                Войти
            </button>
            <p class="text-center text-sm text-slate-600">
                Нет аккаунта? <a class="font-semibold text-rose-600 hover:text-rose-700" href="/?page=register">Зарегистрироваться</a>
            </p>
        </form>
    </div>
</section>

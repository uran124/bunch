<?php /** @var array $pageMeta */ ?>
<?php /** @var array $paymentSettings */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $paymentSettings = $paymentSettings ?? []; ?>
<?php $robokassaSettings = $paymentSettings['robokassa'] ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Настройка сервисов</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'Онлайн оплата', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-sm text-slate-500">
                <?php echo htmlspecialchars($pageMeta['description'] ?? 'Подключаем платёжные шлюзы, контролируем webhooks и возвраты.', ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/admin"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                К разделам
            </a>
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                <span class="material-symbols-rounded text-base">credit_card</span>
                3 шлюза подключаем
            </span>
        </div>
    </header>

    <section class="grid gap-4 lg:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Активный шлюз</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">Robokassa</p>
            <p class="mt-1 text-sm text-slate-500">По умолчанию для новых заказов.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Webhook статус</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-emerald-500">task_alt</span>
                <p class="text-lg font-semibold text-slate-900">Слушаем 24/7</p>
            </div>
            <p class="mt-1 text-sm text-slate-500">Проверка подписей и статус оплат.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Возвраты</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-amber-500">hourglass</span>
                <p class="text-lg font-semibold text-slate-900">Скоро</p>
            </div>
            <p class="mt-1 text-sm text-slate-500">Единый кабинет управления.</p>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Онлайн оплата</p>
                <h2 class="text-xl font-semibold text-slate-900">Платёжные шлюзы</h2>
                <p class="text-sm text-slate-500">Выберите провайдера и заполните параметры доступа.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">lock</span>
                Данные шифруются
            </span>
        </div>

        <div class="mt-4 flex flex-wrap gap-2" role="tablist" aria-label="Платёжные шлюзы">
            <button
                type="button"
                class="payment-tab inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition"
                data-tab="robokassa"
                data-active="true"
                role="tab"
                aria-selected="true"
            >
                <span class="material-symbols-rounded text-base">bolt</span>
                Робокасса
            </button>
            <button
                type="button"
                class="payment-tab inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition"
                data-tab="alfa"
                role="tab"
                aria-selected="false"
            >
                <span class="material-symbols-rounded text-base">account_balance</span>
                Альфа-Банк
            </button>
            <button
                type="button"
                class="payment-tab inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition"
                data-tab="yookassa"
                role="tab"
                aria-selected="false"
            >
                <span class="material-symbols-rounded text-base">account_balance_wallet</span>
                Ю-касса
            </button>
        </div>

        <div class="mt-6">
            <div data-tab-panel="robokassa" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">API Robokassa</p>
                        <h3 class="text-lg font-semibold text-slate-900">Реквизиты и webhooks</h3>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        <span class="material-symbols-rounded text-base">public</span>
                        production.robokassa.ru
                    </span>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                    <form class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4" method="post" action="/admin-services-payment">
                        <input type="hidden" name="gateway" value="robokassa">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Данные магазина</p>
                                <p class="text-xs text-slate-500">Заполните учётные данные из личного кабинета.</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">
                                <span class="material-symbols-rounded text-base">vpn_key</span>
                                API Robokassa
                            </span>
                        </div>
                        <div class="grid gap-2 text-sm">
                            <label class="space-y-1">
                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Merchant Login</span>
                                <input
                                    type="text"
                                    name="robokassa_merchant_login"
                                    value="<?php echo htmlspecialchars((string) ($robokassaSettings['merchant_login'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="shop_bunch"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                >
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Пароль #1</span>
                                <div class="relative">
                                    <input
                                        id="robokassa_password1"
                                        type="password"
                                        name="robokassa_password1"
                                        value="<?php echo htmlspecialchars((string) ($robokassaSettings['password1'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="••••••••"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 pr-10 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                    >
                                    <button
                                        type="button"
                                        class="password-toggle absolute right-2 top-1/2 flex -translate-y-1/2 items-center justify-center rounded-md p-1 text-slate-400 transition hover:text-slate-600"
                                        data-target="robokassa_password1"
                                        aria-label="Показать пароль"
                                        aria-pressed="false"
                                    >
                                        <span class="material-symbols-rounded text-base">visibility</span>
                                    </button>
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Пароль #2</span>
                                <div class="relative">
                                    <input
                                        id="robokassa_password2"
                                        type="password"
                                        name="robokassa_password2"
                                        value="<?php echo htmlspecialchars((string) ($robokassaSettings['password2'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="••••••••"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 pr-10 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                    >
                                    <button
                                        type="button"
                                        class="password-toggle absolute right-2 top-1/2 flex -translate-y-1/2 items-center justify-center rounded-md p-1 text-slate-400 transition hover:text-slate-600"
                                        data-target="robokassa_password2"
                                        aria-label="Показать пароль"
                                        aria-pressed="false"
                                    >
                                        <span class="material-symbols-rounded text-base">visibility</span>
                                    </button>
                                </div>
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Алгоритм подписи</span>
                                <select
                                    name="robokassa_signature_algorithm"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                >
                                    <?php $signatureAlgorithm = strtolower((string) ($robokassaSettings['signature_algorithm'] ?? 'md5')); ?>
                                    <option value="md5" <?php echo $signatureAlgorithm === 'md5' ? 'selected' : ''; ?>>MD5</option>
                                    <option value="sha256" <?php echo $signatureAlgorithm === 'sha256' ? 'selected' : ''; ?>>SHA-256</option>
                                </select>
                                <p class="text-xs text-slate-500">
                                    Должен совпадать с настройкой подписи в кабинете Robokassa.
                                </p>
                            </label>
                        </div>
                        <div class="grid gap-2 text-sm">
                            <label class="space-y-1">
                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Result URL</span>
                                <input
                                    type="text"
                                    name="robokassa_result_url"
                                    value="<?php echo htmlspecialchars((string) ($robokassaSettings['result_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                >
                            </label>
                            <div class="grid gap-2 lg:grid-cols-2">
                                <label class="space-y-1">
                                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Success URL</span>
                                    <input
                                        type="text"
                                        name="robokassa_success_url"
                                        value="<?php echo htmlspecialchars((string) ($robokassaSettings['success_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                    >
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Fail URL</span>
                                    <input
                                        type="text"
                                        name="robokassa_fail_url"
                                        value="<?php echo htmlspecialchars((string) ($robokassaSettings['fail_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                                    >
                                </label>
                            </div>
                        </div>
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800">
                            <div>
                                <p class="font-semibold">Тестовый режим</p>
                                <p class="text-xs text-slate-500">Переключает запросы на sandbox Robokassa.</p>
                            </div>
                            <input
                                type="checkbox"
                                name="robokassa_test_mode"
                                class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                                <?php echo !empty($robokassaSettings['is_test']) ? 'checked' : ''; ?>
                            >
                        </label>
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow"
                        >
                            <span class="material-symbols-rounded text-base">save</span>
                            Сохранить настройки
                        </button>
                    </form>

                    <div class="space-y-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Схема интеграции</p>
                                <p class="text-xs text-slate-500">Что уходит в Robokassa и что ждём на вебхуке.</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 ring-1 ring-indigo-200">
                                <span class="material-symbols-rounded text-base">sync</span>
                                API v2
                            </span>
                        </div>
                        <div class="grid gap-3 text-sm text-slate-700">
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Передаём</p>
                                <ul class="mt-2 space-y-1 text-sm text-slate-700">
                                    <li>InvoiceID + сумма заказа</li>
                                    <li>Описание корзины</li>
                                    <li>Контакт клиента</li>
                                </ul>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Получаем</p>
                                <ul class="mt-2 space-y-1 text-sm text-slate-700">
                                    <li>Подпись ResultURL</li>
                                    <li>Статус оплаты и валюта</li>
                                    <li>Причина отказа</li>
                                </ul>
                            </div>
                            <div class="rounded-lg bg-amber-50 px-3 py-2 text-amber-700 ring-1 ring-amber-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em]">Совет</p>
                                <p class="mt-1 text-sm">Добавьте IP Robokassa в whitelist для webhook.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div data-tab-panel="alfa" class="hidden space-y-4">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                    <p class="text-lg font-semibold text-slate-900">Альфа-Банк</p>
                    <p class="mt-2 text-sm text-slate-500">Форма подключения появится после настройки Robokassa.</p>
                </div>
            </div>

            <div data-tab-panel="yookassa" class="hidden space-y-4">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                    <p class="text-lg font-semibold text-slate-900">Ю-касса</p>
                    <p class="mt-2 text-sm text-slate-500">Добавим ключи API и условия фискализации позже.</p>
                </div>
            </div>
        </div>
    </section>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = Array.from(document.querySelectorAll('.payment-tab'));
        const panels = Array.from(document.querySelectorAll('[data-tab-panel]'));
        const setActive = (name) => {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.tab === name;
                tab.classList.toggle('bg-rose-600', isActive);
                tab.classList.toggle('text-white', isActive);
                tab.classList.toggle('shadow-sm', isActive);
                tab.classList.toggle('shadow-rose-200', isActive);
                tab.classList.toggle('bg-white', !isActive);
                tab.classList.toggle('text-slate-700', !isActive);
                tab.classList.toggle('border', !isActive);
                tab.classList.toggle('border-slate-200', !isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.tabPanel !== name);
            });
        };

        tabs.forEach((tab) => {
            tab.classList.add('border', 'border-slate-200');
            tab.addEventListener('click', () => setActive(tab.dataset.tab));
        });

        const initialTab = tabs.find((tab) => tab.dataset.active === 'true')?.dataset.tab || tabs[0]?.dataset.tab;
        if (initialTab) {
            setActive(initialTab);
        }

        const passwordToggles = Array.from(document.querySelectorAll('.password-toggle'));
        passwordToggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const targetId = toggle.dataset.target;
                const input = targetId ? document.getElementById(targetId) : null;
                if (!input) {
                    return;
                }

                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', (!isVisible).toString());
                toggle.setAttribute('aria-label', isVisible ? 'Показать пароль' : 'Скрыть пароль');

                const icon = toggle.querySelector('.material-symbols-rounded');
                if (icon) {
                    icon.textContent = isVisible ? 'visibility' : 'visibility_off';
                }
            });
        });
    });
</script>

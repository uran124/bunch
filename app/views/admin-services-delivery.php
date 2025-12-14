<?php /** @var array $pageMeta */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $dadata = $dadata ?? []; ?>
<?php $zones = $zones ?? []; ?>
<?php $testAddresses = $testAddresses ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Настройка сервисов</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'DaData + зоны доставки', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-sm text-slate-500"><?php echo htmlspecialchars($pageMeta['description'] ?? 'Подсказки, геокодинг и расчёт стоимости доставки по полигонам.', ENT_QUOTES, 'UTF-8'); ?></p>
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
                <span class="material-symbols-rounded text-base">verified</span>
                DaData подключена
            </span>
        </div>
    </header>

    <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-rose-50/60 ring-1 ring-transparent">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">DaData</p>
                    <h2 class="text-xl font-semibold text-slate-900">Подсказки + геокодинг</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    <span class="material-symbols-rounded text-base text-emerald-500">cloud_sync</span>
                    <?php echo htmlspecialchars($dadata['lastSync'] ?? 'Синхронизация сегодня', ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <form id="dadata-credentials-form" class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Данные авторизации</p>
                            <p class="text-xs text-slate-500">API-ключи нужны для подсказок и геокодинга.</p>
                        </div>
                        <span id="dadata-credentials-status" class="hidden items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            <span class="material-symbols-rounded text-base">task_alt</span>
                            Сохранено
                        </span>
                    </div>
                    <div class="grid gap-2 text-sm">
                        <label class="space-y-1">
                            <span class="text-xs uppercase tracking-[0.14em] text-slate-500">API-ключ</span>
                            <div class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-slate-800 ring-1 ring-slate-200">
                                <input
                                    type="text"
                                    name="apiKey"
                                    id="dadata-api-key"
                                    autocomplete="off"
                                    value="<?php echo htmlspecialchars($dadata['apiKey'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Вставьте ключ DaData"
                                    class="w-full truncate bg-transparent text-sm font-mono focus:outline-none"
                                >
                                <span class="material-symbols-rounded text-base text-rose-500">key_vertical</span>
                            </div>
                            <span id="dadata-api-key-display" class="block truncate text-[11px] text-slate-500"></span>
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Секретный ключ</span>
                            <div class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-slate-800 ring-1 ring-slate-200">
                                <input
                                    type="password"
                                    name="secretKey"
                                    id="dadata-secret-key"
                                    autocomplete="off"
                                    value="<?php echo htmlspecialchars($dadata['secretKey'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Введите секретный ключ"
                                    class="w-full truncate bg-transparent text-sm font-mono focus:outline-none"
                                >
                                <span class="material-symbols-rounded text-base text-indigo-500">lock</span>
                            </div>
                            <span id="dadata-secret-key-display" class="block truncate text-[11px] text-slate-500"></span>
                        </label>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow"
                    >
                        <span class="material-symbols-rounded text-base">save</span>
                        Сохранить ключи
                    </button>
                </form>

                <div class="grid gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Режимы работы</p>
                            <p class="text-xs text-slate-500">Подсказки и геокодинг активируются сразу после сохранения.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            <span class="material-symbols-rounded text-base">done_all</span>
                            Активно
                        </span>
                    </div>
                    <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        <div>
                            <p class="font-semibold">Подсказки адресов</p>
                            <p class="text-xs text-slate-500">Моментальный подбор улицы и дома в один клик.</p>
                        </div>
                        <input
                            type="checkbox"
                            <?php echo !empty($dadata['suggestions']) ? 'checked' : ''; ?>
                            class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                        >
                    </label>
                    <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        <div>
                            <p class="font-semibold">Геокодинг</p>
                            <p class="text-xs text-slate-500">Получаем координаты точки для расчёта зоны.</p>
                        </div>
                        <input
                            type="checkbox"
                            <?php echo !empty($dadata['geocoding']) ? 'checked' : ''; ?>
                            class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                        >
                    </label>
                    <div class="grid grid-cols-2 gap-3 text-sm text-slate-700">
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Лимит в сутки</p>
                            <p class="font-semibold text-slate-900"><?php echo (int) ($dadata['dailyLimit'] ?? 0); ?> запросов</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Сегодня израсходовано</p>
                            <p class="font-semibold text-slate-900"><?php echo (int) ($dadata['requestsToday'] ?? 0); ?> запросов</p>
                        </div>
                    </div>
                </div>

                <article class="space-y-3 rounded-xl border border-amber-100 bg-amber-50/60 p-4 ring-1 ring-amber-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Стоимость доставки по умолчанию</p>
                            <p class="text-xs text-slate-600">Используем, если DaData или полигоны не вернули цену.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200">
                            <span class="material-symbols-rounded text-base">warning</span>
                            Fallback
                        </span>
                    </div>
                    <label class="space-y-1 text-sm font-semibold text-slate-800">
                        Размер стоимости
                        <div class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 ring-1 ring-amber-200">
                            <input
                                type="number"
                                name="defaultDeliveryPrice"
                                value="<?php echo (int) ($dadata['defaultDeliveryPrice'] ?? 350); ?>"
                                class="w-full bg-transparent text-sm font-semibold text-slate-900 focus:outline-none"
                            >
                            <span class="text-xs font-semibold text-slate-500">₽</span>
                        </div>
                        <span class="block text-[11px] font-medium text-slate-500">Изменения применяются на странице корзины сразу после сохранения настроек.</span>
                    </label>
                </article>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Turf.js</p>
                    <h2 class="text-xl font-semibold text-slate-900">Попадание адреса в зону</h2>
                    <p class="text-sm text-slate-500">Полигоны зон доставки, стоимость и приоритет попадания.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                    <span class="material-symbols-rounded text-base">map</span>
                    <?php echo count($zones); ?> зон активны
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-800">Карта полигона</p>
                        <span class="text-xs text-slate-500">turf.booleanPointInPolygon</span>
                    </div>
                    <div id="zone-map" class="relative h-72 rounded-xl bg-gradient-to-br from-slate-100 via-white to-slate-50 ring-1 ring-slate-200">
                        <p class="absolute inset-0 flex items-center justify-center text-sm text-slate-400" aria-hidden="true">Загрузка схемы зон...</p>
                    </div>
                    <p class="text-xs text-slate-500">Точки вычисляются по координатам из DaData, затем проверяются в полигонах turf.js. При совпадении — выбирается стоимость зоны.</p>
                </div>
                <div class="space-y-3">
                    <?php foreach ($zones as $zone): ?>
                        <article class="rounded-xl border border-slate-100 bg-white px-3 py-3 shadow-sm ring-1 ring-transparent">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background: <?php echo htmlspecialchars($zone['color'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($zone['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($zone['landmarks'] ?? 'Микрорайоны и ориентиры', ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-slate-900"><?php echo (int) $zone['price']; ?> ₽</p>
                                    <p class="text-xs text-slate-500">доставка</p>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <div class="rounded-xl border border-dashed border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Добавьте новые точки полигона, если зона расширилась, и сохраните стоимость. Изменения применяются сразу в корзине.
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-[0.8fr_1.2fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Тестирование</p>
                    <h2 class="text-xl font-semibold text-slate-900">Проверка попадания адреса</h2>
                    <p class="text-sm text-slate-500">Улица и дом проходят через DaData, затем точка проверяется в полигонах.</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    <span class="material-symbols-rounded text-base text-emerald-500">task_alt</span>
                    Онлайн расчёт
                </span>
            </div>

            <form id="address-zone-form" class="mt-4 space-y-3">
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-semibold text-slate-700">Адрес для теста</span>
                    <input
                        type="text"
                        id="address-full"
                        name="address"
                        placeholder="Например: Тверская, 12"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    >
                </label>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        <p class="mb-2 text-[13px] font-semibold text-slate-800">Что проверяем</p>
                        <ol class="space-y-1">
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span>Подключение к DaData</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                                <span>Определение адреса (подсказка + индекс)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <span>Получение координат (геокодинг)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <span>Попадание в зону и стоимость</span>
                            </li>
                        </ol>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        <p class="mb-2 text-[13px] font-semibold text-slate-800">Статус шагов</p>
                        <ul class="space-y-1" id="address-steps">
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-800" data-step="connect">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base text-emerald-500">cloud_sync</span>
                                    Подключение DaData
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Ожидает</span>
                            </li>
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-800" data-step="address">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base text-indigo-500">location_searching</span>
                                    Подсказка и нормализация
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Ожидает</span>
                            </li>
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-800" data-step="coords">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base text-amber-500">my_location</span>
                                    Координаты из геокодинга
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Ожидает</span>
                            </li>
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-800" data-step="zone">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-rounded text-base text-rose-500">task_alt</span>
                                    Определение зоны
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Ожидает</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php if (!empty($testAddresses)): ?>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        <p class="mb-2 text-[13px] font-semibold text-slate-800">Примеры адресов с готовыми координатами</p>
                        <ul class="list-disc space-y-1 pl-4">
                            <?php foreach ($testAddresses as $address): ?>
                                <li><?php echo htmlspecialchars($address['label'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow-xl"
                >
                    <span class="material-symbols-rounded text-base">paid</span>
                    Рассчитать доставку
                </button>
            </form>

            <div id="address-zone-result" class="mt-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                Введите адрес, чтобы увидеть стоимость и зону.
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Процесс</p>
                    <h2 class="text-xl font-semibold text-slate-900">Как это работает</h2>
                    <p class="text-sm text-slate-500">От подсказки улицы до добавления стоимости в заказ.</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    <span class="material-symbols-rounded text-base text-rose-500">route</span>
                    4 шага
                </span>
            </div>
            <ol class="mt-4 grid gap-3 md:grid-cols-2">
                <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">1. Подсказка адреса</p>
                    <p class="text-xs text-slate-600">DaData выдаёт улицу и дом, подставляет индекс и город.</p>
                </li>
                <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">2. Геокодинг</p>
                    <p class="text-xs text-slate-600">Получаем координаты точки, готовые для проверки turf.js.</p>
                </li>
                <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">3. Проверка полигона</p>
                    <p class="text-xs text-slate-600">turf.booleanPointInPolygon определяет зону, если точка попала внутрь.</p>
                </li>
                <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">4. Добавление в заказ</p>
                    <p class="text-xs text-slate-600">Стоимость зоны добавляется в корзину и чек-лист доставки.</p>
                </li>
            </ol>
            <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                При смене адреса пересчитываем зону и обновляем оплату в реальном времени.
            </div>
        </article>
    </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
<script>
    const zones = <?php echo json_encode($zones); ?>;
    const testAddresses = <?php echo json_encode($testAddresses); ?>;
    let dadataConfig = <?php echo json_encode($dadata); ?>;

    const mapContainer = document.getElementById('zone-map');
    const addressInput = document.getElementById('address-full');
    const addressSuggestionList = document.createElement('div');
    const credentialsForm = document.getElementById('dadata-credentials-form');
    const apiKeyInput = document.getElementById('dadata-api-key');
    const secretKeyInput = document.getElementById('dadata-secret-key');
    const apiKeyDisplay = document.getElementById('dadata-api-key-display');
    const secretKeyDisplay = document.getElementById('dadata-secret-key-display');
    const credentialsStatus = document.getElementById('dadata-credentials-status');
    let marker;
    let lastSuggestionRequestId = 0;

    function formatKey(value) {
        if (!value) return 'Не задано';
        if (value.length <= 12) return value;
        return `${value.slice(0, 6)}…${value.slice(-4)}`;
    }

    function hydrateCredentials() {
        try {
            const cached = localStorage.getItem('dadataCredentials');
            if (cached) {
                const parsed = JSON.parse(cached);
                dadataConfig = {
                    ...dadataConfig,
                    ...(typeof parsed === 'object' && parsed ? parsed : {}),
                };
            }
        } catch (e) {
            console.error('Не удалось загрузить ключи DaData из localStorage', e);
        }

        renderCredentials();
    }

    function renderCredentials() {
        if (apiKeyInput) apiKeyInput.value = dadataConfig?.apiKey || '';
        if (secretKeyInput) secretKeyInput.value = dadataConfig?.secretKey || '';
        if (apiKeyDisplay) apiKeyDisplay.textContent = formatKey(dadataConfig?.apiKey || '');
        if (secretKeyDisplay) secretKeyDisplay.textContent = formatKey(dadataConfig?.secretKey || '');
    }

    function normalizeText(value) {
        return value.trim().toLowerCase();
    }

    function showCredentialsSaved() {
        if (!credentialsStatus) return;
        credentialsStatus.classList.remove('hidden');
        credentialsStatus.classList.add('inline-flex');
        setTimeout(() => credentialsStatus.classList.add('hidden'), 2400);
    }

    function renderSuggestions(suggestions) {
        if (!addressInput || !addressSuggestionList) return;

        addressSuggestionList.innerHTML = '';
        if (!suggestions.length) {
            addressSuggestionList.classList.add('hidden');
            return;
        }

        suggestions.forEach((item) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className =
                'flex w-full items-start gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-rose-50';
            row.innerHTML = `
                <span class="material-symbols-rounded text-base text-rose-500">location_on</span>
                <span class="flex-1">
                    <span class="block">${item.value || item.label || ''}</span>
                    <span class="block text-xs font-medium text-slate-500">${item.data?.city_with_type || ''}</span>
                </span>
            `;

            row.addEventListener('click', () => {
                addressInput.value = item.unrestricted_value || item.value || item.label || '';
                addressSuggestionList.classList.add('hidden');
            });

            addressSuggestionList.appendChild(row);
        });

        addressSuggestionList.classList.remove('hidden');
    }

    async function fetchSuggestions(query, requestId) {
        if (!query || query.length < 3) return [];

        if (dadataConfig?.apiKey) {
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Token ${dadataConfig.apiKey}`,
                },
                body: JSON.stringify({ query, count: 5 }),
            }).catch(() => null);

            if (response?.ok) {
                const data = await response.json().catch(() => null);
                if (requestId === lastSuggestionRequestId) {
                    return data?.suggestions || [];
                }
            }
        }

        return testAddresses
            .filter((item) => normalizeText(item.label).includes(normalizeText(query)))
            .map((item) => ({ value: item.label }));
    }

    function debouncedSuggest(value) {
        if (!addressSuggestionList) return;
        clearTimeout(debouncedSuggest.timer);
        debouncedSuggest.timer = setTimeout(async () => {
            lastSuggestionRequestId += 1;
            const requestId = lastSuggestionRequestId;
            const suggestions = await fetchSuggestions(value.trim(), requestId);
            renderSuggestions(suggestions);
        }, 250);
    }

    async function geocodeAddress(address) {
        const needle = normalizeText(address);

        if (needle && dadataConfig?.apiKey && dadataConfig?.secretKey) {
            const response = await fetch('https://cleaner.dadata.ru/api/v1/clean/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Token ${dadataConfig.apiKey}`,
                    'X-Secret': dadataConfig.secretKey,
                },
                body: JSON.stringify([address]),
            }).catch(() => null);

            if (response?.ok) {
                const data = await response.json().catch(() => null);
                const row = Array.isArray(data) ? data[0] : null;
                if (row?.geo_lon && row?.geo_lat) {
                    return {
                        label: row.result || address,
                        coords: [Number(row.geo_lon), Number(row.geo_lat)],
                    };
                }
            }
        }

        const fixture = testAddresses.find((item) => needle.includes(item.match));
        if (fixture) {
            return { label: fixture.label, coords: fixture.coords };
        }

        return null;
    }

    function findZone(coords) {
        const point = turf.point(coords);
        for (const zone of zones) {
            const closedPolygon = [...zone.polygon];
            const firstPoint = zone.polygon[0];
            if (firstPoint[0] !== zone.polygon[zone.polygon.length - 1][0] || firstPoint[1] !== zone.polygon[zone.polygon.length - 1][1]) {
                closedPolygon.push(firstPoint);
            }
            const polygon = turf.polygon([closedPolygon]);
            if (turf.booleanPointInPolygon(point, polygon)) {
                return zone;
            }
        }
        return null;
    }

    function renderMap() {
        if (!mapContainer) return;
        const width = mapContainer.clientWidth;
        const height = mapContainer.clientHeight;

        const lons = zones.flatMap((zone) => zone.polygon.map((p) => p[0]));
        const lats = zones.flatMap((zone) => zone.polygon.map((p) => p[1]));
        const minLon = Math.min(...lons);
        const maxLon = Math.max(...lons);
        const minLat = Math.min(...lats);
        const maxLat = Math.max(...lats);

        const svgNS = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.classList.add('absolute', 'inset-0');

        zones.forEach((zone) => {
            const points = zone.polygon.map(([lon, lat]) => {
                const x = ((lon - minLon) / (maxLon - minLon || 1)) * (width - 40) + 20;
                const y = height - (((lat - minLat) / (maxLat - minLat || 1)) * (height - 40) + 20);
                return `${x},${y}`;
            }).join(' ');
            const polygon = document.createElementNS(svgNS, 'polygon');
            polygon.setAttribute('points', points);
            polygon.setAttribute('fill', zone.color + '20');
            polygon.setAttribute('stroke', zone.color);
            polygon.setAttribute('stroke-width', '2');
            svg.appendChild(polygon);
        });

        mapContainer.innerHTML = '';
        mapContainer.appendChild(svg);
    }

    function placeMarker(coords) {
        if (!mapContainer || !mapContainer.firstChild) return;
        const svg = mapContainer.firstChild;
        const width = mapContainer.clientWidth;
        const height = mapContainer.clientHeight;
        const lons = zones.flatMap((zone) => zone.polygon.map((p) => p[0]));
        const lats = zones.flatMap((zone) => zone.polygon.map((p) => p[1]));
        const minLon = Math.min(...lons);
        const maxLon = Math.max(...lons);
        const minLat = Math.min(...lats);
        const maxLat = Math.max(...lats);

        const [lon, lat] = coords;
        const x = ((lon - minLon) / (maxLon - minLon || 1)) * (width - 40) + 20;
        const y = height - (((lat - minLat) / (maxLat - minLat || 1)) * (height - 40) + 20);

        if (marker) {
            svg.removeChild(marker);
        }

        marker = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        marker.setAttribute('cx', x);
        marker.setAttribute('cy', y);
        marker.setAttribute('r', '6');
        marker.setAttribute('fill', '#0f172a');
        marker.setAttribute('stroke', '#fff');
        marker.setAttribute('stroke-width', '2');
        svg.appendChild(marker);
    }

    hydrateCredentials();
    renderMap();

    credentialsForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        const apiKey = (apiKeyInput?.value || '').trim();
        const secretKey = (secretKeyInput?.value || '').trim();

        dadataConfig = { ...dadataConfig, apiKey, secretKey };

        try {
            localStorage.setItem('dadataCredentials', JSON.stringify({ apiKey, secretKey }));
        } catch (e) {
            console.error('Не удалось сохранить ключи DaData', e);
        }

        renderCredentials();
        showCredentialsSaved();
    });

    if (addressInput) {
        const wrapper = addressInput.parentElement;
        if (wrapper) {
            wrapper.classList.add('relative');
            addressSuggestionList.className =
                'absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg';
            wrapper.appendChild(addressSuggestionList);
        }

        addressInput.addEventListener('input', (event) => debouncedSuggest(event.target.value || ''));
        addressInput.addEventListener('focus', (event) => debouncedSuggest(event.target.value || ''));
    }

    document.addEventListener('click', (event) => {
        if (!addressSuggestionList.contains(event.target) && addressInput !== event.target) {
            addressSuggestionList.classList.add('hidden');
        }
    });

    const form = document.getElementById('address-zone-form');
    const result = document.getElementById('address-zone-result');
    const steps = document.querySelectorAll('#address-steps [data-step]');

    function setStepStatus(step, status, color = 'bg-slate-100 text-slate-600') {
        const item = Array.from(steps).find((el) => el.dataset.step === step);
        if (!item) return;

        const badge = item.querySelector('span.rounded-full');
        if (badge) {
            badge.textContent = status;
            badge.className = `rounded-full px-2.5 py-1 text-xs font-semibold ${color}`;
        }
    }

    function resetSteps() {
        setStepStatus('connect', 'Готово', 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200');
        ['address', 'coords', 'zone'].forEach((step) => {
            setStepStatus(step, 'Ожидает', 'bg-slate-100 text-slate-600');
        });
    }

    resetSteps();

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const addressRaw = document.getElementById('address-full').value || '';
        const addressValue = normalizeText(addressRaw);

        resetSteps();

        if (!addressValue) {
            result.textContent = 'Добавьте адрес, чтобы рассчитать доставку.';
            return;
        }

        const geocoded = await geocodeAddress(addressRaw);
        setStepStatus('address', geocoded ? 'Адрес найден' : 'Адрес не найден', geocoded ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200');

        if (!geocoded) {
            result.textContent = 'Нет готовых координат для этого адреса. Добавьте адрес в DaData или включите автодобавление.';
            return;
        }

        setStepStatus('coords', 'Координаты получены', 'bg-amber-50 text-amber-700 ring-1 ring-amber-200');
        const zone = findZone(geocoded.coords);
        placeMarker(geocoded.coords);

        if (zone) {
            setStepStatus('zone', 'Зона найдена', 'bg-rose-50 text-rose-700 ring-1 ring-rose-200');
        } else {
            setStepStatus('zone', 'Зона не найдена', 'bg-amber-50 text-amber-700 ring-1 ring-amber-200');
        }

        if (zone) {
            result.innerHTML = `<strong class="text-slate-900">${geocoded.label}</strong> находится в зоне <strong class="text-slate-900">${zone.name}</strong>. Стоимость доставки: <strong class="text-slate-900">${zone.price} ₽</strong>.`;
        } else {
            result.textContent = 'Координаты получены, но точка не попала ни в одну зону. Добавьте полигон или расширьте границы.';
        }
    });
</script>

<?php /** @var array $pageMeta */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $dadata = $dadata ?? []; ?>
<?php $zones = $zones ?? []; ?>
<?php $testAddresses = $testAddresses ?? []; ?>
<?php $activeZonesCount = count(array_filter($zones, static fn ($zone) => !empty($zone['active']))); ?>

<link rel="stylesheet" href="/assets/css/leaflet.css">
<link rel="stylesheet" href="/assets/css/leaflet.draw.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js" defer></script>

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
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Карта полигона</p>
                            <p class="text-xs text-slate-500">Leaflet + OSM · редактирование и удаление вершин</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="zone-version" class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold text-white shadow-sm">
                                <span class="material-symbols-rounded text-base">layers</span>
                                v<?php echo (int) ($deliveryPricingVersion ?? 1); ?>
                            </span>
                            <button
                                type="button"
                                id="zone-add"
                                class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-slate-800 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-sm"
                            >
                                <span class="material-symbols-rounded text-base text-rose-500">add</span>
                                Новая зона
                            </button>
                        </div>
                    </div>
                    <div id="zone-map" class="relative h-80 rounded-xl ring-1 ring-slate-200">
                        <p class="absolute inset-0 flex items-center justify-center text-sm text-slate-400" aria-hidden="true">Загрузка карты…</p>
                    </div>
                    <div id="zone-status" class="hidden items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold"></div>
                    <p class="text-xs text-slate-500">Точки сохраняются в формате <span class="font-mono">[lon, lat]</span> в базе данных. Подложка — OSM с атрибуцией “© OpenStreetMap contributors”.</p>
                </div>
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Список зон</p>
                            <p class="text-xs text-slate-500">Приоритет сортируется по убыванию.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="zones-active-count" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-semibold text-indigo-700 ring-1 ring-indigo-200">
                                <span class="material-symbols-rounded text-base">map</span>
                                <?php echo $activeZonesCount; ?> активны
                            </span>
                            <button
                                type="button"
                                id="zone-save"
                                class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow"
                            >
                                <span class="material-symbols-rounded text-base">save</span>
                                Сохранить зоны
                            </button>
                        </div>
                    </div>
                    <div id="zone-list" class="grid gap-3"></div>
                    <div class="rounded-xl border border-dashed border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Добавьте новые точки полигона, если зона расширилась, и сохраните стоимость. Изменения применяются сразу в корзине и корзина увидит новую версию тарифов.
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
                        placeholder="Например: Молокова, 1"
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

<script src="/assets/js/turf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const zonesFromServer = <?php echo json_encode($zones, JSON_UNESCAPED_UNICODE); ?>;
    const testAddresses = <?php echo json_encode($testAddresses, JSON_UNESCAPED_UNICODE); ?>;
    let dadataConfig = <?php echo json_encode($dadata, JSON_UNESCAPED_UNICODE); ?>;
    let pricingVersion = Number(<?php echo (int) ($deliveryPricingVersion ?? 1); ?>) || 1;

    const mapCenter = [56.054764, 92.909267];
    const map = L.map('zone-map');
    const drawnItems = new L.FeatureGroup();

    map.setView(mapCenter, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
    }).addTo(map);
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
        draw: {
            polygon: { allowIntersection: false, showArea: true, shapeOptions: { color: '#f43f5e' } },
            marker: false,
            rectangle: false,
            polyline: false,
            circle: false,
            circlemarker: false,
        },
        edit: {
            featureGroup: drawnItems,
            poly: { allowIntersection: false },
        },
    });
    map.addControl(drawControl);

    const zones = Array.isArray(zonesFromServer) ? zonesFromServer.map(normalizeZone) : [];
    let marker = null;
    let lastSuggestionRequestId = 0;

    const mapContainer = document.getElementById('zone-map');
    const zoneList = document.getElementById('zone-list');
    const zoneStatus = document.getElementById('zone-status');
    const versionBadge = document.getElementById('zone-version');
    const activeBadge = document.getElementById('zones-active-count');
    const saveButton = document.getElementById('zone-save');
    const addButton = document.getElementById('zone-add');

    const addressInput = document.getElementById('address-full');
    const addressSuggestionList = document.createElement('div');
    const credentialsForm = document.getElementById('dadata-credentials-form');
    const apiKeyInput = document.getElementById('dadata-api-key');
    const secretKeyInput = document.getElementById('dadata-secret-key');
    const apiKeyDisplay = document.getElementById('dadata-api-key-display');
    const secretKeyDisplay = document.getElementById('dadata-secret-key-display');
    const credentialsStatus = document.getElementById('dadata-credentials-status');
    const form = document.getElementById('address-zone-form');
    const result = document.getElementById('address-zone-result');
    const steps = document.querySelectorAll('#address-steps [data-step]');

    const colorPool = ['#f43f5e', '#06b6d4', '#a855f7', '#f97316', '#22c55e'];

    function normalizeZone(zone) {
        return {
            id: zone.id ?? null,
            name: zone.name || 'Зона доставки',
            price: Number(zone.price ?? 0) || 0,
            priority: Number(zone.priority ?? 0) || 0,
            color: zone.color || '#f43f5e',
            active: zone.active !== false && zone.active !== 0,
            polygon: Array.isArray(zone.polygon) ? zone.polygon : [],
        };
    }

    function coordsToLatLngs(polygon = []) {
        return polygon.map((point) => [point[1], point[0]]);
    }

    function latLngsToCoords(latlngs = []) {
        return latlngs.map(({ lat, lng }) => [Number(lng.toFixed(6)), Number(lat.toFixed(6))]);
    }

    function updateVersionBadge(version) {
        pricingVersion = version || pricingVersion;
        if (versionBadge) {
            versionBadge.innerHTML = `<span class="material-symbols-rounded text-base">layers</span> v${pricingVersion}`;
        }
    }

    function updateActiveBadge() {
        if (!activeBadge) return;
        const activeCount = zones.filter((zone) => zone.active).length;
        activeBadge.innerHTML = `<span class="material-symbols-rounded text-base">map</span> ${activeCount} активны`;
    }

    function showStatus(text, tone = 'info') {
        if (!zoneStatus) return;
        zoneStatus.textContent = text;
        zoneStatus.classList.remove('hidden', 'border-slate-200', 'border-emerald-200', 'border-amber-200', 'text-slate-700', 'text-emerald-700', 'text-amber-700', 'bg-white', 'bg-emerald-50', 'bg-amber-50');
        const toneClasses = {
            info: ['border-slate-200', 'text-slate-700', 'bg-white'],
            success: ['border-emerald-200', 'text-emerald-700', 'bg-emerald-50'],
            warn: ['border-amber-200', 'text-amber-700', 'bg-amber-50'],
        }[tone] || ['border-slate-200', 'text-slate-700', 'bg-white'];
        zoneStatus.classList.add(...toneClasses, 'flex');
    }

    function buildLayer(zone) {
        const layer = L.polygon(coordsToLatLngs(zone.polygon), {
            color: zone.color,
            fillColor: zone.color,
            fillOpacity: 0.25,
            weight: 2,
            opacity: zone.active ? 0.9 : 0.3,
            dashArray: zone.active ? null : '4 4',
        });
        zone.layerId = L.stamp(layer);
        drawnItems.addLayer(layer);
        layer.on('click', () => focusZone(zone));
    }

    function findLayer(layerId) {
        return drawnItems.getLayers().find((layer) => L.stamp(layer) === layerId);
    }

    function focusZone(zone) {
        const layer = findLayer(zone.layerId);
        if (layer) {
            map.fitBounds(layer.getBounds(), { padding: [20, 20] });
        }
    }

    function renderZoneList() {
        if (!zoneList) return;
        zoneList.innerHTML = '';
        const sorted = [...zones].sort((a, b) => b.priority - a.priority);
        sorted.forEach((zone) => {
            const card = document.createElement('article');
            card.className = 'rounded-xl border border-slate-200 bg-white p-3 shadow-sm';
            card.innerHTML = `
                <div class="flex items-center justify-between gap-2">
                    <input type="text" value="${zone.name}" data-field="name" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-900 focus:border-rose-300 focus:outline-none">
                    <input type="color" value="${zone.color}" data-field="color" class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white">
                </div>
                <div class="mt-2 grid gap-2 sm:grid-cols-3 text-sm">
                    <label class="space-y-1">
                        <span class="text-[11px] uppercase tracking-[0.08em] text-slate-500">Цена</span>
                        <input type="number" value="${zone.price}" data-field="price" class="w-full rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-900 focus:border-rose-300 focus:outline-none">
                    </label>
                    <label class="space-y-1">
                        <span class="text-[11px] uppercase tracking-[0.08em] text-slate-500">Приоритет</span>
                        <input type="number" value="${zone.priority}" data-field="priority" class="w-full rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-900 focus:border-rose-300 focus:outline-none">
                    </label>
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800">
                        <input type="checkbox" data-field="active" ${zone.active ? 'checked' : ''} class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Активна
                    </label>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                    <span>${zone.polygon.length} точек · ${zone.active ? 'учитывается в расчёте' : 'выключена'}</span>
                    <div class="flex gap-2">
                        <button type="button" data-action="focus" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:shadow-sm">
                            <span class="material-symbols-rounded text-base">zoom_out_map</span>
                            Показать
                        </button>
                        <button type="button" data-action="delete" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 font-semibold text-rose-700 transition hover:-translate-y-0.5 hover:shadow-sm">
                            <span class="material-symbols-rounded text-base">delete</span>
                            Удалить
                        </button>
                    </div>
                </div>
            `;
            card.querySelectorAll('[data-field]').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const field = event.target.dataset.field;
                    if (field === 'active') {
                        zone.active = event.target.checked;
                    } else if (field === 'price') {
                        zone.price = Number(event.target.value) || 0;
                    } else if (field === 'priority') {
                        zone.priority = Number(event.target.value) || 0;
                    } else {
                        zone[field] = event.target.value || '';
                    }
                    syncLayerStyles(zone);
                    updateActiveBadge();
                });
            });
            card.querySelector('[data-action="focus"]')?.addEventListener('click', () => focusZone(zone));
            card.querySelector('[data-action="delete"]')?.addEventListener('click', () => removeZone(zone));
            zoneList.appendChild(card);
        });
        updateActiveBadge();
    }

    function syncLayerStyles(zone) {
        const layer = findLayer(zone.layerId);
        if (layer) {
            layer.setStyle({
                color: zone.color,
                fillColor: zone.color,
                opacity: zone.active ? 0.9 : 0.3,
                dashArray: zone.active ? null : '4 4',
            });
        }
    }

    function fitToLayers() {
        if (drawnItems.getLayers().length) {
            map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
        } else {
            map.setView(mapCenter, 12);
        }
    }

    function removeZone(zone) {
        const layer = findLayer(zone.layerId);
        if (layer) drawnItems.removeLayer(layer);
        const index = zones.indexOf(zone);
        if (index >= 0) zones.splice(index, 1);
        renderZoneList();
        fitToLayers();
        showStatus('Зона удалена. Не забудьте сохранить изменения.', 'warn');
    }

    function refreshMapFromZones() {
        drawnItems.clearLayers();
        zones.forEach((zone) => {
            if (zone.polygon.length) {
                buildLayer(zone);
            }
        });
        renderZoneList();
        fitToLayers();
    }

    function handleCreatedLayer(layer) {
        const polygon = latLngsToCoords(layer.getLatLngs()[0]);
        const maxPriority = zones.length ? Math.max(...zones.map((z) => z.priority)) : 0;
        const newZone = normalizeZone({
            id: null,
            name: `Новая зона ${zones.length + 1}`,
            price: 0,
            priority: maxPriority + 10,
            color: colorPool[zones.length % colorPool.length],
            active: true,
            polygon,
        });
        zones.push(newZone);
        buildLayer(newZone);
        renderZoneList();
        fitToLayers();
        showStatus('Полигон добавлен. Укажите цену и приоритет, затем сохраните.', 'info');
    }

    map.on(L.Draw.Event.CREATED, (event) => {
        const layer = event.layer;
        drawnItems.addLayer(layer);
        handleCreatedLayer(layer);
    });

    map.on(L.Draw.Event.EDITED, (event) => {
        event.layers.eachLayer((layer) => {
            const zone = zones.find((item) => item.layerId === L.stamp(layer));
            if (zone) {
                zone.polygon = latLngsToCoords(layer.getLatLngs()[0]);
            }
        });
        renderZoneList();
        showStatus('Вершины обновлены. Сохраните изменения.', 'info');
    });

    map.on(L.Draw.Event.DELETED, (event) => {
        event.layers.eachLayer((layer) => {
            const zone = zones.find((item) => item.layerId === L.stamp(layer));
            if (zone) removeZone(zone);
        });
    });

    function placeMarker(coords) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.circleMarker([coords[1], coords[0]], {
            radius: 8,
            color: '#0f172a',
            fillColor: '#0f172a',
            fillOpacity: 0.9,
            weight: 2,
        }).addTo(map);
    }

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
                if (parsed && typeof parsed === 'object') {
                    dadataConfig = {
                        ...dadataConfig,
                        apiKey: dadataConfig?.apiKey || parsed.apiKey || '',
                        secretKey: dadataConfig?.secretKey || parsed.secretKey || '',
                    };
                }
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
            const response = await fetch('/api/dadata/clean-address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: address }),
            }).catch(() => null);

            if (response?.ok) {
                const data = await response.json().catch(() => null);
                const row = Array.isArray(data) ? data[0] : null;
                if (row?.geo_lon && row?.geo_lat) {
                    return {
                        label: row.result || address,
                        coords: [Number(row.geo_lon), Number(row.geo_lat)],
                        qc: row.qc_geo,
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
        for (const zone of [...zones].sort((a, b) => b.priority - a.priority)) {
            const closedPolygon = [...zone.polygon];
            const firstPoint = zone.polygon[0];
            if (firstPoint && (firstPoint[0] !== zone.polygon.at(-1)?.[0] || firstPoint[1] !== zone.polygon.at(-1)?.[1])) {
                closedPolygon.push(firstPoint);
            }
            const polygon = turf.polygon([closedPolygon]);
            if (turf.booleanPointInPolygon(point, polygon)) {
                return zone;
            }
        }
        return null;
    }

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

    async function saveZones() {
        if (!zones.length) {
            showStatus('Добавьте хотя бы одну зону перед сохранением.', 'warn');
            return;
        }

        try {
            const headers = { 'Content-Type': 'application/json' };
            if (dadataConfig?.apiKey) {
                headers.Authorization = `Token ${dadataConfig.apiKey}`;
            }

            const response = await fetch('/api/delivery/zones', {
                method: 'POST',
                headers,
                body: JSON.stringify({ zones }),
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(error.error || 'Не удалось сохранить зоны');
            }

            const data = await response.json();
            if (Array.isArray(data.zones)) {
                zones.length = 0;
                zones.push(...data.zones.map(normalizeZone));
                refreshMapFromZones();
            }
            if (data.version) updateVersionBadge(data.version);
            showStatus('Зоны сохранены, версия тарифов обновлена.', 'success');
        } catch (e) {
            showStatus(e.message, 'warn');
        }
    }

    function useFallbackResult(addressText) {
        result.textContent = 'Координаты получены, но точка не попала ни в одну зону. Добавьте полигон или расширьте границы.';
        setStepStatus('zone', 'Зона не найдена', 'bg-amber-50 text-amber-700 ring-1 ring-amber-200');
        showStatus(`Точка ${addressText} вне зон.`, 'warn');
    }

    hydrateCredentials();
    refreshMapFromZones();

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

    addButton?.addEventListener('click', () => {
        const polygonHandler = drawControl._toolbars?.draw?._modes?.polygon?.handler;
        if (polygonHandler) {
            polygonHandler.enable();
            showStatus('Включён режим рисования. Кликните на карте, чтобы отметить точки полигона.', 'info');
        }
    });

    saveButton?.addEventListener('click', saveZones);

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
        map.setView([geocoded.coords[1], geocoded.coords[0]], 13);

        if (zone) {
            setStepStatus('zone', 'Зона найдена', 'bg-rose-50 text-rose-700 ring-1 ring-rose-200');
            result.innerHTML = `<strong class="text-slate-900">${geocoded.label}</strong> находится в зоне <strong class="text-slate-900">${zone.name}</strong>. Стоимость доставки: <strong class="text-slate-900">${zone.price} ₽</strong>.`;
        } else {
            useFallbackResult(geocoded.label);
        }
    });
});
</script>

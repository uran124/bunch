<?php
$pageMeta = array_merge($pageMeta ?? [], [
    'title' => $pageMeta['title'] ?? 'Оплата и доставка — Bunch flowers',
]);

$deliveryPricingMode = $deliveryPricingMode ?? 'turf';
$deliveryDistanceRates = is_array($deliveryDistanceRates ?? null) ? $deliveryDistanceRates : [];
$orsApiKey = (string) ($orsApiKey ?? '');
$orsOrigin = is_array($orsOrigin ?? null) ? $orsOrigin : ['lat' => '', 'lon' => ''];
$deliveryFallbackPrice = (int) ($deliveryFallbackPrice ?? 0);
$dadataConfig = is_array($dadataConfig ?? null) ? $dadataConfig : [];
$testAddresses = is_array($testAddresses ?? null) ? $testAddresses : [];
?>

<section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div
        class="space-y-4 rounded-2xl border border-rose-100 bg-rose-50/40 p-4"
        data-delivery-calculator
        data-delivery-pricing-mode="<?php echo htmlspecialchars($deliveryPricingMode, ENT_QUOTES, 'UTF-8'); ?>"
        data-delivery-distance-ranges="<?php echo htmlspecialchars(json_encode($deliveryDistanceRates, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
        data-ors-api-key="<?php echo htmlspecialchars($orsApiKey, ENT_QUOTES, 'UTF-8'); ?>"
        data-ors-origin-lat="<?php echo htmlspecialchars((string) ($orsOrigin['lat'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        data-ors-origin-lon="<?php echo htmlspecialchars((string) ($orsOrigin['lon'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        data-delivery-fallback="<?php echo htmlspecialchars((string) $deliveryFallbackPrice, ENT_QUOTES, 'UTF-8'); ?>"
        data-dadata-config="<?php echo htmlspecialchars(json_encode($dadataConfig, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
        data-test-addresses="<?php echo htmlspecialchars(json_encode($testAddresses, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
    >
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Оплата и доставка</h1>
            <p class="text-sm text-slate-500">Введите адрес как в корзине — выберите из подсказок DaData, затем посчитаем километраж и стоимость.</p>
        </div>

        <label class="space-y-2 text-sm font-medium text-slate-700">
            Адрес доставки
            <div class="relative flex flex-col gap-2 sm:flex-row">
                <input
                    type="text"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    placeholder="Красноярск, ул. Авиаторов, 1"
                    autocomplete="off"
                    data-delivery-address
                >
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                    data-delivery-calc-btn
                >Рассчитать</button>
                <div
                    class="absolute left-0 right-0 top-[calc(100%+4px)] z-20 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-xl"
                    data-delivery-suggestion-list
                ></div>
            </div>
        </label>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Километраж</p>
                <p class="text-lg font-semibold text-slate-900" data-delivery-distance>—</p>
            </div>
            <div class="rounded-xl bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Стоимость доставки</p>
                <p class="text-lg font-semibold text-slate-900" data-delivery-price>—</p>
            </div>
            <div class="rounded-xl bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Тарифный диапазон</p>
                <p class="text-sm font-semibold text-slate-700" data-delivery-range>—</p>
            </div>
        </div>

        <p class="text-sm font-semibold text-slate-700" data-delivery-status>Введите адрес для расчёта</p>
    </div>

    <div class="space-y-4 text-sm text-slate-700">
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Оплата</h2>
            <ul class="list-disc space-y-1 pl-5">
                <li>онлайн-оплата банковской картой;</li>
                <li>перевод по счёту для корпоративных клиентов;</li>
                <li>оплата наличными при самовывозе.</li>
            </ul>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Доставка</h2>
            <ul class="list-disc space-y-1 pl-5">
                <li>доставляем ежедневно по выбранному интервалу;</li>
                <li>расчёт на странице ориентировочный, финальную сумму подтверждает менеджер;</li>
                <li>стоимость рассчитывается по километражу и тарифным диапазонам, как в корзине;</li>
                <li>уточняем детали по телефону перед выездом курьера.</li>
            </ul>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Самовывоз</h2>
            <p>Забрать заказ можно по адресу: 9 мая 73. Предупредите нас заранее, чтобы мы подготовили букет к выдаче.</p>
        </div>
    </div>
</section>

<script>
(() => {
    const calculator = document.querySelector('[data-delivery-calculator]');
    if (!calculator) return;

    const addressInput = calculator.querySelector('[data-delivery-address]');
    const calcButton = calculator.querySelector('[data-delivery-calc-btn]');
    const distanceNode = calculator.querySelector('[data-delivery-distance]');
    const priceNode = calculator.querySelector('[data-delivery-price]');
    const rangeNode = calculator.querySelector('[data-delivery-range]');
    const statusNode = calculator.querySelector('[data-delivery-status]');
    const suggestionList = calculator.querySelector('[data-delivery-suggestion-list]');

    const deliveryPricingMode = calculator.dataset.deliveryPricingMode || 'turf';
    const orsApiKey = (calculator.dataset.orsApiKey || '').trim();
    const orsOrigin = {
        lat: Number(calculator.dataset.orsOriginLat || 0),
        lon: Number(calculator.dataset.orsOriginLon || 0),
    };
    const fallbackDeliveryPrice = Number(calculator.dataset.deliveryFallback || 0);
    const dadataConfig = (() => {
        try {
            return JSON.parse(calculator.dataset.dadataConfig || '{}');
        } catch (e) {
            return {};
        }
    })();
    const testAddresses = (() => {
        try {
            return JSON.parse(calculator.dataset.testAddresses || '[]');
        } catch (e) {
            return [];
        }
    })();
    const distanceRanges = (() => {
        try {
            return JSON.parse(calculator.dataset.deliveryDistanceRanges || '[]');
        } catch (e) {
            return [];
        }
    })();

    let lastSuggestionRequestId = 0;

    const setStatus = (text, tone = 'muted') => {
        statusNode.className = 'text-sm font-semibold';
        if (tone === 'success') {
            statusNode.classList.add('text-emerald-700');
        } else if (tone === 'error') {
            statusNode.classList.add('text-rose-700');
        } else {
            statusNode.classList.add('text-slate-700');
        }
        statusNode.textContent = text;
    };

    const normalizeText = (value) => String(value || '').toLowerCase().replace(/ё/g, 'е').trim();
    const DADATA_CENTER = { lat: 56.233717, lon: 92.8426 };

    const formatAddressFromDadata = (data = {}) => {
        const cityLabel = data.settlement_with_type || data.city_with_type || data.settlement || data.city || '';
        const street = data.street_with_type || data.street || '';
        const house = data.house ? `д. ${data.house}` : '';
        return [cityLabel, street, house].filter(Boolean).join(', ');
    };

    const hideSuggestions = () => {
        if (!suggestionList) return;
        suggestionList.classList.add('hidden');
    };

    const renderSuggestions = (suggestions) => {
        if (!suggestionList) return;

        suggestionList.innerHTML = '';
        if (!suggestions.length) {
            hideSuggestions();
            return;
        }

        suggestions.forEach((item) => {
            const data = item.data || {};
            const formatted = formatAddressFromDadata(data) || item.value || '';
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'flex w-full items-start gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-rose-50';
            row.innerHTML = `
                <span class="material-symbols-rounded text-base text-rose-500">location_on</span>
                <span class="flex-1">${formatted}</span>
            `;
            row.addEventListener('click', () => {
                addressInput.value = formatted;
                hideSuggestions();
                setStatus('Адрес выбран из подсказок. Нажмите «Рассчитать».');
            });
            suggestionList.appendChild(row);
        });

        suggestionList.classList.remove('hidden');
    };

    const fetchSuggestions = async (query, requestId) => {
        if (!query || query.length < 3) return [];

        if (dadataConfig.apiKey) {
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Token ${dadataConfig.apiKey}`,
                },
                body: JSON.stringify({
                    query,
                    count: 8,
                    locations_geo: [
                        {
                            lat: DADATA_CENTER.lat,
                            lon: DADATA_CENTER.lon,
                            radius_meters: 60000,
                        },
                    ],
                }),
            }).catch(() => null);

            if (response?.ok) {
                const payload = await response.json().catch(() => null);
                if (requestId === lastSuggestionRequestId) {
                    return payload?.suggestions || [];
                }
            }
        }

        return testAddresses
            .filter((item) => normalizeText(item.label).includes(normalizeText(query)))
            .slice(0, 8)
            .map((item) => ({ value: item.label, data: { city_with_type: item.label } }));
    };

    const debouncedSuggest = (() => {
        let timer;
        return (value) => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                lastSuggestionRequestId += 1;
                const requestId = lastSuggestionRequestId;
                const suggestions = await fetchSuggestions(value.trim(), requestId);
                renderSuggestions(suggestions);
            }, 250);
        };
    })();

    const findDistancePriceRange = (distanceKm) => {
        const distance = Number(distanceKm);
        if (!Number.isFinite(distance)) return null;

        for (const range of distanceRanges) {
            const min = Number(range.min_km ?? range.minKm ?? range.min) || 0;
            const maxRaw = range.max_km ?? range.maxKm ?? range.max;
            const max = maxRaw === null || maxRaw === '' || typeof maxRaw === 'undefined' ? null : Number(maxRaw);
            if (distance < min) continue;
            if (max !== null && Number.isFinite(max) && distance > max) continue;

            const price = Number(range.price ?? 0);
            return {
                min,
                max,
                price: Number.isFinite(price) ? price : fallbackDeliveryPrice,
            };
        }

        return null;
    };

    const getRoadDistance = async (startCoords, endCoords) => {
        if (!orsApiKey) return null;

        const response = await fetch('https://api.openrouteservice.org/v2/directions/driving-car', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: orsApiKey,
            },
            body: JSON.stringify({ coordinates: [startCoords, endCoords] }),
        }).catch(() => null);

        if (!response?.ok) return null;
        const data = await response.json().catch(() => null);
        const meters = data?.routes?.[0]?.summary?.distance;
        if (!Number.isFinite(meters)) return null;

        return meters / 1000;
    };

    const geocodeWithDadata = async (query) => {
        const response = await fetch('/api/dadata/clean-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ query }),
        }).catch(() => null);

        if (!response?.ok) return null;
        const data = await response.json().catch(() => null);
        if (!Array.isArray(data) || !data[0]) return null;

        const row = data[0];
        const lat = Number(row.geo_lat || 0);
        const lon = Number(row.geo_lon || 0);
        if (!lat || !lon) return null;

        return { lat, lon };
    };

    const updateOutput = (distanceKm, price, priceRange) => {
        distanceNode.textContent = `${distanceKm.toFixed(2)} км`;
        priceNode.textContent = `${Math.round(price).toLocaleString('ru-RU')} ₽`;

        if (!priceRange) {
            rangeNode.textContent = 'Не найден, применён fallback';
            return;
        }

        rangeNode.textContent = priceRange.max === null
            ? `от ${priceRange.min.toFixed(2)} км`
            : `${priceRange.min.toFixed(2)}–${priceRange.max.toFixed(2)} км`;
    };

    const calculate = async () => {
        const query = (addressInput.value || '').trim();
        if (!query) {
            setStatus('Введите адрес для расчёта.', 'error');
            return;
        }

        if (deliveryPricingMode !== 'ors') {
            setStatus('Сейчас активирован расчёт по зонам. Километражный расчёт недоступен.', 'error');
            return;
        }

        hideSuggestions();
        setStatus('Проверяем адрес и считаем маршрут...');
        calcButton.disabled = true;

        try {
            const point = await geocodeWithDadata(query);
            if (!point) {
                throw new Error('Не удалось получить координаты адреса. Выберите адрес из подсказок и повторите расчёт.');
            }

            const distanceKm = await getRoadDistance([orsOrigin.lon, orsOrigin.lat], [point.lon, point.lat]);
            if (distanceKm === null) {
                throw new Error('Не удалось получить километраж маршрута. Попробуйте позже.');
            }

            const priceRange = findDistancePriceRange(distanceKm);
            const price = priceRange ? priceRange.price : fallbackDeliveryPrice;

            updateOutput(distanceKm, price, priceRange);
            setStatus('Расчёт готов: километраж сопоставлен с тарифным диапазоном.', 'success');
        } catch (error) {
            setStatus(error.message || 'Не удалось выполнить расчёт. Попробуйте позже.', 'error');
        } finally {
            calcButton.disabled = false;
        }
    };

    addressInput.addEventListener('input', () => {
        debouncedSuggest(addressInput.value || '');
    });

    addressInput.addEventListener('focus', () => {
        if ((addressInput.value || '').trim().length >= 3) {
            debouncedSuggest(addressInput.value || '');
        }
    });

    document.addEventListener('click', (event) => {
        if (!calculator.contains(event.target)) {
            hideSuggestions();
        }
    });

    calcButton.addEventListener('click', calculate);
    addressInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            calculate();
        }
    });
})();
</script>

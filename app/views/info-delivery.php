<?php
$pageMeta = array_merge($pageMeta ?? [], [
    'title' => $pageMeta['title'] ?? 'Оплата и доставка — Bunch flowers',
]);
?>

<section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Оплата и доставка</h1>
        <p class="text-sm text-slate-500">Введите адрес и сразу узнайте примерный километраж и стоимость доставки.</p>
    </header>

    <div
        class="space-y-4 rounded-2xl border border-rose-100 bg-rose-50/40 p-4"
        data-delivery-calculator
        data-shop-lat="56.047130"
        data-shop-lon="92.919190"
        data-base-price="390"
        data-price-per-km="35"
        data-free-radius-km="3"
    >
        <div class="space-y-1">
            <h2 class="text-base font-semibold text-slate-900">Калькулятор доставки</h2>
            <p class="text-xs text-slate-500">Стоимость рассчитывается автоматически по вашему адресу.</p>
        </div>
        <label class="space-y-2 text-sm font-medium text-slate-700">
            Адрес доставки
            <div class="flex flex-col gap-2 sm:flex-row">
                <input
                    type="text"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    placeholder="Красноярск, ул. Авиаторов, 1"
                    data-delivery-address
                >
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                    data-delivery-calc-btn
                >Рассчитать</button>
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
                <p class="text-xs text-slate-500">Статус</p>
                <p class="text-sm font-semibold text-slate-700" data-delivery-status>Введите адрес для расчёта</p>
            </div>
        </div>
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
                <li>расчёт стоимости на странице показывает ориентир, финальная сумма подтверждается менеджером;</li>
                <li>уточняем детали по телефону перед выездом курьера;</li>
                <li>бережная транспортировка в защитной упаковке.</li>
            </ul>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Как мы считаем стоимость</h2>
            <ul class="list-disc space-y-1 pl-5">
                <li>базовая стоимость доставки по городу — от 390 ₽;</li>
                <li>в радиусе 3 км от мастерской действует базовый тариф;</li>
                <li>дальше 3 км добавляется 35 ₽ за каждый следующий километр;</li>
                <li>для удалённых районов и срочной доставки стоимость может отличаться.</li>
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
    const statusNode = calculator.querySelector('[data-delivery-status]');

    const shopLat = Number(calculator.dataset.shopLat || 0);
    const shopLon = Number(calculator.dataset.shopLon || 0);
    const basePrice = Number(calculator.dataset.basePrice || 390);
    const pricePerKm = Number(calculator.dataset.pricePerKm || 35);
    const freeRadiusKm = Number(calculator.dataset.freeRadiusKm || 3);

    const toRadians = (degrees) => degrees * (Math.PI / 180);
    const haversineKm = (lat1, lon1, lat2, lon2) => {
        const earthRadius = 6371;
        const dLat = toRadians(lat2 - lat1);
        const dLon = toRadians(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLon / 2) ** 2;
        return 2 * earthRadius * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    };

    const updateOutput = (distanceKm, price) => {
        distanceNode.textContent = `${distanceKm.toFixed(1)} км`;
        priceNode.textContent = `${Math.round(price).toLocaleString('ru-RU')} ₽`;
    };

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

    const calculate = async () => {
        const query = (addressInput.value || '').trim();
        if (!query) {
            setStatus('Введите адрес для расчёта.', 'error');
            return;
        }

        setStatus('Проверяем адрес и считаем расстояние...');
        calcButton.disabled = true;

        try {
            const response = await fetch('/api-dadata-clean-address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.error || 'Не удалось получить координаты адреса.');
            }

            const targetLat = Number(data.geo_lat || 0);
            const targetLon = Number(data.geo_lon || 0);
            if (!targetLat || !targetLon) {
                throw new Error('Для адреса не найдены координаты. Уточните адрес и повторите расчёт.');
            }

            const distanceKm = haversineKm(shopLat, shopLon, targetLat, targetLon);
            const paidDistance = Math.max(0, distanceKm - freeRadiusKm);
            const totalPrice = basePrice + paidDistance * pricePerKm;

            updateOutput(distanceKm, totalPrice);
            setStatus('Расчёт готов. Это ориентировочная стоимость доставки.', 'success');
        } catch (error) {
            setStatus(error.message || 'Не удалось выполнить расчёт. Попробуйте позже.', 'error');
        } finally {
            calcButton.disabled = false;
        }
    };

    calcButton.addEventListener('click', calculate);
    addressInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            calculate();
        }
    });
})();
</script>

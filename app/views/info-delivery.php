<?php
$pageMeta = array_merge($pageMeta ?? [], [
    'title' => $pageMeta['title'] ?? 'Оплата и доставка — Bunch flowers',
]);
?>

<section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Оплата и доставка</h1>
        <p class="text-sm text-slate-500">Удобные способы оплаты и оперативная доставка по Красноярску.</p>
    </header>

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
                <li>уточняем детали по телефону перед выездом курьера;</li>
                <li>бережная транспортировка в защитной упаковке.</li>
            </ul>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Самовывоз</h2>
            <p>Забрать заказ можно по адресу: 9 мая 73. Предупредите нас заранее, чтобы мы подготовили букет к выдаче.</p>
        </div>
    </div>
</section>

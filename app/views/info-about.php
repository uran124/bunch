<?php
$pageMeta = array_merge($pageMeta ?? [], [
    'title' => $pageMeta['title'] ?? 'О нас — Bunch flowers',
]);
?>

<section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">О нас</h1>
        <p class="text-sm text-slate-500">Bunch flowers — сервис покупки свежих роз в Красноярске с прозрачными условиями и удобной доставкой.</p>
    </header>

    <div class="space-y-4 text-sm text-slate-700">
        <p>Мы работаем напрямую с проверенными поставщиками, чтобы каждая партия была свежей, а букеты — максимально стойкими.</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <h2 class="text-base font-semibold text-slate-900">Что мы делаем</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>подбираем розы под разные поводы и бюджеты;</li>
                    <li>помогаем оформить заказ за несколько минут;</li>
                    <li>контролируем качество перед вручением.</li>
                </ul>
            </div>
            <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <h2 class="text-base font-semibold text-slate-900">Наш подход</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>работаем без лишних переплат и скрытых условий;</li>
                    <li>бережно доставляем букеты в защитной упаковке;</li>
                    <li>держим связь до момента получения.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

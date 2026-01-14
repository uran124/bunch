<?php
$pageMeta = array_merge($pageMeta ?? [], [
    'title' => $pageMeta['title'] ?? 'Наши розы — Bunch flowers',
]);
?>

<section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Информация</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Наши розы</h1>
        <p class="text-sm text-slate-500">Мы выбираем розы с плотным бутоном, насыщенным цветом и стойким ароматом.</p>
    </header>

    <div class="space-y-4 text-sm text-slate-700">
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Свежие поставки</h2>
            <p>Поставки приходят несколько раз в неделю, поэтому вы получаете розы, которые простояли минимум времени после срезки.</p>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Разнообразие сортов</h2>
            <p>Классические красные, нежные пастельные и модные двуцветные — мы регулярно обновляем ассортимент.</p>
        </div>
        <div class="space-y-2">
            <h2 class="text-base font-semibold text-slate-900">Контроль качества</h2>
            <p>Перед выдачей мы проверяем каждую розу и заменяем цветы, если они не соответствуют стандартам.</p>
        </div>
    </div>
</section>

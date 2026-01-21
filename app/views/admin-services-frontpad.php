<?php /** @var array $pageMeta */ ?>
<?php $pageMeta = $pageMeta ?? []; ?>
<?php $settings = $settings ?? []; ?>
<?php $products = $products ?? []; ?>

<section class="flex flex-col gap-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Настройка сервисов</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($pageMeta['h1'] ?? 'FrontPad', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="max-w-2xl text-sm text-slate-500">
                <?php echo htmlspecialchars($pageMeta['description'] ?? 'Укажите секрет API и проверьте артикулы товаров для синхронизации.', ENT_QUOTES, 'UTF-8'); ?>
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
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                <span class="material-symbols-rounded text-base">vpn_key</span>
                Доступы хранятся в базе
            </span>
        </div>
    </header>

    <?php if (($status ?? '') === 'saved') : ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            Настройки FrontPad сохранены.
        </div>
    <?php elseif (($status ?? '') === 'article_saved') : ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            Артикул обновлён.
        </div>
    <?php endif; ?>

    <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
        <form method="post" action="/admin-services-frontpad" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="action" value="settings">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">FrontPad API</p>
                <h2 class="text-xl font-semibold text-slate-900">Настройки и ключи</h2>
                <p class="text-sm text-slate-500">Секретный код из раздела «Общие» и базовый URL для методов API.</p>
            </div>

            <div class="grid gap-3 text-sm">
                <label class="space-y-1">
                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Secret</span>
                    <input
                        type="text"
                        name="frontpad_secret"
                        value="<?php echo htmlspecialchars($settings['secret'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Ваш секрет API"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                    >
                </label>
                <label class="space-y-1">
                    <span class="text-xs uppercase tracking-[0.14em] text-slate-500">API URL</span>
                    <input
                        type="text"
                        name="frontpad_api_url"
                        value="<?php echo htmlspecialchars($settings['apiUrl'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="https://app.frontpad.ru/api/index.php"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                    >
                </label>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-800">Быстрые ссылки</p>
                <ul class="mt-2 space-y-1 text-xs text-slate-500">
                    <li>new_order → <?php echo htmlspecialchars(($settings['apiUrl'] ?? 'https://app.frontpad.ru/api/index.php') . '?new_order', ENT_QUOTES, 'UTF-8'); ?></li>
                    <li>get_products → <?php echo htmlspecialchars(($settings['apiUrl'] ?? 'https://app.frontpad.ru/api/index.php') . '?get_products', ENT_QUOTES, 'UTF-8'); ?></li>
                    <li>get_client → <?php echo htmlspecialchars(($settings['apiUrl'] ?? 'https://app.frontpad.ru/api/index.php') . '?get_client', ENT_QUOTES, 'UTF-8'); ?></li>
                    <li>get_certificate → <?php echo htmlspecialchars(($settings['apiUrl'] ?? 'https://app.frontpad.ru/api/index.php') . '?get_certificate', ENT_QUOTES, 'UTF-8'); ?></li>
                    <li>get_stops → <?php echo htmlspecialchars(($settings['apiUrl'] ?? 'https://app.frontpad.ru/api/index.php') . '?get_stops', ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <p class="mt-3 text-xs text-slate-500">Лимиты: не более 30 запросов в минуту и 2 в секунду. Для get_products — 1 запрос в час.</p>
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:-translate-y-0.5 hover:shadow"
            >
                <span class="material-symbols-rounded text-base">save</span>
                Сохранить настройки
            </button>
        </form>

        <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Подсказка</p>
                <h3 class="text-lg font-semibold text-slate-900">Подготовка к подключению</h3>
                <ol class="mt-2 list-decimal space-y-1 pl-4 text-sm text-slate-500">
                    <li>Включите API в разделе «Общие» настроек FrontPad.</li>
                    <li>Сохраните секретный код (secret) и добавьте его в форму.</li>
                    <li>Назначьте товарам уникальные цифровые артикулы в карточках.</li>
                </ol>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4 text-sm text-amber-700">
                <p class="font-semibold">Важно</p>
                <p class="mt-2 text-xs text-amber-700">FrontPad запрещает автоматический перебор данных. Отправляйте запросы только после действий клиента.</p>
            </div>
        </aside>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Товары</p>
                <h2 class="text-xl font-semibold text-slate-900">Артикулы для FrontPad</h2>
                <p class="text-sm text-slate-500">Обновляйте артикулы товаров, которые будут продаваться через API.</p>
            </div>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-100">
            <div class="grid grid-cols-[90px_1.6fr_1.2fr_70px] gap-4 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                <span>ID товара</span>
                <span>Наименование</span>
                <span>Артикул</span>
                <span class="text-right">Обновить</span>
            </div>
            <div class="divide-y divide-slate-100">
                <?php foreach ($products as $product): ?>
                    <form method="post" action="/admin-services-frontpad" class="grid grid-cols-[90px_1.6fr_1.2fr_70px] items-center gap-4 px-5 py-4 text-sm">
                        <input type="hidden" name="action" value="update_article">
                        <input type="hidden" name="product_id" value="<?php echo (int) ($product['id'] ?? 0); ?>">
                        <div class="font-semibold text-slate-900">
                            <?php echo (int) ($product['id'] ?? 0); ?>
                        </div>
                        <div class="text-slate-700">
                            <?php echo htmlspecialchars($product['name'] ?? 'Без названия', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div>
                            <input
                                type="text"
                                name="product_article"
                                value="<?php echo htmlspecialchars($product['article'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Артикул"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/30"
                            >
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition hover:-translate-y-0.5 hover:border-rose-200 hover:text-rose-600"
                                aria-label="Обновить артикул"
                            >
                                <span class="material-symbols-rounded text-base">refresh</span>
                            </button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</section>

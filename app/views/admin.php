<section class="page-heading">
    <div>
        <p class="overline">Администрирование</p>
        <h1>Панель управления</h1>
        <p class="subtitle">Быстрый доступ ко всем ключевым разделам сайта и операционным настройкам.</p>
    </div>
    <div class="actions">
        <button class="btn primary">Создать акцию</button>
        <button class="btn ghost">Добавить товар</button>
    </div>
</section>

<section class="dashboard-grid">
    <article class="dashboard-card highlight">
        <div class="card-header">
            <div>
                <p class="overline">Мониторинг</p>
                <h2>Операционный обзор</h2>
            </div>
            <span class="status-pill success">Стабильно</span>
        </div>
        <ul class="metric-list">
            <li><span>Активных заказов</span><strong>18</strong></li>
            <li><span>Подписок в работе</span><strong>42</strong></li>
            <li><span>Остатков на складе</span><strong>3 150 шт</strong></li>
        </ul>
        <p class="card-note">Проверьте, достаточно ли остатков под утренние доставки и запланированные акции.</p>
    </article>

    <?php foreach ($sections as $section): ?>
        <article class="dashboard-card">
            <div class="card-header">
                <p class="overline">Раздел</p>
                <h3><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            </div>
            <ul class="link-list">
                <?php foreach ($section['items'] as $item): ?>
                    <li>
                        <div class="link-title"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="link-desc"><?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <button class="btn tiny">Открыть</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>

    <article class="dashboard-card full">
        <div class="card-header">
            <p class="overline">Быстрые действия</p>
            <h3>Ручное управление</h3>
        </div>
        <div class="quick-actions">
            <button class="btn secondary">Создать заказ</button>
            <button class="btn secondary">Начислить бонусы</button>
            <button class="btn secondary">Запланировать рассылку</button>
            <button class="btn secondary">Остановить продажи</button>
        </div>
        <p class="card-note">Все действия фиксируются в логах администрирования. Проверьте доступы перед изменением настроек.</p>
    </article>
</section>

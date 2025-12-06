<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bunch flowers — панель</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="app-header">
        <div class="app-header__brand">Bunch flowers</div>
        <div class="app-header__context">Панель управления</div>
    </header>

    <main class="app-content">
        <?php echo $content; ?>
    </main>

    <footer class="app-footer">
        <span>© <?php echo date('Y'); ?> Bunch flowers</span>
        <span class="footer-status">Красноярск · Asia/Krasnoyarsk</span>
    </footer>
</body>
</html>

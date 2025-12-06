<?php
// config.php
$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    require $localConfigPath;
}
// База данных
if (!defined('DB_HOST')) {
    define('DB_HOST', '188.127.239.143');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'bunch');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'bunch');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'tM1pE1iN5g');
}
// Общие настройки приложения
if (!defined('APP_ENV')) {
    define('APP_ENV', 'prod'); // prod | dev
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}
// Telegram бот
if (!defined('TG_BOT_TOKEN')) {
    define('TG_BOT_TOKEN', getenv('TG_BOT_TOKEN') ?: '');
}
if (!defined('TG_BOT_USERNAME')) {
    define('TG_BOT_USERNAME', getenv('TG_BOT_USERNAME') ?: '');
}
if (!defined('TG_WEBHOOK_SECRET')) {
    define('TG_WEBHOOK_SECRET', getenv('TG_WEBHOOK_SECRET') ?: '');
}
// Таймзона
date_default_timezone_set('Asia/Krasnoyarsk');

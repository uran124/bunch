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
// Таймзона
date_default_timezone_set('Asia/Krasnoyarsk');

// DaData credentials
if (!defined('DADATA_API_KEY')) {
    define('DADATA_API_KEY', 'd32d23e2087d406928a38947855d3179f03dcff2');
}

if (!defined('DADATA_SECRET_KEY')) {
    define('DADATA_SECRET_KEY', 'b11fccf3100a8666f0cb5382071f2e935c449df9');
}

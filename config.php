<?php
// config.php
$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    require $localConfigPath;
}

$readEnv = static function (string $key): ?string {
    $value = getenv($key);
    if ($value === false) {
        return null;
    }

    return $value;
};

$defineIfMissing = static function (string $key, mixed $value): void {
    if (!defined($key)) {
        define($key, $value);
    }
};

// База данных
$defineIfMissing('DB_HOST', $readEnv('DB_HOST') ?? '188.127.239.143');
$defineIfMissing('DB_NAME', $readEnv('DB_NAME') ?? 'bunch');
$defineIfMissing('DB_USER', $readEnv('DB_USER') ?? 'bunch');
$defineIfMissing('DB_PASS', $readEnv('DB_PASS') ?? 'tM1pE1iN5g');

// Общие настройки приложения
$defineIfMissing('APP_ENV', $readEnv('APP_ENV') ?? 'prod'); // prod | dev
$defineIfMissing('APP_DEBUG', filter_var($readEnv('APP_DEBUG') ?? false, FILTER_VALIDATE_BOOL));
$defineIfMissing('APP_TIMEZONE', $readEnv('APP_TIMEZONE') ?? 'Asia/Krasnoyarsk');

// Таймзона
date_default_timezone_set(APP_TIMEZONE);

// DaData credentials
$defineIfMissing('DADATA_API_KEY', $readEnv('DADATA_API_KEY') ?? 'd32d23e2087d406928a38947855d3179f03dcff2');
$defineIfMissing('DADATA_SECRET_KEY', $readEnv('DADATA_SECRET_KEY') ?? 'b11fccf3100a8666f0cb5382071f2e935c449df9');

// Internal Telegram relay API (VPS -> old hosting)
$defineIfMissing('TG_INTERNAL_API_KEY_ID', $readEnv('TG_INTERNAL_API_KEY_ID') ?? 'bunch-bot-v1');
$defineIfMissing('TG_INTERNAL_API_SECRET', $readEnv('TG_INTERNAL_API_SECRET') ?? '');
$defineIfMissing('TG_INTERNAL_API_MAX_SKEW_SECONDS', (int) ($readEnv('TG_INTERNAL_API_MAX_SKEW_SECONDS') ?? 300));

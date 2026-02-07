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
$defineIfMissing('DB_HOST', $readEnv('DB_HOST') ?? 'localhost');
$defineIfMissing('DB_NAME', $readEnv('DB_NAME') ?? 'bunch');
$defineIfMissing('DB_USER', $readEnv('DB_USER') ?? 'bunch');
$defineIfMissing('DB_PASS', $readEnv('DB_PASS') ?? '');

// Общие настройки приложения
$defineIfMissing('APP_ENV', $readEnv('APP_ENV') ?? 'prod'); // prod | dev
$defineIfMissing('APP_DEBUG', filter_var($readEnv('APP_DEBUG') ?? false, FILTER_VALIDATE_BOOL));
$defineIfMissing('APP_TIMEZONE', $readEnv('APP_TIMEZONE') ?? 'Asia/Krasnoyarsk');

// Таймзона
date_default_timezone_set(APP_TIMEZONE);

// DaData credentials
$defineIfMissing('DADATA_API_KEY', $readEnv('DADATA_API_KEY') ?? '');
$defineIfMissing('DADATA_SECRET_KEY', $readEnv('DADATA_SECRET_KEY') ?? '');

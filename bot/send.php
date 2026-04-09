<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

function sendJson(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readRelayHeader(string $name): ?string
{
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    $value = $_SERVER[$key] ?? null;
    return is_string($value) ? trim($value) : null;
}

function readConfigString(string $key): string
{
    if (defined($key)) {
        return trim((string) constant($key));
    }

    if (function_exists('cfg')) {
        $value = cfg($key, '');
        if (is_string($value)) {
            return trim($value);
        }
    }

    $value = getenv($key);
    if ($value === false) {
        return '';
    }

    return trim((string) $value);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    sendJson(405, ['ok' => false, 'error' => 'Method not allowed']);
}

$relayKey = readRelayHeader('X-Relay-Key') ?? '';
$expectedRelayKey = readConfigString('TG_OUTBOUND_RELAY_KEY');
if ($expectedRelayKey === '' || !hash_equals($expectedRelayKey, $relayKey)) {
    sendJson(403, ['ok' => false, 'error' => 'Forbidden']);
}

// Для relay endpoint в первую очередь берём токен из env/config,
// чтобы отправка работала даже если БД недоступна на VPS.
$botToken = readConfigString('TG_BOT_TOKEN');
if ($botToken === '') {
    try {
        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $botToken = trim((string) $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? ''));
    } catch (Throwable $e) {
        $botToken = '';
    }
}
if ($botToken === '') {
    sendJson(500, ['ok' => false, 'error' => 'Missing TG_BOT_TOKEN']);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode(is_string($rawBody) ? $rawBody : '', true);
if (!is_array($payload)) {
    sendJson(422, ['ok' => false, 'error' => 'Invalid JSON body']);
}

$method = trim((string) ($payload['method'] ?? ''));
$params = is_array($payload['params'] ?? null) ? $payload['params'] : [];
if ($method === '') {
    sendJson(422, ['ok' => false, 'error' => 'Method is required']);
}

$url = 'https://api.telegram.org/bot' . $botToken . '/' . $method;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
    CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $errno !== 0) {
    sendJson(502, ['ok' => false, 'error' => 'Telegram network error', 'errno' => $errno, 'details' => $error]);
}

$decoded = json_decode((string) $response, true);
if (!is_array($decoded)) {
    sendJson(502, ['ok' => false, 'error' => 'Invalid Telegram response', 'http_status' => $status, 'raw' => $response]);
}

sendJson($status > 0 ? $status : 200, $decoded);

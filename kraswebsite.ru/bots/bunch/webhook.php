<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function logLine(string $message, array $ctx = []): void
{
    $file = cfg('LOG_FILE', '/tmp/bunch_vps_bot.log');
    $line = date('c') . ' ' . $message;
    if ($ctx) {
        $line .= ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE);
    }
    @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function getHeader(string $name): ?string
{
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    $value = $_SERVER[$serverKey] ?? null;
    return is_string($value) ? $value : null;
}

function getRequestIp(): ?string
{
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if (is_string($xff) && $xff !== '') {
        $parts = explode(',', $xff);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? null;
}

function httpPostJson(string $url, array $headers, string $jsonBody, int $connectTimeout, int $timeout): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $jsonBody,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);

    $respBody = curl_exec($ch);
    $errNo = curl_errno($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => $errNo === 0,
        'status' => $status,
        'body' => is_string($respBody) ? $respBody : '',
        'error' => $err,
        'errno' => $errNo,
    ];
}

function sendTelegramMessage(string $token, int $chatId, string $text, array $options = []): array
{
    $payload = array_merge([
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
    ], $options);

    // reply_markup может прийти строкой JSON — преобразуем
    if (isset($payload['reply_markup']) && is_string($payload['reply_markup'])) {
        $decoded = json_decode($payload['reply_markup'], true);
        if (is_array($decoded)) {
            $payload['reply_markup'] = $decoded;
        }
    }

    $url = TELEGRAM_API_BASE . '/bot' . $token . '/sendMessage';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);

    $respBody = curl_exec($ch);
    $errNo = curl_errno($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode(is_string($respBody) ? $respBody : '', true);

    return [
        'ok' => $errNo === 0 && is_array($decoded) && !empty($decoded['ok']),
        'status' => $status,
        'body' => is_string($respBody) ? $respBody : '',
        'decoded' => is_array($decoded) ? $decoded : null,
        'error' => $err,
        'errno' => $errNo,
    ];
}

// 1) Метод
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonResponse(405, ['ok' => false, 'error' => 'Method not allowed']);
}

// 2) Проверка секрета Telegram webhook
$expectedWebhookSecret = (string) cfg('TG_WEBHOOK_SECRET', '');
$providedWebhookSecret = (string) (getHeader('X-Telegram-Bot-Api-Secret-Token') ?? '');

if ($expectedWebhookSecret === '' || !hash_equals($expectedWebhookSecret, $providedWebhookSecret)) {
    logLine('forbidden_webhook_secret', [
        'ip' => getRequestIp(),
        'provided' => $providedWebhookSecret !== '' ? 'present' : 'empty',
    ]);
    jsonResponse(403, ['ok' => false, 'error' => 'Forbidden']);
}

// 3) Читаем raw update
$rawUpdate = file_get_contents('php://input');
if (!is_string($rawUpdate) || $rawUpdate === '') {
    jsonResponse(422, ['ok' => false, 'error' => 'Empty body']);
}

$update = json_decode($rawUpdate, true);
if (!is_array($update)) {
    jsonResponse(422, ['ok' => false, 'error' => 'Invalid JSON']);
}

// 4) Собираем payload в old site
$payload = [
    'meta' => [
        'bot_id' => 'bunch-main-bot',
        'received_at' => gmdate('c'),
        'source_ip' => getRequestIp(),
        'webhook_secret_valid' => true,
    ],
    'update' => $update,
];

$rawPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
if (!is_string($rawPayload)) {
    jsonResponse(500, ['ok' => false, 'error' => 'Payload encode error']);
}

// 5) Подпись для old site
$keyId = (string) cfg('TG_INTERNAL_API_KEY_ID', 'bunch-bot-v1');
$secret = (string) cfg('TG_INTERNAL_API_SECRET', '');
$oldSiteUrl = (string) cfg('OLD_SITE_URL', '');

if ($secret === '' || $oldSiteUrl === '') {
    jsonResponse(500, ['ok' => false, 'error' => 'Missing relay config']);
}

$timestamp = (string) time();
$nonce = function_exists('uuid_create')
    ? uuid_create(UUID_TYPE_RANDOM)
    : sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        random_int(0, 0xffff), random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0x0fff) | 0x4000,
        random_int(0, 0x3fff) | 0x8000,
        random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
    );

$signBase = $timestamp . '.' . $nonce . '.' . $rawPayload;
$hmac = hash_hmac('sha256', $signBase, $secret);

$headers = [
    'Content-Type: application/json; charset=utf-8',
    'X-Bot-Key-Id: ' . $keyId,
    'X-Bot-Timestamp: ' . $timestamp,
    'X-Bot-Nonce: ' . $nonce,
    'X-Bot-Signature: sha256=' . $hmac,
];

// 6) Отправляем в old site
$connectTimeout = (int) cfg('HTTP_CONNECT_TIMEOUT', '5');
$timeout = (int) cfg('HTTP_TIMEOUT', '12');

$relay = httpPostJson($oldSiteUrl, $headers, $rawPayload, $connectTimeout, $timeout);

if (!$relay['ok']) {
    logLine('relay_network_error', ['errno' => $relay['errno'], 'error' => $relay['error']]);
    jsonResponse(502, ['ok' => false, 'error' => 'Relay network error']);
}

$resp = json_decode($relay['body'], true);
if (!is_array($resp)) {
    logLine('relay_invalid_json', ['status' => $relay['status'], 'body' => $relay['body']]);
    jsonResponse(502, ['ok' => false, 'error' => 'Relay invalid response']);
}

$decision = (string) ($resp['decision'] ?? '');
$actions = is_array($resp['actions'] ?? null) ? $resp['actions'] : [];

logLine('relay_response', [
    'status' => $relay['status'],
    'decision' => $decision,
    'actions_count' => count($actions),
    'idempotency_key' => $resp['idempotency_key'] ?? null,
]);

// 7) Исполняем actions только для handled
if ($relay['status'] !== 200) {
    // 401/408/409/422 обычно не ретраим автоматически
    jsonResponse(200, ['ok' => true, 'relay_status' => $relay['status'], 'decision' => $decision]);
}

if ($decision !== 'handled') {
    jsonResponse(200, ['ok' => true, 'decision' => $decision, 'actions_executed' => 0]);
}

$botToken = (string) cfg('TG_BOT_TOKEN', '');
if ($botToken === '') {
    logLine('missing_tg_bot_token');
    jsonResponse(500, ['ok' => false, 'error' => 'Missing TG_BOT_TOKEN']);
}

$executed = 0;
$failed = 0;

foreach ($actions as $idx => $action) {
    if (!is_array($action)) {
        $failed++;
        continue;
    }

    $type = (string) ($action['type'] ?? '');
    if ($type !== 'sendMessage') {
        // пока поддерживаем только sendMessage
        continue;
    }

    $chatId = isset($action['chat_id']) ? (int) $action['chat_id'] : 0;
    $text = (string) ($action['text'] ?? '');
    $options = is_array($action['options'] ?? null) ? $action['options'] : [];

    if ($chatId === 0 || $text === '') {
        $failed++;
        logLine('invalid_action_payload', ['index' => $idx, 'action' => $action]);
        continue;
    }

    $send = sendTelegramMessage($botToken, $chatId, $text, $options);
    if ($send['ok']) {
        $executed++;
    } else {
        $failed++;
        logLine('telegram_send_failed', [
            'index' => $idx,
            'chat_id' => $chatId,
            'status' => $send['status'],
            'error' => $send['error'],
            'body' => $send['body'],
        ]);
    }
}

jsonResponse(200, [
    'ok' => true,
    'decision' => $decision,
    'actions_total' => count($actions),
    'actions_executed' => $executed,
    'actions_failed' => $failed,
]);

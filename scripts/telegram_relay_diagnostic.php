<?php
declare(strict_types=1);

/**
 * Telegram relay diagnostic script.
 * IMPORTANT: run from CLI on VPS. Do not execute via browser on shared hosting.
 *
 * Usage example:
 * php scripts/telegram_relay_diagnostic.php \
 *   --old-site-url="https://bunchflowers.ru/api/internal/telegram/handle-update" \
 *   --key-id="bunch-bot-v1" \
 *   --secret="replace_me" \
 *   --chat-id=790000001 \
 *   --from-id=790000001 \
 *   --username="ivan" \
 *   --text="/start" \
 *   --bot-token="123456:ABC..." \
 *   --execute-actions=1
 */

function usage(): void
{
    $msg = <<<TXT
Usage:
  php scripts/telegram_relay_diagnostic.php --old-site-url=URL --key-id=ID --secret=SECRET --chat-id=INT [options]

Required:
  --old-site-url       Full URL to old-site endpoint (handle-update)
  --key-id             X-Bot-Key-Id (e.g. bunch-bot-v1)
  --secret             Shared HMAC secret
  --chat-id            Telegram chat ID

Optional:
  --from-id            Telegram user id (default: chat-id)
  --username           Telegram username (default: diagnostic_user)
  --first-name         Sender first name (default: Relay)
  --last-name          Sender last name (default: Diagnostic)
  --text               Message text (default: /start)
  --update-id          update_id override (default: random)
  --bot-token          Bot token for executing sendMessage actions
  --execute-actions    1/0 execute actions in Telegram (default: 0)
  --connect-timeout    cURL connect timeout sec (default: 5)
  --timeout            cURL total timeout sec (default: 12)
TXT;

    $stderr = @fopen('php://stderr', 'wb');
    if ($stderr !== false) {
        fwrite($stderr, $msg . PHP_EOL);
        fclose($stderr);
        return;
    }

    echo $msg . PHP_EOL;
}

function asInt(mixed $value, int $default = 0): int
{
    if (is_int($value)) {
        return $value;
    }

    if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
        return (int) $value;
    }

    return $default;
}

function asString(mixed $value, string $default = ''): string
{
    return is_string($value) ? $value : $default;
}

function hmacSignature(string $secret, string $timestamp, string $nonce, string $rawBody): string
{
    return hash_hmac('sha256', $timestamp . '.' . $nonce . '.' . $rawBody, $secret);
}

function isLikelyTelegramBotToken(string $token): bool
{
    return preg_match('/^\d{6,}:[A-Za-z0-9_-]{20,}$/', $token) === 1;
}

function requestJson(string $url, array $headers, string $rawBody, int $connectTimeout, int $timeout): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $rawBody,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => $errno === 0,
        'status' => $status,
        'body' => is_string($body) ? $body : '',
        'errno' => $errno,
        'error' => $error,
    ];
}

function sendTelegramAction(string $botToken, array $action, int $connectTimeout, int $timeout): array
{
    $chatId = asInt($action['chat_id'] ?? null);
    $text = asString($action['text'] ?? '');
    $options = is_array($action['options'] ?? null) ? $action['options'] : [];

    $payload = array_merge([
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
    ], $options);

    if (isset($payload['reply_markup']) && is_string($payload['reply_markup'])) {
        $decodedMarkup = json_decode($payload['reply_markup'], true);
        if (is_array($decodedMarkup)) {
            $payload['reply_markup'] = $decodedMarkup;
        }
    }

    $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';
    return requestJson(
        $url,
        ['Content-Type: application/json; charset=utf-8'],
        (string) json_encode($payload, JSON_UNESCAPED_UNICODE),
        $connectTimeout,
        $timeout
    );
}

function telegramHintFromResponse(array $decoded): ?string
{
    $description = asString($decoded['description'] ?? '');
    if ($description === '') {
        return null;
    }

    $descriptionLower = mb_strtolower($description, 'UTF-8');
    if (str_contains($descriptionLower, 'chat not found')) {
        return 'Hint: open Telegram app and send /start to this bot from that exact account/chat before testing.';
    }

    if (str_contains($descriptionLower, 'bot was blocked')) {
        return 'Hint: this account blocked the bot. Unblock the bot and send /start again.';
    }

    if (str_contains($descriptionLower, 'user is deactivated')) {
        return 'Hint: target Telegram user/chat is unavailable.';
    }

    return null;
}

$opts = getopt('', [
    'old-site-url:',
    'key-id:',
    'secret:',
    'chat-id:',
    'from-id::',
    'username::',
    'first-name::',
    'last-name::',
    'text::',
    'update-id::',
    'bot-token::',
    'execute-actions::',
    'connect-timeout::',
    'timeout::',
]);

$sapi = PHP_SAPI;
if ($sapi !== 'cli') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "This script is CLI-only.\n";
    echo "Run it on VPS server shell:\n";
    echo "php scripts/telegram_relay_diagnostic.php --old-site-url=... --key-id=... --secret=... --chat-id=...\n";
    exit(1);
}

$oldSiteUrl = asString($opts['old-site-url'] ?? '');
$keyId = asString($opts['key-id'] ?? '');
$secret = asString($opts['secret'] ?? '');
$chatId = asInt($opts['chat-id'] ?? null);

if ($oldSiteUrl === '' || $keyId === '' || $secret === '' || $chatId === 0) {
    usage();
    exit(2);
}

$fromId = asInt($opts['from-id'] ?? null, $chatId);
$username = asString($opts['username'] ?? 'diagnostic_user');
$firstName = asString($opts['first-name'] ?? 'Relay');
$lastName = asString($opts['last-name'] ?? 'Diagnostic');
$text = asString($opts['text'] ?? '/start');
$updateId = asInt($opts['update-id'] ?? null, random_int(100000000, 999999999));
$botToken = asString($opts['bot-token'] ?? '');
$executeActions = asInt($opts['execute-actions'] ?? '0') === 1;
$connectTimeout = asInt($opts['connect-timeout'] ?? '5', 5);
$timeout = asInt($opts['timeout'] ?? '12', 12);

$payload = [
    'meta' => [
        'bot_id' => 'bunch-main-bot',
        'received_at' => gmdate('c'),
        'source_ip' => 'diagnostic-script',
        'webhook_secret_valid' => true,
    ],
    'update' => [
        'update_id' => $updateId,
        'message' => [
            'message_id' => random_int(100, 9999),
            'date' => time(),
            'chat' => ['id' => $chatId, 'type' => 'private'],
            'from' => [
                'id' => $fromId,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            'text' => $text,
            'message_thread_id' => null,
            'reply_to_message' => null,
        ],
    ],
];

$rawPayload = (string) json_encode($payload, JSON_UNESCAPED_UNICODE);
$timestamp = (string) time();
$nonce = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    random_int(0, 0xffff),
    random_int(0, 0xffff),
    random_int(0, 0xffff),
    random_int(0, 0x0fff) | 0x4000,
    random_int(0, 0x3fff) | 0x8000,
    random_int(0, 0xffff),
    random_int(0, 0xffff),
    random_int(0, 0xffff)
);

$signature = hmacSignature($secret, $timestamp, $nonce, $rawPayload);
$headers = [
    'Content-Type: application/json; charset=utf-8',
    'X-Bot-Key-Id: ' . $keyId,
    'X-Bot-Timestamp: ' . $timestamp,
    'X-Bot-Nonce: ' . $nonce,
    'X-Bot-Signature: sha256=' . $signature,
];

$relayResp = requestJson($oldSiteUrl, $headers, $rawPayload, $connectTimeout, $timeout);
$relayJson = json_decode($relayResp['body'], true);

echo "=== Relay request ===" . PHP_EOL;
echo "URL: {$oldSiteUrl}" . PHP_EOL;
echo "update_id: {$updateId}" . PHP_EOL;
echo "chat_id: {$chatId}" . PHP_EOL;
echo "text: {$text}" . PHP_EOL;
echo "timestamp: {$timestamp}" . PHP_EOL;
echo "nonce: {$nonce}" . PHP_EOL;
echo PHP_EOL;

echo "=== Relay response ===" . PHP_EOL;
echo "http_status: {$relayResp['status']}" . PHP_EOL;
echo "network_ok: " . ($relayResp['ok'] ? 'true' : 'false') . PHP_EOL;
if (!$relayResp['ok']) {
    echo "curl_errno: {$relayResp['errno']}" . PHP_EOL;
    echo "curl_error: {$relayResp['error']}" . PHP_EOL;
}
echo "body: {$relayResp['body']}" . PHP_EOL;
echo PHP_EOL;

if (!is_array($relayJson)) {
    echo "Relay body is not JSON. Stop." . PHP_EOL;
    exit(1);
}

$decision = asString($relayJson['decision'] ?? '');
$actions = is_array($relayJson['actions'] ?? null) ? $relayJson['actions'] : [];
echo "decision: {$decision}" . PHP_EOL;
echo "actions_count: " . count($actions) . PHP_EOL;
echo PHP_EOL;

if (!$executeActions) {
    echo "Actions execution skipped (--execute-actions=0)." . PHP_EOL;
    exit(0);
}

if ($botToken === '') {
    echo "Missing --bot-token while --execute-actions=1." . PHP_EOL;
    exit(2);
}

if (!isLikelyTelegramBotToken($botToken)) {
    echo "Invalid bot token format. Expected something like 123456789:AA... (without quotes/placeholders)." . PHP_EOL;
    exit(2);
}

echo "=== Execute actions in Telegram ===" . PHP_EOL;
foreach ($actions as $index => $action) {
    if (!is_array($action)) {
        echo "[{$index}] invalid action format" . PHP_EOL;
        continue;
    }

    $type = asString($action['type'] ?? '');
    if ($type !== 'sendMessage') {
        echo "[{$index}] skip unsupported action type: {$type}" . PHP_EOL;
        continue;
    }

    $sendResp = sendTelegramAction($botToken, $action, $connectTimeout, $timeout);
    $sendJson = json_decode($sendResp['body'], true);
    $sendOk = is_array($sendJson) && !empty($sendJson['ok']);

    echo "[{$index}] sendMessage status={$sendResp['status']} ok=" . ($sendOk ? 'true' : 'false') . PHP_EOL;
    if (!$sendResp['ok']) {
        echo "[{$index}] curl_errno={$sendResp['errno']} curl_error={$sendResp['error']}" . PHP_EOL;
    }
    echo "[{$index}] body={$sendResp['body']}" . PHP_EOL;

    if (is_array($sendJson)) {
        $hint = telegramHintFromResponse($sendJson);
        if ($hint !== null) {
            echo "[{$index}] {$hint}" . PHP_EOL;
        }
    }
}

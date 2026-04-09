<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$token = (string) cfg('TG_BOT_TOKEN', '');
if ($token === '') {
    http_response_code(500);
    echo "Missing TG_BOT_TOKEN\n";
    exit;
}

$url = TELEGRAM_API_BASE . '/bot' . $token . '/getWebhookInfo';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: {$status}\n";
if ($error !== '') {
    echo "cURL error: {$error}\n";
}
echo (string) $response . "\n";

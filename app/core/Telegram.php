<?php
// app/core/Telegram.php

class Telegram
{
    private string $apiUrl;
    private string $relayUrl;
    private string $relayKey;

    public function __construct(string $token)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token . '/';
        $this->relayUrl = defined('TG_OUTBOUND_RELAY_URL') ? (string) TG_OUTBOUND_RELAY_URL : '';
        $this->relayKey = defined('TG_OUTBOUND_RELAY_KEY') ? (string) TG_OUTBOUND_RELAY_KEY : '';
    }

    public function sendMessage(int $chatId, string $text, array $options = []): ?array
    {
        $skipLog = (bool) ($options['skip_log'] ?? false);
        unset($options['skip_log']);
        $rawText = $text;
        $text = $this->formatText($text);
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options);
        $payload['parse_mode'] = 'HTML';

        $response = $this->request('sendMessage', $payload);

        if (!$skipLog && $chatId > 0 && class_exists('NotificationLog')) {
            $logger = new NotificationLog();
            $logger->recordForChatId($chatId, $rawText, ['source' => 'telegram']);
        }

        return $response;
    }

    private function formatText(string $text): string
    {
        if (preg_match('/<[^>]+>/', $text)) {
            return $text;
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function request(string $method, array $params): ?array
    {
        if ($this->relayUrl !== '' && $this->relayKey !== '') {
            return $this->requestViaRelay($method, $params);
        }

        $ch = curl_init($this->apiUrl . $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            (new Logger('telegram_errors.log'))->logRaw(date('c') . ' ' . $error);
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function requestViaRelay(string $method, array $params): ?array
    {
        $payload = json_encode([
            'method' => $method,
            'params' => $params,
        ], JSON_UNESCAPED_UNICODE);

        if (!is_string($payload)) {
            return null;
        }

        $ch = curl_init($this->relayUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'X-Relay-Key: ' . $this->relayKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            (new Logger('telegram_errors.log'))->logRaw(date('c') . ' relay ' . $error);
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}

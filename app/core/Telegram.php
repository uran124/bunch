<?php
// app/core/Telegram.php

class Telegram
{
    private string $apiUrl;

    public function __construct(string $token)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token . '/';
    }

    public function sendMessage(int $chatId, string $text, array $options = []): void
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options);

        $this->request('sendMessage', $payload);
    }

    private function request(string $method, array $params): void
    {
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
        }

        curl_close($ch);
    }
}

<?php

// app/controllers/ApiController.php

class ApiController extends Controller
{
    public function cleanDadataAddress(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $address = trim((string) ($payload['query'] ?? ''));

        if ($address === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Пустой адрес для нормализации']);
            return;
        }

        if (!DADATA_API_KEY || !DADATA_SECRET_KEY) {
            http_response_code(500);
            echo json_encode(['error' => 'Ключи DaData не настроены']);
            return;
        }

        $ch = curl_init('https://cleaner.dadata.ru/api/v1/clean/address');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . DADATA_API_KEY,
            'X-Secret: ' . DADATA_SECRET_KEY,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([$address], JSON_UNESCAPED_UNICODE));

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 0;
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            http_response_code(502);
            echo json_encode(['error' => 'Ошибка запроса к DaData: ' . ($curlError ?: 'unknown')]);
            return;
        }

        $data = json_decode($response, true);

        if ($statusCode >= 200 && $statusCode < 300 && is_array($data)) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            return;
        }

        $errorMessage = 'DaData ответила с ошибкой';

        if (is_array($data)) {
            $errorMessage = $data['message']
                ?? $data['detail']
                ?? $data['reason']
                ?? $errorMessage;
        } elseif (is_string($response) && $response !== '') {
            $errorMessage = $response;
        }

        http_response_code($statusCode >= 400 && $statusCode < 600 ? 502 : ($statusCode ?: 502));
        echo json_encode(['error' => $errorMessage, 'status' => $statusCode], JSON_UNESCAPED_UNICODE);
    }
}

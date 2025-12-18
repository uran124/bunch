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

        $dadata = new DadataClient(DADATA_API_KEY, DADATA_SECRET_KEY);
        $response = $dadata->normalizeAddress($address);

        if ($response['success'] && is_array($response['data'])) {
            echo json_encode($response['data'], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code($response['status'] >= 400 && $response['status'] < 600 ? 502 : 502);
        echo json_encode([
            'error' => $response['error'] ?? 'Не удалось получить ответ от DaData',
            'status' => $response['status'],
        ], JSON_UNESCAPED_UNICODE);
    }
}

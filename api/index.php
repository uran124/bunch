<?php
require_once __DIR__ . '/../config.php';

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

Session::start();

header('Content-Type: application/json; charset=utf-8');

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$resource = ltrim(substr($path, strlen('api/')), '/');

switch ($resource) {
    case 'delivery/zones':
        handleDeliveryZones();
        break;
    case 'dadata/clean-address':
        handleDadataCleanAddress();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

function handleDeliveryZones(): void
{
    $zoneModel = new DeliveryZone();
    $apiToken = defined('DADATA_API_KEY') ? DADATA_API_KEY : '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === '1';
        $zones = $zoneModel->getZones(!$includeInactive, true);

        echo json_encode([
            'shop' => 'bunch',
            'version' => $zoneModel->getPricingVersion(),
            'zones' => $zones,
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $providedToken = null;

    if (stripos($authHeader, 'Token ') === 0) {
        $providedToken = trim(substr($authHeader, strlen('Token ')));
    } elseif (stripos($authHeader, 'Bearer ') === 0) {
        $providedToken = trim(substr($authHeader, strlen('Bearer ')));
    }

    $isAuthorized = Auth::check() || ($providedToken && hash_equals($apiToken, $providedToken));

    if (!$isAuthorized) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        return;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $zones = is_array($payload['zones'] ?? null) ? $payload['zones'] : null;

    if ($zones === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный формат данных']);
        return;
    }

    try {
        $version = $zoneModel->saveZones($zones);
        echo json_encode([
            'ok' => true,
            'version' => $version,
            'zones' => $zoneModel->getZones(false, true),
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDadataCleanAddress(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
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

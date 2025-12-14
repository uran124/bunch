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
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

function handleDeliveryZones(): void
{
    $zoneModel = new DeliveryZone();

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

    if (!Auth::check()) {
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

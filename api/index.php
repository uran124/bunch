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

if (str_starts_with($resource, 'account/addresses')) {
    handleAccountAddresses($resource);
    exit;
}

switch ($resource) {
    case 'delivery/zones':
        handleDeliveryZones();
        break;
    case 'dadata/clean-address':
        handleDadataCleanAddress();
        break;
    case 'lottery/tickets':
        handleLotteryTickets();
        break;
    case 'lottery/reserve':
        handleLotteryReserve();
        break;
    case 'lottery/pay':
        handleLotteryPay();
        break;
    case 'auction/lot':
        handleAuctionLot();
        break;
    case 'auction/bid':
        handleAuctionBid();
        break;
    case 'auction/bid/cancel':
        handleAuctionBidCancel();
        break;
    case 'auction/blitz':
        handleAuctionBlitz();
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

function handleAccountAddresses(string $resource): void
{
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        return;
    }

    $userId = (int) Auth::userId();
    $segments = array_values(array_filter(explode('/', $resource)));
    $addressId = isset($segments[2]) ? (int) $segments[2] : null;
    $action = $segments[3] ?? null;

    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $addressModel = new UserAddress();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'primary' && $addressId) {
        $updated = $addressModel->setPrimaryForUser($userId, $addressId);
        if (!$updated) {
            http_response_code(404);
            echo json_encode(['error' => 'Адрес не найден']);
            return;
        }
        echo json_encode(['ok' => true]);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$addressId) {
        $addressText = trim((string) ($payload['address_text'] ?? ''));
        if ($addressText === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Адрес не указан']);
            return;
        }

        $addressId = $addressModel->createForUser($userId, $payload);
        echo json_encode(['ok' => true, 'id' => $addressId]);
        return;
    }

    if (($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') && $addressId) {
        $addressText = trim((string) ($payload['address_text'] ?? ''));
        if ($addressText === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Адрес не указан']);
            return;
        }

        $updated = $addressModel->updateForUser($userId, $addressId, $payload);
        if (!$updated) {
            http_response_code(404);
            echo json_encode(['error' => 'Адрес не найден']);
            return;
        }

        echo json_encode(['ok' => true]);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $addressId) {
        $deleted = $addressModel->archiveForUser($userId, $addressId);
        if (!$deleted) {
            http_response_code(404);
            echo json_encode(['error' => 'Адрес не найден']);
            return;
        }
        echo json_encode(['ok' => true]);
        return;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function handleLotteryTickets(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $lotteryId = (int) ($_GET['lottery_id'] ?? 0);
    if ($lotteryId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный идентификатор лотереи']);
        return;
    }

    $lotteryModel = new Lottery();
    $lottery = $lotteryModel->getById($lotteryId);
    if (!$lottery) {
        http_response_code(404);
        echo json_encode(['error' => 'Лотерея не найдена']);
        return;
    }

    $ticketModel = new LotteryTicket();
    $tickets = $ticketModel->listTickets($lotteryId, Auth::userId());

    echo json_encode([
        'lottery' => $lottery,
        'tickets' => $tickets,
        'reserve_ttl' => $lotteryModel->getReserveTtlMinutes(),
    ], JSON_UNESCAPED_UNICODE);
}

function handleLotteryReserve(): void
{
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
    $lotteryId = (int) ($payload['lottery_id'] ?? 0);
    $ticketNumber = isset($payload['ticket_number']) ? (int) $payload['ticket_number'] : null;
    $random = !empty($payload['random']);

    if ($lotteryId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректные данные']);
        return;
    }

    if ($random) {
        $ticketNumber = null;
    }

    $userModel = new User();
    $user = $userModel->findById(Auth::userId());
    $phone = $user['phone'] ?? '';
    $digits = preg_replace('/\D+/', '', $phone);
    $last4 = $digits !== '' ? substr($digits, -4) : '----';

    $ticketModel = new LotteryTicket();

    try {
        $ticket = $ticketModel->reserveTicket($lotteryId, $ticketNumber, Auth::userId(), $last4);
        echo json_encode(['ticket' => $ticket], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function handleLotteryPay(): void
{
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
    $ticketId = (int) ($payload['ticket_id'] ?? 0);
    if ($ticketId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный билет']);
        return;
    }

    $userModel = new User();
    $user = $userModel->findById(Auth::userId());
    $phone = $user['phone'] ?? '';
    $digits = preg_replace('/\D+/', '', $phone);
    $last4 = $digits !== '' ? substr($digits, -4) : '----';

    $ticketModel = new LotteryTicket();

    try {
        $ticketModel->markPaid($ticketId, Auth::userId(), $last4);
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function handleAuctionLot(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $lotId = (int) ($_GET['id'] ?? 0);
    if ($lotId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный лот']);
        return;
    }

    $lotModel = new AuctionLot();
    $lot = $lotModel->getLotDetails($lotId);
    if (!$lot) {
        http_response_code(404);
        echo json_encode(['error' => 'Лот не найден']);
        return;
    }

    $bidModel = new AuctionBid();
    $bids = $bidModel->getRecentBids($lotId, 6);

    echo json_encode([
        'lot' => $lot,
        'bids' => $bids,
    ], JSON_UNESCAPED_UNICODE);
}

function handleAuctionBid(): void
{
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
    $lotId = (int) ($payload['lot_id'] ?? 0);
    $amount = isset($payload['amount']) ? (float) $payload['amount'] : null;

    if ($lotId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный лот']);
        return;
    }

    $lotModel = new AuctionLot();

    try {
        $bid = $lotModel->placeBid($lotId, Auth::userId(), $amount);
        echo json_encode(['bid' => $bid], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function handleAuctionBidCancel(): void
{
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
    $bidId = (int) ($payload['bid_id'] ?? 0);

    if ($bidId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректная ставка']);
        return;
    }

    $lotModel = new AuctionLot();

    try {
        $lotModel->cancelBid($bidId, Auth::userId());
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function handleAuctionBlitz(): void
{
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
    $lotId = (int) ($payload['lot_id'] ?? 0);
    if ($lotId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный лот']);
        return;
    }

    $lotModel = new AuctionLot();

    try {
        $bid = $lotModel->blitz($lotId, Auth::userId());
        echo json_encode(['bid' => $bid], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

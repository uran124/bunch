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

if (Csrf::shouldProtectMethod() && shouldValidateApiCsrf($resource) && !Csrf::isValidRequest()) {
    http_response_code(403);
    echo json_encode(['error' => 'Недействительный CSRF-токен'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (str_starts_with($resource, 'account/addresses')) {
    handleAccountAddresses($resource);
    exit;
}

if (str_starts_with($resource, 'account/calendar')) {
    handleAccountCalendar($resource);
    exit;
}

if (str_starts_with($resource, 'account/profile')) {
    handleAccountProfile();
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
    case 'internal/telegram/handle-update':
        handleInternalTelegramUpdate();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

function shouldValidateApiCsrf(string $resource): bool
{
    if ($resource === 'internal/telegram/handle-update') {
        return false;
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (stripos($authHeader, 'Token ') === 0 || stripos($authHeader, 'Bearer ') === 0) {
        return false;
    }

    return true;
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
        $settlement = trim((string) ($payload['settlement'] ?? ''));
        $street = trim((string) ($payload['street'] ?? ''));
        $house = trim((string) ($payload['house'] ?? ''));
        $recipientName = trim((string) ($payload['recipient_name'] ?? ''));
        $recipientPhone = trim((string) ($payload['recipient_phone'] ?? ''));
        $payload['zone_calculated_at'] = normalizeZoneCalculatedAt($payload['zone_calculated_at'] ?? null);

        if ($settlement === '' || $street === '' || $house === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Укажите город, улицу и номер дома']);
            return;
        }

        if ($recipientName === '' || $recipientPhone === '') {
            $userModel = new User();
            $user = $userModel->findById($userId);
            $recipientName = $recipientName ?: trim((string) ($user['name'] ?? ''));
            $recipientPhone = $recipientPhone ?: trim((string) ($user['phone'] ?? ''));
            $payload['recipient_name'] = $recipientName;
            $payload['recipient_phone'] = $recipientPhone;
        }

        if ($recipientName === '' || $recipientPhone === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Укажите имя и телефон получателя']);
            return;
        }

        $addressText = trim((string) ($payload['address_text'] ?? ''));
        if ($addressText === '') {
            $addressText = buildAddressText($settlement, $street, $house, (string) ($payload['apartment'] ?? ''));
            $payload['address_text'] = $addressText;
        }

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
        $settlement = trim((string) ($payload['settlement'] ?? ''));
        $street = trim((string) ($payload['street'] ?? ''));
        $house = trim((string) ($payload['house'] ?? ''));
        $recipientName = trim((string) ($payload['recipient_name'] ?? ''));
        $recipientPhone = trim((string) ($payload['recipient_phone'] ?? ''));
        $payload['zone_calculated_at'] = normalizeZoneCalculatedAt($payload['zone_calculated_at'] ?? null);

        if ($settlement === '' || $street === '' || $house === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Укажите город, улицу и номер дома']);
            return;
        }

        if ($recipientName === '' || $recipientPhone === '') {
            $userModel = new User();
            $user = $userModel->findById($userId);
            $recipientName = $recipientName ?: trim((string) ($user['name'] ?? ''));
            $recipientPhone = $recipientPhone ?: trim((string) ($user['phone'] ?? ''));
            $payload['recipient_name'] = $recipientName;
            $payload['recipient_phone'] = $recipientPhone;
        }

        if ($recipientName === '' || $recipientPhone === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Укажите имя и телефон получателя']);
            return;
        }

        $addressText = trim((string) ($payload['address_text'] ?? ''));
        if ($addressText === '') {
            $addressText = buildAddressText($settlement, $street, $house, (string) ($payload['apartment'] ?? ''));
            $payload['address_text'] = $addressText;
        }

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

function handleAccountCalendar(string $resource): void
{
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        return;
    }

    $userId = (int) Auth::userId();
    $segments = array_values(array_filter(explode('/', $resource)));
    $segment = $segments[2] ?? null;
    $reminderId = null;
    $action = $segments[3] ?? null;

    if ($segment === 'settings') {
        $action = 'settings';
    } elseif ($segment !== null) {
        $reminderId = (int) $segment;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $reminderModel = new BirthdayReminder();
    $userModel = new User();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $reminders = $reminderModel->getByUserId($userId);
        echo json_encode(['reminders' => $reminders], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($action === 'settings' && in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'], true)) {
        $leadDays = (int) ($payload['lead_days'] ?? 0);
        if ($leadDays < 1 || $leadDays > 7) {
            http_response_code(422);
            echo json_encode(['error' => 'Допустимый диапазон напоминания — 1-7 дней.']);
            return;
        }

        $userModel->updateBirthdayReminderDays($userId, $leadDays);
        echo json_encode(['ok' => true, 'lead_days' => $leadDays], JSON_UNESCAPED_UNICODE);
        return;
    }

    $recipient = trim((string) ($payload['recipient'] ?? ''));
    $occasion = trim((string) ($payload['occasion'] ?? ''));
    $dateRaw = trim((string) ($payload['date'] ?? ''));

    $validatePayload = static function () use ($recipient, $occasion, $dateRaw): ?string {
        if ($recipient === '') {
            return 'Укажите имя получателя.';
        }
        if ($occasion === '') {
            return 'Укажите повод.';
        }
        if ($dateRaw === '') {
            return 'Укажите дату.';
        }
        $date = DateTime::createFromFormat('Y-m-d', $dateRaw);
        if (!$date || $date->format('Y-m-d') !== $dateRaw) {
            return 'Некорректная дата.';
        }
        return null;
    };

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$reminderId) {
        $error = $validatePayload();
        if ($error !== null) {
            http_response_code(422);
            echo json_encode(['error' => $error]);
            return;
        }

        $newId = $reminderModel->createForUser($userId, [
            'recipient' => $recipient,
            'occasion' => $occasion,
            'reminder_date' => $dateRaw,
        ]);
        echo json_encode([
            'ok' => true,
            'reminder' => [
                'id' => $newId,
                'recipient' => $recipient,
                'occasion' => $occasion,
                'reminder_date' => $dateRaw,
            ],
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($reminderId && $_SERVER['REQUEST_METHOD'] === 'PUT') {
        $error = $validatePayload();
        if ($error !== null) {
            http_response_code(422);
            echo json_encode(['error' => $error]);
            return;
        }

        $updated = $reminderModel->updateForUser($userId, $reminderId, [
            'recipient' => $recipient,
            'occasion' => $occasion,
            'reminder_date' => $dateRaw,
        ]);

        if (!$updated) {
            http_response_code(404);
            echo json_encode(['error' => 'Напоминание не найдено']);
            return;
        }

        echo json_encode([
            'ok' => true,
            'reminder' => [
                'id' => $reminderId,
                'recipient' => $recipient,
                'occasion' => $occasion,
                'reminder_date' => $dateRaw,
            ],
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($reminderId && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $deleted = $reminderModel->deleteForUser($userId, $reminderId);
        if (!$deleted) {
            http_response_code(404);
            echo json_encode(['error' => 'Напоминание не найдено']);
            return;
        }
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($reminderId && $action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $deleted = $reminderModel->deleteForUser($userId, $reminderId);
        if (!$deleted) {
            http_response_code(404);
            echo json_encode(['error' => 'Напоминание не найдено']);
            return;
        }
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        return;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function handleAccountProfile(): void
{
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        return;
    }

    if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'POST'], true)) {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim((string) ($payload['name'] ?? ''));

    if ($name === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Имя не может быть пустым']);
        return;
    }

    $userId = (int) Auth::userId();
    $userModel = new User();
    $updated = $userModel->updateName($userId, $name);

    if (!$updated) {
        http_response_code(404);
        echo json_encode(['error' => 'Пользователь не найден']);
        return;
    }

    echo json_encode(['ok' => true, 'name' => $name], JSON_UNESCAPED_UNICODE);
}

function buildAddressText(string $settlement, string $street, string $house, string $apartment = ''): string
{
    $parts = array_filter([
        $settlement,
        $street,
        $house !== '' ? 'д. ' . $house : null,
        $apartment !== '' ? 'кв/офис ' . $apartment : null,
    ]);

    return $parts ? implode(', ', $parts) : '';
}

function normalizeZoneCalculatedAt(?string $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    try {
        $dt = new DateTimeImmutable($value);
    } catch (Throwable $e) {
        return null;
    }

    return $dt->format('Y-m-d H:i:s');
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
    $settings = new Setting();
    $defaults = $settings->getLotteryDefaults();
    $limitRaw = $settings->get(Setting::LOTTERY_FREE_MONTHLY_LIMIT, $defaults[Setting::LOTTERY_FREE_MONTHLY_LIMIT] ?? '0');
    $freeMonthlyLimit = (int) $limitRaw;

    echo json_encode([
        'lottery' => $lottery,
        'tickets' => $tickets,
        'reserve_ttl' => $lotteryModel->getReserveTtlMinutes(),
        'free_monthly_limit' => $freeMonthlyLimit,
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
    $history = isset($_GET['history']) && $_GET['history'] === '1';
    $bids = $history ? $bidModel->getLotBids($lotId) : $bidModel->getRecentBids($lotId, 6);

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
    $amount = isset($payload['amount']) ? (int) floor((float) $payload['amount']) : null;

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

function handleInternalTelegramUpdate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'decision' => 'error', 'errors' => ['Method not allowed']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody)) {
        $rawBody = '';
    }

    $keyId = trim((string) ($_SERVER['HTTP_X_BOT_KEY_ID'] ?? ''));
    $timestampRaw = trim((string) ($_SERVER['HTTP_X_BOT_TIMESTAMP'] ?? ''));
    $nonce = trim((string) ($_SERVER['HTTP_X_BOT_NONCE'] ?? ''));
    $signatureHeader = trim((string) ($_SERVER['HTTP_X_BOT_SIGNATURE'] ?? ''));

    if (
        $keyId === ''
        || $timestampRaw === ''
        || $nonce === ''
        || $signatureHeader === ''
        || !preg_match('/^\d+$/', $timestampRaw)
    ) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Missing or invalid auth headers']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $expectedKeyId = defined('TG_INTERNAL_API_KEY_ID') ? (string) TG_INTERNAL_API_KEY_ID : 'bunch-bot-v1';
    $secret = defined('TG_INTERNAL_API_SECRET') ? (string) TG_INTERNAL_API_SECRET : '';
    $maxSkew = defined('TG_INTERNAL_API_MAX_SKEW_SECONDS') ? (int) TG_INTERNAL_API_MAX_SKEW_SECONDS : 300;

    if ($keyId !== $expectedKeyId || $secret === '') {
        http_response_code(401);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Invalid key id or secret is not configured']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $timestamp = (int) $timestampRaw;
    $now = time();
    if (abs($now - $timestamp) > $maxSkew) {
        http_response_code(408);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Request timestamp is expired']], JSON_UNESCAPED_UNICODE);
        return;
    }

    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $nonce)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Invalid nonce format']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $providedSignature = $signatureHeader;
    if (stripos($providedSignature, 'sha256=') === 0) {
        $providedSignature = substr($providedSignature, 7);
    }

    $signedPayload = $timestampRaw . '.' . $nonce . '.' . $rawBody;
    $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

    if (!hash_equals($expectedSignature, strtolower($providedSignature))) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Invalid signature']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $nonceCache = internalTelegramReadCache('telegram_api_nonce_cache.json');
    internalTelegramGcCache($nonceCache, $now, 24 * 3600);
    if (isset($nonceCache[$nonce])) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Nonce already used']], JSON_UNESCAPED_UNICODE);
        return;
    }
    $nonceCache[$nonce] = $now;
    internalTelegramWriteCache('telegram_api_nonce_cache.json', $nonceCache);

    $payload = json_decode($rawBody, true);
    if (!is_array($payload) || !is_array($payload['update'] ?? null)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['Invalid payload body']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $update = $payload['update'];
    $updateId = isset($update['update_id']) ? (int) $update['update_id'] : 0;
    $message = $update['message'] ?? null;

    if ($updateId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['update.update_id is required']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $idempotencyKey = 'tg:' . $updateId;
    $idempotencyCache = internalTelegramReadCache('telegram_api_idempotency_cache.json');
    internalTelegramGcCache($idempotencyCache, $now, 24 * 3600);

    if (isset($idempotencyCache[$idempotencyKey])) {
        echo json_encode([
            'ok' => true,
            'decision' => 'duplicate',
            'idempotency_key' => $idempotencyKey,
            'actions' => [],
            'events' => [],
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if (!is_array($message)) {
        $idempotencyCache[$idempotencyKey] = $now;
        internalTelegramWriteCache('telegram_api_idempotency_cache.json', $idempotencyCache);

        echo json_encode([
            'ok' => true,
            'decision' => 'ignored',
            'idempotency_key' => $idempotencyKey,
            'actions' => [],
            'events' => [],
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    $chatId = (int) ($message['chat']['id'] ?? 0);
    if ($chatId === 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'decision' => 'error', 'actions' => [], 'errors' => ['message.chat.id is required']], JSON_UNESCAPED_UNICODE);
        return;
    }

    $messageThreadId = isset($message['message_thread_id']) ? (int) $message['message_thread_id'] : null;
    $supportChatId = -1002055168794;
    $supportThreadId = 1155;

    if ($chatId === $supportChatId) {
        $text = trim((string) ($message['text'] ?? ''));
        $replyTo = is_array($message['reply_to_message'] ?? null) ? $message['reply_to_message'] : null;
        $replyToId = (int) ($replyTo['message_id'] ?? 0);

        if (($messageThreadId === null || $messageThreadId === $supportThreadId) && $replyToId > 0 && $text !== '') {
            $supportChat = new SupportChat();
            $resolvedChatId = $supportChat->getChatIdForTelegramMessage($replyToId);
            if ($resolvedChatId === null) {
                $resolvedChatId = internalTelegramExtractSupportChatIdFromMessage($replyTo);
            }

            if ($resolvedChatId) {
                $supportChat->appendMessage($resolvedChatId, 'support', $text, [
                    'telegram' => [
                        'message_id' => (int) ($message['message_id'] ?? 0),
                    ],
                ]);

                $currentMessageId = (int) ($message['message_id'] ?? 0);
                if ($currentMessageId > 0) {
                    $supportChat->mapTelegramMessage($currentMessageId, $resolvedChatId);
                }
            }
        }

        $idempotencyCache[$idempotencyKey] = $now;
        internalTelegramWriteCache('telegram_api_idempotency_cache.json', $idempotencyCache);

        echo json_encode([
            'ok' => true,
            'decision' => 'handled',
            'idempotency_key' => $idempotencyKey,
            'actions' => [],
            'events' => [],
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    $userModel = new User();
    $verificationModel = new VerificationCode();
    $logger = new Logger();
    $analytics = new Analytics();

    $username = $message['from']['username'] ?? null;
    $fromFirstName = trim((string) ($message['from']['first_name'] ?? ''));
    $fromLastName = trim((string) ($message['from']['last_name'] ?? ''));
    $fromName = trim($fromFirstName . ' ' . $fromLastName) ?: null;
    $text = trim((string) ($message['text'] ?? ''));
    $contact = is_array($message['contact'] ?? null) ? $message['contact'] : null;
    $phoneFromText = $text !== '' ? internalTelegramExtractPhoneFromText($text) : null;

    $actions = [];
    $events = [];
    $decision = 'handled';

    if (preg_match('/^\/start(?:\s+|$)/u', $text) === 1) {
        [$actions, $events] = internalTelegramBuildRegistrationCodeActions($userModel, $verificationModel, $chatId, $username, null, $logger, $analytics, $contact, $fromName);
    } elseif (mb_stripos($text, 'восстановить pin') !== false || mb_stripos($text, 'восстановить пин') !== false) {
        [$actions, $events] = internalTelegramBuildRecoveryCodeActions($userModel, $verificationModel, $chatId, $username, $logger, $analytics);
    } elseif (mb_stripos($text, 'получить код') !== false || mb_stripos($text, 'регистрация') !== false) {
        [$actions, $events] = internalTelegramBuildRegistrationCodeActions($userModel, $verificationModel, $chatId, $username, null, $logger, $analytics, null, $fromName);
    } elseif ($contact || $phoneFromText) {
        $phone = $contact['phone_number'] ?? $phoneFromText;
        [$actions, $events] = internalTelegramBuildRegistrationCodeActions($userModel, $verificationModel, $chatId, $username, $phone, $logger, $analytics, $contact, $fromName);
    } else {
        $actions = internalTelegramBuildUnhandledMessageActions($chatId, $username, $fromName, $text);
    }

    $idempotencyCache[$idempotencyKey] = $now;
    internalTelegramWriteCache('telegram_api_idempotency_cache.json', $idempotencyCache);

    echo json_encode([
        'ok' => true,
        'decision' => $decision,
        'idempotency_key' => $idempotencyKey,
        'actions' => $actions,
        'events' => $events,
        'errors' => [],
    ], JSON_UNESCAPED_UNICODE);
}

function internalTelegramBuildRegistrationCodeActions(
    User $userModel,
    VerificationCode $verificationModel,
    int $chatId,
    ?string $username,
    ?string $phone,
    Logger $logger,
    Analytics $analytics,
    ?array $contact = null,
    ?string $fallbackName = null
): array {
    $actions = [];
    $events = [];
    $phone = $phone ? internalTelegramNormalisePhone($phone) : null;
    $existingByChat = $userModel->findByTelegramChatId($chatId);

    if ($existingByChat) {
        $code = $verificationModel->createCode(
            $chatId,
            'recover',
            $existingByChat['phone'],
            (int) $existingByChat['id'],
            $username,
            $existingByChat['name'] ?? null
        );

        $userModel->linkTelegram((int) $existingByChat['id'], $chatId, $username);

        $actions[] = internalTelegramBuildSendMessageAction($chatId, 'Вы уже зарегестрированны на bunch! Используйте ссылку чтобы войти: https://bunchflowers.ru/login, чтобы восстановить пароль: https://bunchflowers.ru/recover. Введите код для восстановления:');
        $actions[] = internalTelegramBuildSendMessageAction($chatId, internalTelegramFormatCode($code));

        $eventPayload = ['user_id' => (int) $existingByChat['id'], 'chat_id' => $chatId];
        $logger->logEvent('TG_ALREADY_REGISTERED_CODE_SENT', $eventPayload);
        $analytics->track('tg_code_sent', ['purpose' => 'recover', 'user_id' => (int) $existingByChat['id']]);
        $events[] = ['name' => 'TG_ALREADY_REGISTERED_CODE_SENT', 'payload' => $eventPayload];

        return [$actions, $events];
    }

    $existing = $phone ? $userModel->findByPhone($phone) : $userModel->findByTelegramChatId($chatId);
    $userId = $existing ? (int) $existing['id'] : null;
    $name = $existing['name'] ?? null;

    if ($contact) {
        $firstName = trim((string) ($contact['first_name'] ?? ''));
        $lastName = trim((string) ($contact['last_name'] ?? ''));
        $name = trim($firstName . ' ' . $lastName) ?: null;
    }

    if (!$name && $fallbackName) {
        $name = $fallbackName;
    }

    if ($existing) {
        $userModel->linkTelegram((int) $existing['id'], $chatId, $username);
    }

    $codePhone = $phone ?? ($existing['phone'] ?? null);
    $code = $verificationModel->createCode($chatId, 'register', $codePhone, $userId, $username, $name);

    $actions[] = internalTelegramBuildSendMessageAction($chatId, 'Ваш код для регистрации на сайте:');
    $actions[] = internalTelegramBuildSendMessageAction($chatId, internalTelegramFormatCode($code));

    $eventPayload = ['user_id' => $userId, 'chat_id' => $chatId, 'phone' => $phone];
    $logger->logEvent('TG_REG_CODE_SENT', $eventPayload);
    $analytics->track('tg_code_sent', ['purpose' => 'register', 'user_id' => $userId]);
    $events[] = ['name' => 'TG_REG_CODE_SENT', 'payload' => $eventPayload];

    return [$actions, $events];
}


function internalTelegramBuildUnhandledMessageActions(int $chatId, ?string $username, ?string $fromName, string $text): array
{
    $unknownChatId = -1002055168794;
    $unknownThreadId = 1109;

    $displayName = $username ? '@' . $username : ($fromName ?: 'Пользователь ' . $chatId);
    $forwardText = $text !== '' ? $text : '[без текста]';

    $safeDisplayName = htmlspecialchars($displayName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeForwardText = htmlspecialchars($forwardText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return [
        internalTelegramBuildSendMessageAction(
            $unknownChatId,
            '[' . $safeDisplayName . ' ' . $safeForwardText . ']',
            ['message_thread_id' => $unknownThreadId]
        ),
        internalTelegramBuildSendMessageAction(
            $chatId,
            'Запрос зафиксирован. Я передал его менеджеру, он свяжется с вами в ближайшее время.'
        ),
    ];
}

function internalTelegramBuildRecoveryCodeActions(
    User $userModel,
    VerificationCode $verificationModel,
    int $chatId,
    ?string $username,
    Logger $logger,
    Analytics $analytics
): array {
    $actions = [];
    $events = [];
    $user = $userModel->findByTelegramChatId($chatId);

    if (!$user) {
        $actions[] = internalTelegramBuildSendMessageAction($chatId, 'Пользователь не найден. Сначала получите код и привяжите номер телефона.');
        return [$actions, $events];
    }

    $code = $verificationModel->createCode($chatId, 'recover', $user['phone'], (int) $user['id'], $username, $user['name'] ?? null);
    $userModel->linkTelegram((int) $user['id'], $chatId, $username);

    $actions[] = internalTelegramBuildSendMessageAction($chatId, 'Код для смены PIN:');
    $actions[] = internalTelegramBuildSendMessageAction($chatId, internalTelegramFormatCode($code));

    $eventPayload = ['user_id' => (int) $user['id'], 'chat_id' => $chatId];
    $logger->logEvent('TG_RECOVERY_CODE_SENT', $eventPayload);
    $analytics->track('tg_code_sent', ['purpose' => 'recover', 'user_id' => (int) $user['id']]);
    $events[] = ['name' => 'TG_RECOVERY_CODE_SENT', 'payload' => $eventPayload];

    return [$actions, $events];
}

function internalTelegramBuildSendMessageAction(int $chatId, string $text, array $options = []): array
{
    $actionOptions = array_merge(['parse_mode' => 'HTML'], $options);

    return [
        'type' => 'sendMessage',
        'chat_id' => $chatId,
        'text' => $text,
        'options' => $actionOptions,
    ];
}

function internalTelegramFormatCode(string $code): string
{
    $safe = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<code>' . $safe . '</code>';
}

function internalTelegramNormalisePhone(string $phone): string
{
    $normalized = internalTelegramExtractPhoneFromText($phone);

    return $normalized ?? $phone;
}

function internalTelegramExtractPhoneFromText(string $text): ?string
{
    $digits = preg_replace('/\D+/', '', $text);
    if ($digits === null || $digits === '' || strlen($digits) < 5) {
        return null;
    }

    if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
        $digits = '7' . substr($digits, 1);
    }

    if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
        $digits = '7' . $digits;
    }

    return '+' . $digits;
}

function internalTelegramExtractSupportChatIdFromMessage(?array $message): ?string
{
    if (!$message) {
        return null;
    }

    $text = trim((string) ($message['text'] ?? ''));
    if ($text === '') {
        return null;
    }

    if (preg_match('/#chat:([a-z0-9_-]+)/i', $text, $matches) === 1) {
        return strtolower((string) $matches[1]);
    }

    return null;
}

function internalTelegramReadCache(string $fileName): array
{
    $path = internalTelegramDataFilePath($fileName);
    if (!file_exists($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
}

function internalTelegramWriteCache(string $fileName, array $cache): void
{
    $path = internalTelegramDataFilePath($fileName);
    @file_put_contents($path, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function internalTelegramGcCache(array &$cache, int $now, int $ttl): void
{
    foreach ($cache as $key => $createdAt) {
        if (!is_numeric($createdAt) || ((int) $createdAt + $ttl) < $now) {
            unset($cache[$key]);
        }
    }
}

function internalTelegramDataFilePath(string $fileName): string
{
    $dir = __DIR__ . '/../bot/data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return rtrim($dir, '/') . '/' . ltrim($fileName, '/');
}

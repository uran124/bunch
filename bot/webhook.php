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

$unknownLogger = new Logger('telegram_unknown.log');
$appLogger = new Logger();
$analytics = new Analytics();

if (!isset($_GET['secret']) || $_GET['secret'] !== TG_WEBHOOK_SECRET) {
    http_response_code(403);
    $unknownLogger->logRaw(date('c') . ' invalid secret ' . ($_GET['secret'] ?? ''));
    exit;
}

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    $unknownLogger->logRaw(date('c') . ' unable to parse update: ' . $input);
    exit;
}

$message = $update['message'] ?? null;
if (!$message) {
    $unknownLogger->logRaw(date('c') . ' unsupported payload: ' . $input);
    exit;
}

$chatId = $message['chat']['id'] ?? null;
$username = $message['from']['username'] ?? null;
$text = trim($message['text'] ?? '');
$contact = $message['contact'] ?? null;

if (!$chatId) {
    $unknownLogger->logRaw(date('c') . ' no chat id: ' . $input);
    exit;
}

$telegram = new Telegram(TG_BOT_TOKEN);
$userModel = new User();

if ($text === '/start') {
    sendStartMenu($telegram, $chatId);
    exit;
}

if (mb_stripos($text, 'регистрация') !== false) {
    requestPhone($telegram, $chatId);
    exit;
}

if (mb_stripos($text, 'восстановить pin') !== false) {
    handlePinReset($telegram, $userModel, $chatId, $username, $appLogger, $analytics);
    exit;
}

if ($contact || ($text !== '' && preg_match('/^\+?\d{5,}$/', $text))) {
    $phone = $contact['phone_number'] ?? $text;
    handleRegistration($telegram, $userModel, $chatId, $username, $phone, $appLogger, $analytics);
    exit;
}

$unknownLogger->logRaw(date('c') . ' unhandled message: ' . $input);

function sendStartMenu(Telegram $telegram, int $chatId): void
{
    $keyboard = [
        'keyboard' => [
            [
                ['text' => 'Регистрация/Вход', 'request_contact' => false],
            ],
            [
                ['text' => 'Восстановить PIN', 'request_contact' => false],
            ],
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ];

    $telegram->sendMessage($chatId, "Выберите действие", [
        'reply_markup' => json_encode($keyboard, JSON_UNESCAPED_UNICODE),
    ]);
}

function requestPhone(Telegram $telegram, int $chatId): void
{
    $keyboard = [
        'keyboard' => [
            [
                ['text' => 'Отправить телефон', 'request_contact' => true],
            ],
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ];

    $telegram->sendMessage($chatId, 'Для входа отправьте номер телефона.', [
        'reply_markup' => json_encode($keyboard, JSON_UNESCAPED_UNICODE),
    ]);
}

function handleRegistration(
    Telegram $telegram,
    User $userModel,
    int $chatId,
    ?string $username,
    string $phone,
    Logger $logger,
    Analytics $analytics
): void
{
    $phone = normalisePhone($phone);
    $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $pinHash = password_hash($pin, PASSWORD_DEFAULT);

    $existing = $userModel->findByPhone($phone);

    if ($existing) {
        $userModel->updatePin((int) $existing['id'], $pinHash);
        $userModel->linkTelegram((int) $existing['id'], $chatId, $username);
        $logger->logEvent('TG_PIN_RESET', ['user_id' => $existing['id'], 'chat_id' => $chatId]);
        $analytics->track('pin_reset', ['user_id' => $existing['id']]);
    } else {
        $userId = $userModel->create($phone, $pinHash, $chatId, $username);
        $logger->logEvent('TG_USER_REGISTERED', ['user_id' => $userId, 'phone' => $phone]);
    }

    $message = "Ваш PIN: {$pin}. Используйте его для входа на сайте.";
    $telegram->sendMessage($chatId, $message);
}

function handlePinReset(Telegram $telegram, User $userModel, int $chatId, ?string $username, Logger $logger, Analytics $analytics): void
{
    $user = $userModel->findByTelegramChatId($chatId);

    if (!$user) {
        $telegram->sendMessage($chatId, 'Пользователь не найден. Отправьте телефон через "Регистрация/Вход".');
        return;
    }

    $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $pinHash = password_hash($pin, PASSWORD_DEFAULT);

    $userModel->updatePin((int) $user['id'], $pinHash);
    $userModel->linkTelegram((int) $user['id'], $chatId, $username);

    $logger->logEvent('TG_PIN_RESET', ['user_id' => $user['id'], 'chat_id' => $chatId]);
    $analytics->track('pin_reset', ['user_id' => $user['id']]);

    $telegram->sendMessage($chatId, "Новый PIN: {$pin}");
}

function normalisePhone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);

    if ($digits === null) {
        return $phone;
    }

    if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
        $digits = '7' . substr($digits, 1);
    }

    if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
        $digits = '7' . $digits;
    }

    return '+' . $digits;
}

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
$verificationModel = new VerificationCode();

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

if (mb_stripos($text, 'восстановить pin') !== false || mb_stripos($text, 'восстановить пин') !== false) {
    handleRecoveryCode($telegram, $userModel, $verificationModel, $chatId, $username, $appLogger, $analytics);
    exit;
}

if (mb_stripos($text, 'получить код') !== false || mb_stripos($text, 'регистрация') !== false) {
    requestPhone($telegram, $chatId);
    exit;
}

if ($contact || ($text !== '' && preg_match('/^\+?\d{5,}$/', $text))) {
    $phone = $contact['phone_number'] ?? $text;
    handleRegistrationCode($telegram, $userModel, $verificationModel, $chatId, $username, $phone, $appLogger, $analytics, $contact);
    exit;
}

$unknownLogger->logRaw(date('c') . ' unhandled message: ' . $input);

function sendStartMenu(Telegram $telegram, int $chatId): void
{
    $keyboard = [
        'keyboard' => [
            [
                ['text' => 'Получить код', 'request_contact' => false],
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

    $telegram->sendMessage($chatId, 'Отправьте свой номер, и я вышлю одноразовый код из 5 цифр для сайта.', [
        'reply_markup' => json_encode($keyboard, JSON_UNESCAPED_UNICODE),
    ]);
}

function handleRegistrationCode(
    Telegram $telegram,
    User $userModel,
    VerificationCode $verificationModel,
    int $chatId,
    ?string $username,
    string $phone,
    Logger $logger,
    Analytics $analytics,
    ?array $contact = null
): void {
    $phone = normalisePhone($phone);

    $existing = $userModel->findByPhone($phone);
    $userId = $existing ? (int) $existing['id'] : null;
    $name = null;

    if ($contact) {
        $firstName = trim($contact['first_name'] ?? '');
        $lastName = trim($contact['last_name'] ?? '');
        $name = trim($firstName . ' ' . $lastName) ?: null;
    }

    if ($existing) {
        $userModel->linkTelegram((int) $existing['id'], $chatId, $username);
    }

    $code = $verificationModel->createCode($chatId, 'register', $phone, $userId, $username, $name);

    $telegram->sendMessage($chatId, "Ваш код для регистрации на сайте: {$code}\nВведите его в форме и продолжите заполнение профиля.");

    $logger->logEvent('TG_REG_CODE_SENT', ['user_id' => $userId, 'chat_id' => $chatId, 'phone' => $phone]);
    $analytics->track('tg_code_sent', ['purpose' => 'register', 'user_id' => $userId]);
}

function handleRecoveryCode(
    Telegram $telegram,
    User $userModel,
    VerificationCode $verificationModel,
    int $chatId,
    ?string $username,
    Logger $logger,
    Analytics $analytics
): void {
    $user = $userModel->findByTelegramChatId($chatId);

    if (!$user) {
        $telegram->sendMessage($chatId, 'Пользователь не найден. Сначала получите код и привяжите номер телефона.');
        return;
    }

    $code = $verificationModel->createCode($chatId, 'recover', $user['phone'], (int) $user['id'], $username, $user['name'] ?? null);

    $userModel->linkTelegram((int) $user['id'], $chatId, $username);

    $logger->logEvent('TG_RECOVERY_CODE_SENT', ['user_id' => $user['id'], 'chat_id' => $chatId]);
    $analytics->track('tg_code_sent', ['purpose' => 'recover', 'user_id' => $user['id']]);

    $telegram->sendMessage($chatId, "Код для смены PIN: {$code}\nВведите его на странице восстановления на сайте.");
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

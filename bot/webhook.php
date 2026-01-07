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
$settings = new Setting();
$defaults = $settings->getTelegramDefaults();
$webhookSecret = $settings->get(Setting::TG_WEBHOOK_SECRET, $defaults[Setting::TG_WEBHOOK_SECRET] ?? '');
$botToken = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');

if (!isset($_GET['secret']) || $_GET['secret'] !== $webhookSecret) {
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
$fromFirstName = trim($message['from']['first_name'] ?? '');
$fromLastName = trim($message['from']['last_name'] ?? '');
$fromName = trim($fromFirstName . ' ' . $fromLastName) ?: null;
$text = trim($message['text'] ?? '');
$contact = $message['contact'] ?? null;

if (!$chatId) {
    $unknownLogger->logRaw(date('c') . ' no chat id: ' . $input);
    exit;
}

if ($botToken === '') {
    $unknownLogger->logRaw(date('c') . ' missing TG_BOT_TOKEN');
    exit;
}

$telegram = new Telegram($botToken);
$userModel = new User();

if ($text === '/start') {
    handleRegistrationCode($telegram, $userModel, $verificationModel, $chatId, $username, null, $appLogger, $analytics, $contact, $fromName);
    exit;
}

if (mb_stripos($text, 'восстановить pin') !== false || mb_stripos($text, 'восстановить пин') !== false) {
    handleRecoveryCode($telegram, $userModel, $verificationModel, $chatId, $username, $appLogger, $analytics);
    exit;
}

if (mb_stripos($text, 'получить код') !== false || mb_stripos($text, 'регистрация') !== false) {
    handleRegistrationCode($telegram, $userModel, $verificationModel, $chatId, $username, null, $appLogger, $analytics, null, $fromName);
    exit;
}

$phoneFromText = $text !== '' ? extractPhoneFromText($text) : null;

if ($contact || $phoneFromText) {
    $phone = $contact['phone_number'] ?? $phoneFromText;
    handleRegistrationCode($telegram, $userModel, $verificationModel, $chatId, $username, $phone, $appLogger, $analytics, $contact, $fromName);
    exit;
}

$unknownLogger->logRaw(date('c') . ' unhandled message: ' . $input);
$telegram->sendMessage($chatId, 'Не понял запрос. Нажмите «Получить код» или отправьте номер телефона (можно с пробелами).');

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
    ?string $phone,
    Logger $logger,
    Analytics $analytics,
    ?array $contact = null,
    ?string $fallbackName = null
): void {
    $phone = $phone ? normalisePhone($phone) : null;

    $existing = $phone ? $userModel->findByPhone($phone) : $userModel->findByTelegramChatId($chatId);
    $userId = $existing ? (int) $existing['id'] : null;
    $name = $existing['name'] ?? null;

    if ($contact) {
        $firstName = trim($contact['first_name'] ?? '');
        $lastName = trim($contact['last_name'] ?? '');
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

    $telegram->sendMessage($chatId, 'Ваш код для регистрации на сайте:');
    $telegram->sendMessage($chatId, $code);

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

    $telegram->sendMessage($chatId, 'Код для смены PIN:');
    $telegram->sendMessage($chatId, $code);
}

function normalisePhone(string $phone): string
{
    $normalized = extractPhoneFromText($phone);

    return $normalized ?? $phone;
}

function extractPhoneFromText(string $text): ?string
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

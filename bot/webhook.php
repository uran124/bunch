<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/data/webhook_php_errors.log');
error_reporting(E_ALL);

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

$requestBody = file_get_contents('php://input');
$providedSecret = getProvidedSecret();

if ($providedSecret === null || $providedSecret !== $webhookSecret) {
    http_response_code(403);
    $unknownLogger->logRaw(date('c') . ' invalid secret ' . ($providedSecret ?? ''));
    $unknownLogger->logRaw(date('c') . ' webhook request ' . json_encode([
        'ip' => getRequestIp(),
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'query' => $_GET,
        'headers' => getRequestHeaders(),
        'body' => $requestBody,
    ], JSON_UNESCAPED_UNICODE));
    exit;
}

$input = $requestBody;
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
$threadId = $message['message_thread_id'] ?? null; // NEW: ID темы (топика) в группах с темами
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

/**
 * NEW: Пассивный сбор метаданных для админки (НЕ влияет на текущую бизнес-логику).
 * Сохраняет последний апдейт и реестр чатов/тем в /bot/data.
 */
try {
    tg_admin_passive_capture(__DIR__ . '/data', $update, $message, (int) $chatId, $threadId, $username, $fromName, $text);
} catch (\Throwable $e) {
    // Ничего не делаем, чтобы не ломать основной сценарий
}

if ($botToken === '') {
    $unknownLogger->logRaw(date('c') . ' missing TG_BOT_TOKEN');
    exit;
}

$telegram = new Telegram($botToken);
$userModel = new User();

if (preg_match('/^\/start(?:\s+|$)/u', $text) === 1) {
    handleRegistrationCode($telegram, $userModel, $verificationModel, (int) $chatId, $username, null, $appLogger, $analytics, $contact, $fromName);
    exit;
}

if (mb_stripos($text, 'восстановить pin') !== false || mb_stripos($text, 'восстановить пин') !== false) {
    handleRecoveryCode($telegram, $userModel, $verificationModel, (int) $chatId, $username, $appLogger, $analytics);
    exit;
}

if (mb_stripos($text, 'получить код') !== false || mb_stripos($text, 'регистрация') !== false) {
    handleRegistrationCode($telegram, $userModel, $verificationModel, (int) $chatId, $username, null, $appLogger, $analytics, null, $fromName);
    exit;
}

$phoneFromText = $text !== '' ? extractPhoneFromText($text) : null;

if ($contact || $phoneFromText) {
    $phone = $contact['phone_number'] ?? $phoneFromText;
    handleRegistrationCode($telegram, $userModel, $verificationModel, (int) $chatId, $username, $phone, $appLogger, $analytics, $contact, $fromName);
    exit;
}

$unknownLogger->logRaw(date('c') . ' unhandled message: ' . $input);
$unknownChatId = -1002055168794;
$unknownThreadId = 1109;
$displayName = $username ? '@' . $username : ($fromName ?: 'Пользователь ' . $chatId);
$forwardText = $text !== '' ? $text : '[без текста]';
$telegram->sendMessage($unknownChatId, '[' . $displayName . ' ' . $forwardText . ']', [
    'message_thread_id' => $unknownThreadId,
]);
$telegram->sendMessage((int) $chatId, 'Не понял запрос. Нажмите «Получить код» или отправьте номер телефона (можно с пробелами).');

function getRequestHeaders(): array
{
    if (function_exists('getallheaders')) {
        return getallheaders();
    }

    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
            $name = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$name] = $value;
        }
    }

    return $headers;
}

function getProvidedSecret(): ?string
{
    if (isset($_GET['secret']) && $_GET['secret'] !== '') {
        return $_GET['secret'];
    }

    if (!empty($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'];
    }

    $headers = getRequestHeaders();
    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'x-telegram-bot-api-secret-token') {
            return (string) $value;
        }
    }

    return null;
}

function getRequestIp(): ?string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }

    return $_SERVER['REMOTE_ADDR'] ?? null;
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
    ?string $phone,
    Logger $logger,
    Analytics $analytics,
    ?array $contact = null,
    ?string $fallbackName = null
): void {
    $phone = $phone ? normalisePhone($phone) : null;
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

        $telegram->sendMessage(
            $chatId,
            'Вы уже зарегестрированны на bunch! Используйте ссылку чтобы войти: https://bunchflowers.ru/login, чтобы восстановить пароль: https://bunchflowers.ru/recover. Введите код для восстановления:'
        );
        $telegram->sendMessage($chatId, formatTelegramCode($code));

        $logger->logEvent('TG_ALREADY_REGISTERED_CODE_SENT', [
            'user_id' => $existingByChat['id'],
            'chat_id' => $chatId,
        ]);
        $analytics->track('tg_code_sent', ['purpose' => 'recover', 'user_id' => $existingByChat['id']]);
        return;
    }

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
    $telegram->sendMessage($chatId, formatTelegramCode($code));

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
    $telegram->sendMessage($chatId, formatTelegramCode($code));
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

function formatTelegramCode(string $code): string
{
    $safe = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return "<code>{$safe}</code>";
}

/**
 * NEW: Пассивный сбор метаданных для админки (/bot/index.php)
 * - сохраняет последний апдейт
 * - ведет реестр всех увиденных chat_id и message_thread_id
 *
 * Никак не влияет на обработку сообщений в боте (всё в try/catch выше).
 */
function tg_admin_passive_capture(
    string $dataDir,
    array $update,
    array $message,
    int $chatId,
    $threadId,
    ?string $username,
    ?string $fromName,
    string $text
): void {
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }

    $chatType = $message['chat']['type'] ?? null;
    $chatTitle = $message['chat']['title'] ?? null;

    $lastPayload = [
        'received_at' => date('c'),
        'update_id' => $update['update_id'] ?? null,
        'chat_id' => $chatId,
        'message_thread_id' => ($threadId !== null && $threadId !== '') ? (int) $threadId : null,
        'chat_type' => $chatType,
        'chat_title' => $chatTitle,
        'from_username' => $username,
        'from_name' => $fromName,
        'text' => $text,
    ];

    // last update
    $lastFile = rtrim($dataDir, '/') . '/telegram_last_update.json';
    @file_put_contents($lastFile, json_encode($lastPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

    // registry
    $registryFile = rtrim($dataDir, '/') . '/telegram_chat_registry.json';
    $registry = [];

    if (file_exists($registryFile)) {
        $decoded = json_decode((string) file_get_contents($registryFile), true);
        if (is_array($decoded)) {
            $registry = $decoded;
        }
    }

    $key = (string) $chatId;
    if (!isset($registry[$key])) {
        $registry[$key] = [
            'chat_id' => $chatId,
            'chat_type' => $chatType,
            'chat_title' => $chatTitle,
            'first_seen' => date('c'),
            'last_seen' => date('c'),
            'threads' => [],
        ];
    }

    $registry[$key]['chat_type'] = $chatType ?? ($registry[$key]['chat_type'] ?? null);
    $registry[$key]['chat_title'] = $chatTitle ?? ($registry[$key]['chat_title'] ?? null);
    $registry[$key]['last_seen'] = date('c');

    if ($threadId !== null && $threadId !== '') {
        $tkey = (string) (int) $threadId;
        if (!isset($registry[$key]['threads'][$tkey])) {
            $registry[$key]['threads'][$tkey] = [
                'message_thread_id' => (int) $threadId,
                'first_seen' => date('c'),
                'last_seen' => date('c'),
            ];
        } else {
            $registry[$key]['threads'][$tkey]['last_seen'] = date('c');
        }
    }

    @file_put_contents($registryFile, json_encode($registry, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

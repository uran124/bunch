<?php
// app/controllers/AuthController.php

class AuthController extends Controller
{
    private User $userModel;
    private Logger $logger;
    private Analytics $analytics;
    private VerificationCode $verificationModel;
    private Setting $settings;
    private ?Telegram $telegram = null;
    private string $botUsername = '';
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct()
    {
        Session::start();
        $this->userModel = new User();
        $this->logger = new Logger();
        $this->analytics = new Analytics();
        $this->verificationModel = new VerificationCode();
        $this->settings = new Setting();
        $defaults = $this->settings->getTelegramDefaults();
        $botUsernameRaw = $this->settings->get(Setting::TG_BOT_USERNAME, $defaults[Setting::TG_BOT_USERNAME] ?? '');
        $this->botUsername = ltrim(trim($botUsernameRaw ?? ''), '@');

        $botToken = $this->settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');
        if ($botToken !== '') {
            $this->telegram = new Telegram($botToken);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Session::destroy();
        header('Location: /?page=login');
        exit;
    }

    public function login()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone'] ?? '');
            $pin = trim($_POST['pin'] ?? '');

            if ($phone === '' || $pin === '') {
                $errors[] = 'Укажите телефон и PIN.';
            } else {
                $normalizedPhone = $this->normalisePhone($phone);
                $user = $this->userModel->findByPhone($normalizedPhone);

                if (!$user) {
                    $errors[] = 'Пользователь не найден.';
                    $this->logger->logEvent('LOGIN_FAIL', ['phone' => $normalizedPhone, 'reason' => 'not_found']);
                    $this->analytics->track('login_fail', ['phone' => $normalizedPhone]);
                } else {
                    $locked = $this->isLocked($user);

                    if ($locked) {
                        $errors[] = 'Слишком много попыток. Попробуйте позже.';
                        $this->logger->logEvent('LOGIN_FAIL', ['phone' => $normalizedPhone, 'reason' => 'locked']);
                        $this->analytics->track('login_fail', ['phone' => $normalizedPhone, 'reason' => 'locked']);
                    } elseif (!password_verify($pin, $user['pin_hash'])) {
                        $updatedUser = $this->userModel->incrementFailedPinAttempts((int) $user['id']);
                        $errors[] = 'Неверный PIN.';
                        $this->logger->logEvent('LOGIN_FAIL', ['phone' => $normalizedPhone, 'attempts' => $updatedUser['failed_pin_attempts']]);
                        $this->analytics->track('login_fail', ['phone' => $normalizedPhone]);
                    } else {
                        $this->userModel->resetFailedAttempts((int) $user['id']);
                        Auth::login((int) $user['id']);
                        $this->logger->logEvent('LOGIN_SUCCESS', ['user_id' => $user['id'], 'phone' => $normalizedPhone]);
                        $this->analytics->track('login_success', ['user_id' => $user['id']]);

                        $this->redirectAfterAuth('/?page=account');
                    }
                }
            }
        }

        $this->render('login', [
            'errors' => $errors,
            'botUsername' => $this->botUsername,
        ]);
    }

    public function register()
    {
        $errors = [];
        $successMessage = '';
        $stage = 'code';
        $prefillName = '';
        $prefillPhone = '';
        $prefillEmail = '';
        $consentPersonalChecked = false;
        $consentMarketingChecked = false;

        $sessionVerification = Session::get('register_verification');
        if ($sessionVerification) {
            $stage = 'details';
            [$prefillName, $prefillPhone, $prefillEmail] = $this->getPrefillData($sessionVerification);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $step = $_POST['step'] ?? 'verify_code';

            if ($step === 'verify_code') {
                $code = trim($_POST['code'] ?? '');

                if (!preg_match('/^\d{5}$/', $code)) {
                    $errors[] = 'Введите 5-значный код из Telegram.';
                } else {
                    $verification = $this->verificationModel->consumeValidCode($code, 'register');

                    if (!$verification) {
                        $errors[] = 'Код не найден или устарел. Запросите новый в Telegram.';
                    } else {
                        $chatId = (int) $verification['chat_id'];
                        $existingByChat = $chatId ? $this->userModel->findByTelegramChatId($chatId) : null;

                        if ($existingByChat) {
                            Session::set('auth_notice', 'Такой пользователь уже есть. Мы выполнили вход и перенаправили вас на главную.');
                            $this->userModel->resetFailedAttempts((int) $existingByChat['id']);
                            Auth::login((int) $existingByChat['id']);
                            $this->logger->logEvent('LOGIN_SUCCESS', ['user_id' => $existingByChat['id'], 'phone' => $existingByChat['phone'] ?? null]);
                            $this->analytics->track('login_success', ['user_id' => $existingByChat['id'], 'source' => 'register_code']);

                            $this->redirectAfterAuth('/?page=home');
                        }

                        $data = [
                            'chat_id' => (int) $verification['chat_id'],
                            'username' => $verification['username'] ?? null,
                            'user_id' => $verification['user_id'] ? (int) $verification['user_id'] : null,
                            'phone' => $verification['phone'] ?? '',
                            'name' => $verification['name'] ?? '',
                        ];
                        Session::set('register_verification', $data);
                        $sessionVerification = $data;
                        $stage = 'details';
                        [$prefillName, $prefillPhone, $prefillEmail] = $this->getPrefillData($sessionVerification);
                        $successMessage = 'Код подтверждён. Заполните данные профиля.';
                    }
                }
            } elseif ($step === 'complete_registration') {
                $verification = Session::get('register_verification');

                if (!$verification) {
                    $errors[] = 'Сначала подтвердите код из Telegram.';
                    $stage = 'code';
                } else {
                    $name = trim($_POST['name'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $pin = $this->collectPin($_POST);
                    $consentPersonalChecked = isset($_POST['consent_personal']);
                    $consentMarketingChecked = isset($_POST['consent_marketing']);

                    if ($name === '') {
                        $errors[] = 'Укажите ваше имя.';
                    }

                    if ($phone === '') {
                        $errors[] = 'Укажите номер телефона.';
                    }

                    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Укажите корректный email или оставьте поле пустым.';
                    }

                    if (!preg_match('/^\d{4}$/', $pin)) {
                        $errors[] = 'PIN должен состоять из 4 цифр.';
                    }

                    if (!$consentPersonalChecked) {
                        $errors[] = 'Для регистрации нужно согласие на обработку персональных данных.';
                    }

                    if (empty($errors)) {
                        $normalizedPhone = $this->normalisePhone($phone);
                        $pinHash = password_hash($pin, PASSWORD_DEFAULT);
                        $chatId = (int) $verification['chat_id'];
                        $username = $verification['username'] ?? null;
                        $userId = $verification['user_id'] ?? null;

                        $existingUser = null;
                        if ($userId) {
                            $existingUser = $this->userModel->findById((int) $userId);
                        }

                        if (!$existingUser) {
                            $existingUser = $this->userModel->findByPhone($normalizedPhone);
                        }

                        if ($existingUser) {
                            $this->userModel->updateProfileAndPin((int) $existingUser['id'], $name, $normalizedPhone, $pinHash, $email, $chatId, $username);
                            $userId = (int) $existingUser['id'];
                            $this->logger->logEvent('WEB_USER_UPDATED', ['user_id' => $userId, 'phone' => $normalizedPhone]);
                        } else {
                            $userId = $this->userModel->create($normalizedPhone, $pinHash, $email, $chatId, $username, $name);
                            $this->logger->logEvent('WEB_USER_REGISTERED', ['user_id' => $userId, 'phone' => $normalizedPhone]);
                        }

                        $this->userModel->resetFailedAttempts($userId);
                        $this->analytics->track('registration_complete', ['user_id' => $userId]);
                        Session::remove('register_verification');

                        Auth::login($userId);
                        $this->redirectAfterAuth('/?page=account');
                    } else {
                        $stage = 'details';
                        $prefillName = $name;
                        $prefillPhone = $phone;
                        $prefillEmail = $email;
                    }
                }
            }
        }

        $this->render('register', [
            'errors' => $errors,
            'successMessage' => $successMessage,
            'botUsername' => $this->botUsername,
            'stage' => $stage,
            'prefillName' => $prefillName,
            'prefillPhone' => $prefillPhone,
            'prefillEmail' => $prefillEmail,
            'consentPersonalChecked' => $consentPersonalChecked,
            'consentMarketingChecked' => $consentMarketingChecked,
        ]);
    }

    public function recover()
    {
        $errors = [];
        $successMessage = '';
        $stage = 'code';
        $prefillPhone = '';

        if (Session::get('recover_verification')) {
            $stage = 'reset';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $step = $_POST['step'] ?? 'verify_code';

            if ($step === 'request_code') {
                $phone = trim($_POST['phone'] ?? '');

                if ($phone === '') {
                    $errors[] = 'Укажите номер телефона.';
                } else {
                    $normalizedPhone = $this->normalisePhone($phone);
                    $user = $this->userModel->findByPhone($normalizedPhone);

                    if (!$user) {
                        $errors[] = 'Пользователь с таким номером не найден.';
                    } elseif (empty($user['telegram_chat_id'])) {
                        $errors[] = 'Для этого номера нет привязки к Telegram. Напишите боту, чтобы связать аккаунт.';
                    } elseif (!$this->telegram) {
                        $errors[] = 'Отправка кода временно недоступна.';
                    } else {
                        $chatId = (int) $user['telegram_chat_id'];
                        $username = $user['telegram_username'] ?? null;

                        $code = $this->verificationModel->createCode(
                            $chatId,
                            'recover',
                            $normalizedPhone,
                            (int) $user['id'],
                            $username,
                            $user['name'] ?? null
                        );

                        $this->userModel->linkTelegram((int) $user['id'], $chatId, $username);

                        $this->logger->logEvent('WEB_RECOVERY_CODE_SENT', [
                            'user_id' => $user['id'],
                            'chat_id' => $chatId,
                        ]);
                        $this->analytics->track('tg_code_sent', ['purpose' => 'recover', 'user_id' => $user['id']]);

                        $safeCode = $this->formatTelegramCode($code);
                        $this->telegram->sendMessage($chatId, "Код для смены PIN: {$safeCode}\nВведите его на странице восстановления на сайте.", [
                            'parse_mode' => 'HTML',
                        ]);

                        $successMessage = 'Одноразовый код отправлен в Telegram.';
                    }
                }

                $prefillPhone = $phone;
            } elseif ($step === 'verify_code') {
                $code = trim($_POST['code'] ?? '');

                if (!preg_match('/^\d{5}$/', $code)) {
                    $errors[] = 'Введите 5-значный код из Telegram.';
                } else {
                    $verification = $this->verificationModel->consumeValidCode($code, 'recover');

                    if (!$verification || empty($verification['user_id'])) {
                        $errors[] = 'Код не найден или устарел. Запросите новый в Telegram.';
                    } else {
                        Session::set('recover_verification', [
                            'chat_id' => (int) $verification['chat_id'],
                            'user_id' => (int) $verification['user_id'],
                            'username' => $verification['username'] ?? null,
                        ]);
                        $stage = 'reset';
                        $successMessage = 'Код подтверждён. Задайте новый PIN.';
                    }
                }
            } elseif ($step === 'update_pin') {
                $verification = Session::get('recover_verification');

                if (!$verification) {
                    $errors[] = 'Сначала подтвердите код из Telegram.';
                    $stage = 'code';
                } else {
                    $pin = trim($_POST['pin'] ?? '');
                    $pinConfirm = trim($_POST['pin_confirm'] ?? '');

                    if (!preg_match('/^\d{4}$/', $pin)) {
                        $errors[] = 'PIN должен состоять из 4 цифр.';
                    }

                    if ($pin !== $pinConfirm) {
                        $errors[] = 'PIN и подтверждение не совпадают.';
                    }

                    if (empty($errors)) {
                        $user = $this->userModel->findById((int) $verification['user_id']);

                        if (!$user) {
                            $errors[] = 'Пользователь не найден.';
                            $stage = 'code';
                        } else {
                            $pinHash = password_hash($pin, PASSWORD_DEFAULT);
                            $this->userModel->updatePin((int) $verification['user_id'], $pinHash);
                            $this->userModel->resetFailedAttempts((int) $verification['user_id']);

                            if (!empty($verification['chat_id'])) {
                                $this->userModel->linkTelegram((int) $verification['user_id'], (int) $verification['chat_id'], $verification['username'] ?? null);
                            }

                            $this->logger->logEvent('PIN_RECOVERED', ['user_id' => $verification['user_id']]);
                            $this->analytics->track('pin_recovered', ['user_id' => $verification['user_id']]);

                            Session::remove('recover_verification');
                            Auth::login((int) $verification['user_id']);
                            Session::set('auth_notice', 'PIN обновлён, вход выполнен. Мы перенаправили вас на главную.');
                            $this->redirectAfterAuth('/?page=home');
                        }
                    } else {
                        $stage = 'reset';
                    }
                }
            }
        }

        $this->render('recover', [
            'errors' => $errors,
            'successMessage' => $successMessage,
            'botUsername' => $this->botUsername,
            'stage' => $stage,
            'prefillPhone' => $prefillPhone,
        ]);
    }

    private function isLocked(array $user): bool
    {
        if ((int) $user['failed_pin_attempts'] < self::MAX_FAILED_ATTEMPTS) {
            return false;
        }

        if (empty($user['last_failed_pin_at'])) {
            return false;
        }

        $lastFailed = new DateTime($user['last_failed_pin_at']);
        $diffMinutes = (new DateTime())->getTimestamp() - $lastFailed->getTimestamp();
        $diffMinutes = $diffMinutes / 60;

        return $diffMinutes < self::LOCK_MINUTES;
    }

    private function normalisePhone(string $phone): string
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

    private function getPrefillData(array $verification): array
    {
        $prefillName = trim($verification['name'] ?? '');
        $prefillPhone = trim($verification['phone'] ?? '');
        $prefillEmail = '';

        if (!empty($verification['user_id'])) {
            $user = $this->userModel->findById((int) $verification['user_id']);

            if ($user) {
                $prefillName = $user['name'] ?? $prefillName;
                $prefillPhone = $user['phone'] ?? $prefillPhone;
                $prefillEmail = $user['email'] ?? $prefillEmail;
            }
        }

        return [$prefillName, $prefillPhone, $prefillEmail];
    }

    private function collectPin(array $payload): string
    {
        $digits = [
            trim($payload['pin_1'] ?? ''),
            trim($payload['pin_2'] ?? ''),
            trim($payload['pin_3'] ?? ''),
            trim($payload['pin_4'] ?? ''),
        ];

        return implode('', $digits);
    }

    private function formatTelegramCode(string $code): string
    {
        $safe = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return "<code>{$safe}</code>";
    }

    private function redirectAfterAuth(string $fallback): void
    {
        $redirect = Session::get('auth_redirect');
        Session::remove('auth_redirect');

        if (!empty($redirect) && is_string($redirect) && str_starts_with($redirect, '/')) {
            header('Location: ' . $redirect);
            exit;
        }

        header('Location: ' . $fallback);
        exit;
    }
}

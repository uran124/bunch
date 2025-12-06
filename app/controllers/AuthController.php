<?php
// app/controllers/AuthController.php

class AuthController extends Controller
{
    private User $userModel;
    private Logger $logger;
    private Analytics $analytics;
    private ?Telegram $telegram = null;
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct()
    {
        Session::start();
        $this->userModel = new User();
        $this->logger = new Logger();
        $this->analytics = new Analytics();

        if (TG_BOT_TOKEN !== '') {
            $this->telegram = new Telegram(TG_BOT_TOKEN);
        }
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

                        header('Location: /?page=account');
                        exit;
                    }
                }
            }
        }

        $this->render('login', [
            'errors' => $errors,
            'botUsername' => TG_BOT_USERNAME,
        ]);
    }

    public function register()
    {
        $errors = [];
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone'] ?? '');

            if ($phone === '') {
                $errors[] = 'Укажите номер телефона.';
            } else {
                $normalizedPhone = $this->normalisePhone($phone);
                $user = $this->userModel->findByPhone($normalizedPhone);

                $pin = $this->generatePin();
                $pinHash = password_hash($pin, PASSWORD_DEFAULT);

                if ($user) {
                    $this->userModel->updatePin((int) $user['id'], $pinHash);
                    $user = $this->userModel->findById((int) $user['id']);
                } else {
                    $userId = $this->userModel->create($normalizedPhone, $pinHash);
                    $user = $this->userModel->findById($userId);
                    $this->logger->logEvent('WEB_USER_RESERVED', ['user_id' => $userId, 'phone' => $normalizedPhone]);
                }

                if ($user && $this->sendPinToTelegram($user, $pin, 'Регистрация')) {
                    $successMessage = 'PIN-код отправлен в Telegram. Откройте чат с ботом и проверьте сообщения.';
                } else {
                    $errors[] = 'Не удалось отправить PIN. Убедитесь, что вы запустили нашего Telegram-бота и поделились телефоном.';
                }
            }
        }

        $this->render('register', [
            'errors' => $errors,
            'successMessage' => $successMessage,
            'botUsername' => TG_BOT_USERNAME,
        ]);
    }

    public function recover()
    {
        $errors = [];
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone'] ?? '');

            if ($phone === '') {
                $errors[] = 'Укажите номер телефона.';
            } else {
                $normalizedPhone = $this->normalisePhone($phone);
                $user = $this->userModel->findByPhone($normalizedPhone);

                if (!$user) {
                    $errors[] = 'Пользователь не найден. Сначала зарегистрируйтесь через Telegram.';
                } else {
                    $pin = $this->generatePin();
                    $pinHash = password_hash($pin, PASSWORD_DEFAULT);
                    $this->userModel->updatePin((int) $user['id'], $pinHash);
                    $user = $this->userModel->findById((int) $user['id']);

                    if ($this->sendPinToTelegram($user, $pin, 'Восстановление PIN')) {
                        $successMessage = 'Мы отправили новый PIN в ваш Telegram. Проверьте диалог с ботом.';
                    } else {
                        $errors[] = 'Не удалось отправить PIN. Запустите бота и повторите попытку.';
                    }
                }
            }
        }

        $this->render('recover', [
            'errors' => $errors,
            'successMessage' => $successMessage,
            'botUsername' => TG_BOT_USERNAME,
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

    private function generatePin(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function sendPinToTelegram(array $user, string $pin, string $reason): bool
    {
        if (!$this->telegram || empty($user['telegram_chat_id'])) {
            return false;
        }

        $message = "Ваш PIN: {$pin}. Введите его на сайте Bunch flowers.";
        $this->telegram->sendMessage((int) $user['telegram_chat_id'], $message);

        $this->logger->logEvent('WEB_PIN_SENT', [
            'user_id' => $user['id'],
            'reason' => $reason,
        ]);
        $this->analytics->track('pin_sent', [
            'user_id' => $user['id'],
            'reason' => $reason,
        ]);

        return true;
    }
}

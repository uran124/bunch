<?php
// app/controllers/AuthController.php

class AuthController extends Controller
{
    private User $userModel;
    private Logger $logger;
    private Analytics $analytics;
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct()
    {
        Session::start();
        $this->userModel = new User();
        $this->logger = new Logger();
        $this->analytics = new Analytics();
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
                $user = $this->userModel->findByPhone($phone);

                if (!$user) {
                    $errors[] = 'Пользователь не найден.';
                    $this->logger->logEvent('LOGIN_FAIL', ['phone' => $phone, 'reason' => 'not_found']);
                    $this->analytics->track('login_fail', ['phone' => $phone]);
                } else {
                    $locked = $this->isLocked($user);

                    if ($locked) {
                        $errors[] = 'Слишком много попыток. Попробуйте позже.';
                        $this->logger->logEvent('LOGIN_FAIL', ['phone' => $phone, 'reason' => 'locked']);
                        $this->analytics->track('login_fail', ['phone' => $phone, 'reason' => 'locked']);
                    } elseif (!password_verify($pin, $user['pin_hash'])) {
                        $updatedUser = $this->userModel->incrementFailedPinAttempts((int) $user['id']);
                        $errors[] = 'Неверный PIN.';
                        $this->logger->logEvent('LOGIN_FAIL', ['phone' => $phone, 'attempts' => $updatedUser['failed_pin_attempts']]);
                        $this->analytics->track('login_fail', ['phone' => $phone]);
                    } else {
                        $this->userModel->resetFailedAttempts((int) $user['id']);
                        Auth::login((int) $user['id']);
                        $this->logger->logEvent('LOGIN_SUCCESS', ['user_id' => $user['id'], 'phone' => $phone]);
                        $this->analytics->track('login_success', ['user_id' => $user['id']]);

                        header('Location: /?page=account');
                        exit;
                    }
                }
            }
        }

        $this->render('login', [
            'errors' => $errors,
        ]);
    }

    public function register()
    {
        return $this->login();
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
}

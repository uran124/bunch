<?php
// app/models/Setting.php

class Setting extends Model
{
    public const TG_BOT_TOKEN = 'telegram_bot_token';
    public const TG_BOT_USERNAME = 'telegram_bot_username';
    public const TG_WEBHOOK_SECRET = 'telegram_webhook_secret';
    public const LOTTERY_FREE_MONTHLY_LIMIT = 'lottery_free_monthly_limit';
    public const ONLINE_PAYMENT_ENABLED = 'online_payment_enabled';
    public const ONLINE_PAYMENT_GATEWAY = 'online_payment_gateway';
    public const ROBOKASSA_MERCHANT_LOGIN = 'robokassa_merchant_login';
    public const ROBOKASSA_PASSWORD1 = 'robokassa_password1';
    public const ROBOKASSA_PASSWORD2 = 'robokassa_password2';
    public const ROBOKASSA_TEST_MODE = 'robokassa_test_mode';
    public const ROBOKASSA_RESULT_URL = 'robokassa_result_url';
    public const ROBOKASSA_SUCCESS_URL = 'robokassa_success_url';
    public const ROBOKASSA_FAIL_URL = 'robokassa_fail_url';
    public const ROBOKASSA_SIGNATURE_ALGORITHM = 'robokassa_signature_algorithm';
    public const FRONTPAD_SECRET = 'frontpad_secret';
    public const FRONTPAD_API_URL = 'frontpad_api_url';

    public function get(string $code, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return $default;
        }

        return $value !== null ? (string) $value : null;
    }

    public function set(string $code, ?string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (code, value, created_at, updated_at) VALUES (:code, :value, NOW(), NOW())
            ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()'
        );

        $stmt->execute([
            'code' => $code,
            'value' => $value,
        ]);
    }

    public function getTelegramDefaults(): array
    {
        return [
            self::TG_BOT_TOKEN => getenv('TG_BOT_TOKEN') ?: '',
            self::TG_BOT_USERNAME => getenv('TG_BOT_USERNAME') ?: '@bunchflowersBot',
            self::TG_WEBHOOK_SECRET => getenv('TG_WEBHOOK_SECRET') ?: 'bfb',
        ];
    }

    public function getLotteryDefaults(): array
    {
        return [
            self::LOTTERY_FREE_MONTHLY_LIMIT => getenv('LOTTERY_FREE_MONTHLY_LIMIT') ?: '3',
        ];
    }

    public function getPaymentDefaults(): array
    {
        return [
            self::ONLINE_PAYMENT_ENABLED => getenv('ONLINE_PAYMENT_ENABLED') ?: '1',
            self::ONLINE_PAYMENT_GATEWAY => getenv('ONLINE_PAYMENT_GATEWAY') ?: 'robokassa',
        ];
    }

    public function getRobokassaDefaults(): array
    {
        return [
            self::ROBOKASSA_MERCHANT_LOGIN => getenv('ROBOKASSA_MERCHANT_LOGIN') ?: '',
            self::ROBOKASSA_PASSWORD1 => getenv('ROBOKASSA_PASSWORD1') ?: '',
            self::ROBOKASSA_PASSWORD2 => getenv('ROBOKASSA_PASSWORD2') ?: '',
            self::ROBOKASSA_TEST_MODE => getenv('ROBOKASSA_TEST_MODE') ?: '0',
            self::ROBOKASSA_RESULT_URL => getenv('ROBOKASSA_RESULT_URL') ?: '',
            self::ROBOKASSA_SUCCESS_URL => getenv('ROBOKASSA_SUCCESS_URL') ?: '',
            self::ROBOKASSA_FAIL_URL => getenv('ROBOKASSA_FAIL_URL') ?: '',
            self::ROBOKASSA_SIGNATURE_ALGORITHM => getenv('ROBOKASSA_SIGNATURE_ALGORITHM') ?: 'md5',
        ];
    }

    public function getFrontpadDefaults(): array
    {
        return [
            self::FRONTPAD_SECRET => getenv('FRONTPAD_SECRET') ?: '',
            self::FRONTPAD_API_URL => getenv('FRONTPAD_API_URL') ?: 'https://app.frontpad.ru/api/index.php',
        ];
    }
}

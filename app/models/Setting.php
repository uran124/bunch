<?php
// app/models/Setting.php

class Setting extends Model
{
    public const TG_BOT_TOKEN = 'telegram_bot_token';
    public const TG_BOT_USERNAME = 'telegram_bot_username';
    public const TG_WEBHOOK_SECRET = 'telegram_webhook_secret';
    public const LOTTERY_FREE_MONTHLY_LIMIT = 'lottery_free_monthly_limit';

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
}

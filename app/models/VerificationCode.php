<?php
// app/models/VerificationCode.php

class VerificationCode extends Model
{
    private const LIFETIME_MINUTES = 15;

    public function createCode(
        int $chatId,
        string $purpose,
        ?string $phone = null,
        ?int $userId = null,
        ?string $username = null,
        ?string $name = null
    ): string {
        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME_MINUTES * 60);

        $stmt = $this->db->prepare(
            'INSERT INTO verification_codes (code, purpose, chat_id, phone, name, username, user_id, is_used, created_at, expires_at) ' .
            'VALUES (:code, :purpose, :chat_id, :phone, :name, :username, :user_id, 0, NOW(), :expires_at)'
        );

        $stmt->execute([
            'code' => $code,
            'purpose' => $purpose,
            'chat_id' => $chatId,
            'phone' => $phone,
            'name' => $name,
            'username' => $username,
            'user_id' => $userId,
            'expires_at' => $expiresAt,
        ]);

        return $code;
    }

    public function consumeValidCode(string $code, string $purpose): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM verification_codes WHERE code = :code AND purpose = :purpose AND is_used = 0 AND expires_at > NOW() ' .
            'ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([
            'code' => $code,
            'purpose' => $purpose,
        ]);

        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        $this->markUsed((int) $record['id']);

        return $record;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE verification_codes SET is_used = 1, used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

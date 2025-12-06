<?php
// app/models/User.php

class User extends Model
{
    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE phone = :phone LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByTelegramChatId(int $chatId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE telegram_chat_id = :chat_id LIMIT 1');
        $stmt->execute(['chat_id' => $chatId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $phone, string $pinHash, ?string $email = null, ?int $chatId = null, ?string $username = null, ?string $name = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (phone, name, email, pin_hash, pin_updated_at, telegram_chat_id, telegram_username, created_at, updated_at) VALUES (:phone, :name, :email, :pin_hash, :pin_updated_at, :chat_id, :username, NOW(), NOW())');
        $stmt->execute([
            'phone' => $phone,
            'name' => $name,
            'email' => $email,
            'pin_hash' => $pinHash,
            'pin_updated_at' => date('Y-m-d H:i:s'),
            'chat_id' => $chatId,
            'username' => $username,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updatePin(int $userId, string $pinHash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET pin_hash = :pin_hash, pin_updated_at = :updated_at, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'pin_hash' => $pinHash,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $userId,
        ]);
    }

    public function linkTelegram(int $userId, int $chatId, ?string $username): void
    {
        $stmt = $this->db->prepare('UPDATE users SET telegram_chat_id = :chat_id, telegram_username = :username, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'chat_id' => $chatId,
            'username' => $username,
            'id' => $userId,
        ]);
    }

    public function incrementFailedPinAttempts(int $userId): array
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_pin_attempts = failed_pin_attempts + 1, last_failed_pin_at = :failed_at WHERE id = :id');
        $stmt->execute([
            'failed_at' => date('Y-m-d H:i:s'),
            'id' => $userId,
        ]);

        return $this->findById($userId);
    }

    public function resetFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_pin_attempts = 0, last_failed_pin_at = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function updateProfileAndPin(
        int $userId,
        string $name,
        string $phone,
        string $pinHash,
        ?string $email = null,
        ?int $chatId = null,
        ?string $username = null
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = :name, phone = :phone, email = :email, pin_hash = :pin_hash, pin_updated_at = :updated_at, telegram_chat_id = :chat_id, telegram_username = :username, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'pin_hash' => $pinHash,
            'updated_at' => date('Y-m-d H:i:s'),
            'chat_id' => $chatId,
            'username' => $username,
            'id' => $userId,
        ]);
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}

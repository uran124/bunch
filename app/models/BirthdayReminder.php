<?php
// app/models/BirthdayReminder.php

class BirthdayReminder extends Model
{
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, recipient, occasion, reminder_date FROM birthday_reminders WHERE user_id = :user_id ORDER BY reminder_date ASC, recipient ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function createForUser(int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO birthday_reminders (user_id, recipient, occasion, reminder_date, created_at, updated_at)
            VALUES (:user_id, :recipient, :occasion, :reminder_date, NOW(), NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'recipient' => $data['recipient'],
            'occasion' => $data['occasion'],
            'reminder_date' => $data['reminder_date'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateForUser(int $userId, int $reminderId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE birthday_reminders SET recipient = :recipient, occasion = :occasion, reminder_date = :reminder_date, updated_at = NOW()
            WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'recipient' => $data['recipient'],
            'occasion' => $data['occasion'],
            'reminder_date' => $data['reminder_date'],
            'id' => $reminderId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteForUser(int $userId, int $reminderId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM birthday_reminders WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $reminderId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }
}

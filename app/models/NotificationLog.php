<?php
// app/models/NotificationLog.php

class NotificationLog extends Model
{
    private string $storageDir;

    public function __construct()
    {
        parent::__construct();
        $this->storageDir = dirname(__DIR__, 2) . '/storage/notifications';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function recordForChatId(int $chatId, string $text, array $meta = []): void
    {
        if ($chatId <= 0) {
            return;
        }

        $userModel = new User();
        $user = $userModel->findByTelegramChatId($chatId);
        if (!$user) {
            return;
        }

        $this->appendForUser((int) $user['id'], $text, $meta);
    }

    public function appendForUser(int $userId, string $text, array $meta = []): array
    {
        $text = trim($text);
        $entries = $this->readEntries($userId);
        $entry = [
            'id' => bin2hex(random_bytes(8)),
            'text' => $text,
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];

        if ($meta) {
            $entry['meta'] = $meta;
        }

        $entries[] = $entry;
        if (count($entries) > 200) {
            $entries = array_slice($entries, -200);
        }

        $this->writeEntries($userId, $entries);

        return $entry;
    }

    public function getForUser(int $userId, int $limit = 100): array
    {
        $entries = $this->readEntries($userId);
        if ($limit <= 0) {
            return $entries;
        }

        return array_slice($entries, -$limit);
    }

    private function readEntries(int $userId): array
    {
        $path = $this->getPath($userId);
        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    private function writeEntries(int $userId, array $entries): void
    {
        $path = $this->getPath($userId);
        file_put_contents($path, json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function getPath(int $userId): string
    {
        return $this->storageDir . '/notifications-' . $userId . '.json';
    }
}

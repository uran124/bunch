<?php
// app/models/SupportChat.php

class SupportChat
{
    private string $storageDir;
    private string $mapFile;

    public function __construct()
    {
        $this->storageDir = dirname(__DIR__, 2) . '/storage/support-chats';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        $this->mapFile = $this->storageDir . '/message-map.json';
    }

    public function appendMessage(int $userId, string $sender, string $text, array $meta = []): array
    {
        $text = trim($text);
        $messages = $this->readMessages($userId);
        $entry = [
            'id' => $this->generateMessageId(),
            'sender' => $sender,
            'text' => $text,
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];

        if ($meta) {
            $entry['meta'] = $meta;
        }

        $messages[] = $entry;
        if (count($messages) > 200) {
            $messages = array_slice($messages, -200);
        }

        $this->writeMessages($userId, $messages);

        return $entry;
    }

    public function listMessages(int $userId, ?string $afterId = null): array
    {
        $messages = $this->readMessages($userId);
        if ($afterId === null || $afterId === '') {
            return $messages;
        }

        $filtered = [];
        $found = false;
        foreach ($messages as $message) {
            if ($found) {
                $filtered[] = $message;
            }

            if (($message['id'] ?? '') === $afterId) {
                $found = true;
            }
        }

        return $found ? $filtered : $messages;
    }

    public function mapTelegramMessage(int $messageId, int $userId): void
    {
        $map = $this->readMessageMap();
        $map[(string) $messageId] = $userId;
        $this->writeMessageMap($map);
    }

    public function getUserIdForTelegramMessage(int $messageId): ?int
    {
        $map = $this->readMessageMap();
        $userId = $map[(string) $messageId] ?? null;

        return $userId ? (int) $userId : null;
    }

    private function readMessages(int $userId): array
    {
        $path = $this->getChatPath($userId);
        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    private function writeMessages(int $userId, array $messages): void
    {
        $path = $this->getChatPath($userId);
        file_put_contents($path, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function getChatPath(int $userId): string
    {
        return $this->storageDir . '/chat-' . $userId . '.json';
    }

    private function generateMessageId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function readMessageMap(): array
    {
        if (!file_exists($this->mapFile)) {
            return [];
        }

        $raw = file_get_contents($this->mapFile);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    private function writeMessageMap(array $map): void
    {
        file_put_contents($this->mapFile, json_encode($map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
}

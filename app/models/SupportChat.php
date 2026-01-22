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

    public function appendMessage(string $chatId, string $sender, string $text, array $meta = []): array
    {
        $text = trim($text);
        $messages = $this->readMessages($chatId);
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

        $this->writeMessages($chatId, $messages);

        return $entry;
    }

    public function listMessages(string $chatId, ?string $afterId = null): array
    {
        $messages = $this->readMessages($chatId);
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

    public function mapTelegramMessage(int $messageId, string $chatId): void
    {
        $map = $this->readMessageMap();
        $map[(string) $messageId] = $chatId;
        $this->writeMessageMap($map);
    }

    public function getChatIdForTelegramMessage(int $messageId): ?string
    {
        $map = $this->readMessageMap();
        $chatId = $map[(string) $messageId] ?? null;

        return $chatId ? (string) $chatId : null;
    }

    private function readMessages(string $chatId): array
    {
        $path = $this->getChatPath($chatId);
        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? $data : [];
    }

    private function writeMessages(string $chatId, array $messages): void
    {
        $path = $this->getChatPath($chatId);
        file_put_contents($path, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function getChatPath(string $chatId): string
    {
        $safeId = $this->normalizeChatId($chatId);
        return $this->storageDir . '/chat-' . $safeId . '.json';
    }

    private function generateMessageId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function normalizeChatId(string $chatId): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9_-]+/i', '-', $chatId));
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'guest';
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

<?php
// app/models/BroadcastMessage.php

class BroadcastMessage extends Model
{
    public function create(string $body, ?DateTimeInterface $sendAt, array $groupIds): int
    {
        $status = $sendAt && $sendAt > new DateTimeImmutable() ? 'scheduled' : 'sent';

        $stmt = $this->db->prepare(
            'INSERT INTO broadcast_messages (body, send_at, status, created_at, updated_at) VALUES (:body, :send_at, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'body' => $body,
            'send_at' => $sendAt ? $sendAt->format('Y-m-d H:i:s') : null,
            'status' => $status,
        ]);

        $messageId = (int) $this->db->lastInsertId();
        $this->attachGroups($messageId, $groupIds);

        return $messageId;
    }

    public function sendNow(int $messageId, string $body, array $groupIds): void
    {
        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $botToken = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');
        $chatIds = $this->collectRecipientChatIds($groupIds);
        $emails = $this->collectRecipientEmails($groupIds);

        if (empty($chatIds) && empty($emails)) {
            (new Logger('telegram_errors.log'))->logRaw(date('c') . ' no recipients for broadcast ' . $messageId);

            return;
        }

        if ($botToken === '' && !empty($chatIds)) {
            (new Logger('telegram_errors.log'))->logRaw(date('c') . ' missing TG_BOT_TOKEN for broadcast ' . $messageId);
        }

        if ($botToken !== '') {
            $telegram = new Telegram($botToken);

            foreach ($chatIds as $chatId) {
                $telegram->sendMessage($chatId, $body);
            }
        }

        if (!empty($emails)) {
            $mailer = new Mailer($this->buildMailConfig());
            foreach ($emails as $email) {
                $mailer->send($email, 'Bunch flowers — уведомление', $body);
            }
        }

        if ($botToken !== '' || !empty($emails)) {
            $this->markAsSent($messageId);
        }
    }

    public function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $total = (int) $this->db->query('SELECT COUNT(*) FROM broadcast_messages')->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $currentPage = min($page, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $stmt = $this->db->prepare(
            'SELECT id, body, send_at, status, created_at FROM broadcast_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $messages = [];
        foreach ($stmt->fetchAll() as $row) {
            $groupData = $this->getGroupsForMessage((int) $row['id']);
            $messages[] = [
                'id' => (int) $row['id'],
                'body' => $row['body'],
                'sendAt' => $row['send_at'] ?: '',
                'createdAt' => $row['created_at'],
                'status' => $this->resolveStatus($row['status'], $row['send_at']),
                'groups' => array_column($groupData, 'name'),
                'recipients' => $this->getRecipientsCount($groupData),
            ];
        }

        return [
            'messages' => $messages,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
        ];
    }

    private function resolveStatus(string $storedStatus, ?string $sendAt): string
    {
        if ($storedStatus === 'sent') {
            return 'sent';
        }

        if (!$sendAt) {
            return 'sent';
        }

        $sendDate = new DateTimeImmutable($sendAt);

        return $sendDate > new DateTimeImmutable() ? 'scheduled' : 'sent';
    }

    private function markAsSent(int $messageId): void
    {
        $stmt = $this->db->prepare('UPDATE broadcast_messages SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'status' => 'sent',
            'id' => $messageId,
        ]);
    }

    private function attachGroups(int $messageId, array $groupIds): void
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        if (empty($groupIds)) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO broadcast_message_groups (broadcast_id, group_id) VALUES (:broadcast_id, :group_id)'
        );

        foreach ($groupIds as $groupId) {
            $stmt->execute([
                'broadcast_id' => $messageId,
                'group_id' => $groupId,
            ]);
        }
    }

    private function getGroupsForMessage(int $messageId): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.id, g.name, g.is_system FROM broadcast_message_groups bmg INNER JOIN broadcast_groups g ON g.id = bmg.group_id WHERE bmg.broadcast_id = :id'
        );
        $stmt->execute(['id' => $messageId]);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'is_system' => (bool) $row['is_system'],
            ];
        }, $stmt->fetchAll());
    }

    private function getRecipientsCount(array $groups): int
    {
        if (empty($groups)) {
            return 0;
        }

        $hasSystemGroup = array_reduce($groups, static function (bool $carry, array $group): bool {
            return $carry || $group['is_system'] === true;
        }, false);

        if ($hasSystemGroup) {
            $stmt = $this->db->query('SELECT COUNT(*) FROM users WHERE is_active = 1');

            return (int) $stmt->fetchColumn();
        }

        $groupIds = array_map(static function (array $group): int {
            return (int) $group['id'];
        }, $groups);

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

        $sql = 'SELECT COUNT(DISTINCT u.id) AS cnt FROM users u INNER JOIN broadcast_group_users bgu ON bgu.user_id = u.id WHERE u.is_active = 1 AND bgu.group_id IN (' . $placeholders . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($groupIds);

        return (int) $stmt->fetchColumn();
    }

    private function collectRecipientChatIds(array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        if (empty($groupIds)) {
            return [];
        }

        if ($this->hasSystemGroup($groupIds)) {
            $stmt = $this->db->query('SELECT telegram_chat_id FROM users WHERE is_active = 1 AND telegram_chat_id IS NOT NULL');

            return array_values(array_unique(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

        $sql = 'SELECT DISTINCT u.telegram_chat_id FROM users u INNER JOIN broadcast_group_users bgu ON bgu.user_id = u.id WHERE u.is_active = 1 AND u.telegram_chat_id IS NOT NULL AND bgu.group_id IN (' . $placeholders . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($groupIds);

        return array_values(array_unique(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    private function hasSystemGroup(array $groupIds): bool
    {
        if (empty($groupIds)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM broadcast_groups WHERE is_system = 1 AND id IN (' . $placeholders . ')');
        $stmt->execute($groupIds);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function collectRecipientEmails(array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        if (empty($groupIds)) {
            return [];
        }

        if ($this->hasSystemGroup($groupIds)) {
            $stmt = $this->db->query('SELECT email FROM users WHERE is_active = 1 AND email IS NOT NULL AND email <> ""');
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $this->normalizeEmails($emails);
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $sql = 'SELECT DISTINCT u.email FROM users u INNER JOIN broadcast_group_users bgu ON bgu.user_id = u.id WHERE u.is_active = 1 AND u.email IS NOT NULL AND u.email <> "" AND bgu.group_id IN (' . $placeholders . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($groupIds);

        return $this->normalizeEmails($stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function normalizeEmails(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            $email = trim((string) $value);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $normalized[] = mb_strtolower($email);
        }

        return array_values(array_unique($normalized));
    }

    private function buildMailConfig(): array
    {
        $settings = new Setting();
        $defaults = $settings->getMailDefaults();

        return [
            'host' => $settings->get(Setting::SMTP_HOST, $defaults[Setting::SMTP_HOST] ?? ''),
            'port' => $settings->get(Setting::SMTP_PORT, $defaults[Setting::SMTP_PORT] ?? '587'),
            'encryption' => $settings->get(Setting::SMTP_ENCRYPTION, $defaults[Setting::SMTP_ENCRYPTION] ?? 'tls'),
            'username' => $settings->get(Setting::SMTP_USERNAME, $defaults[Setting::SMTP_USERNAME] ?? ''),
            'password' => $settings->get(Setting::SMTP_PASSWORD, $defaults[Setting::SMTP_PASSWORD] ?? ''),
            'from_email' => $settings->get(Setting::SMTP_FROM_EMAIL, $defaults[Setting::SMTP_FROM_EMAIL] ?? ''),
            'from_name' => $settings->get(Setting::SMTP_FROM_NAME, $defaults[Setting::SMTP_FROM_NAME] ?? 'Bunch flowers'),
            'allow_self_signed' => $settings->get(Setting::SMTP_ALLOW_SELF_SIGNED, $defaults[Setting::SMTP_ALLOW_SELF_SIGNED] ?? '0'),
        ];
    }
}

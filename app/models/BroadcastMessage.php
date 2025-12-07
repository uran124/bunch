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
}

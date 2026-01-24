<?php
// app/models/BroadcastGroup.php

class BroadcastGroup extends Model
{
    public function ensureSystemGroup(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM broadcast_groups WHERE is_system = 1 LIMIT 1');
        $stmt->execute();
        $system = $stmt->fetch();

        if ($system) {
            return $system;
        }

        $insert = $this->db->prepare(
            'INSERT INTO broadcast_groups (name, description, is_system, created_at, updated_at) VALUES (:name, :description, 1, NOW(), NOW())'
        );
        $insert->execute([
            'name' => 'Всем',
            'description' => 'Все активные пользователи',
        ]);

        $id = (int) $this->db->lastInsertId();

        return [
            'id' => $id,
            'name' => 'Всем',
            'description' => 'Все активные пользователи',
            'is_system' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function allWithCounts(): array
    {
        $sql = <<<'SQL'
SELECT
    g.id,
    g.name,
    g.description,
    g.is_system,
    COUNT(gu.user_id) AS members,
    g.created_at
FROM broadcast_groups g
LEFT JOIN broadcast_group_users gu ON gu.group_id = g.id
GROUP BY g.id, g.name, g.description, g.is_system, g.created_at
ORDER BY g.is_system DESC, g.created_at DESC
SQL;

        $stmt = $this->db->query($sql);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'is_system' => (bool) $row['is_system'],
                'members' => (int) $row['members'],
                'created_at' => $row['created_at'],
            ];
        }, $stmt->fetchAll());
    }

    public function editableWithCounts(): array
    {
        return array_values(array_filter($this->allWithCounts(), static function (array $group): bool {
            return $group['is_system'] === false;
        }));
    }

    public function membershipMap(): array
    {
        $stmt = $this->db->query('SELECT group_id, user_id FROM broadcast_group_users');
        $map = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $groupId = (int) $row['group_id'];
            $userId = (int) $row['user_id'];
            $map[$groupId] = $map[$groupId] ?? [];
            $map[$groupId][] = $userId;
        }

        return $map;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM broadcast_groups WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'is_system' => (bool) $row['is_system'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    public function create(string $name, ?string $description = null, bool $isSystem = false): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO broadcast_groups (name, description, is_system, created_at, updated_at) VALUES (:name, :description, :is_system, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'is_system' => $isSystem ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, ?string $description = null): void
    {
        $stmt = $this->db->prepare(
            'UPDATE broadcast_groups SET name = :name, description = :description, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function syncMembers(int $groupId, array $userIds): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        $this->db->beginTransaction();

        $delete = $this->db->prepare('DELETE FROM broadcast_group_users WHERE group_id = :group_id');
        $delete->execute(['group_id' => $groupId]);

        if (!empty($userIds)) {
            $insert = $this->db->prepare(
                'INSERT INTO broadcast_group_users (group_id, user_id, added_at) VALUES (:group_id, :user_id, NOW())'
            );

            foreach ($userIds as $userId) {
                $insert->execute([
                    'group_id' => $groupId,
                    'user_id' => $userId,
                ]);
            }
        }

        $this->db->commit();
    }

    public function addUserToGroup(int $groupId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO broadcast_group_users (group_id, user_id, added_at) VALUES (:group_id, :user_id, NOW())'
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
    }

    public function removeUserFromGroup(int $groupId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM broadcast_group_users WHERE group_id = :group_id AND user_id = :user_id'
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
    }
}

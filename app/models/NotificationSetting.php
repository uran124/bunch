<?php
// app/models/NotificationSetting.php

class NotificationSetting extends Model
{
    public function getSettingsForUser(int $userId, array $defaults = []): array
    {
        $settings = [];

        foreach ($defaults as $option) {
            $code = $option['code'] ?? null;
            if ($code) {
                $settings[$code] = (bool) ($option['default'] ?? true);
            }
        }

        $stmt = $this->db->prepare(
            'SELECT nt.code, uns.is_enabled FROM notification_types nt LEFT JOIN user_notification_settings uns ON uns.notification_type_id = nt.id AND uns.user_id = :user_id WHERE nt.is_active = 1 ORDER BY nt.sort_order ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        foreach ($stmt->fetchAll() as $row) {
            if ($row['is_enabled'] !== null) {
                $settings[$row['code']] = (bool) $row['is_enabled'];
            }
        }

        return $settings;
    }

    public function syncTypes(array $types): void
    {
        $sql = 'INSERT INTO notification_types (code, title, description, sort_order, channel, is_active) VALUES (:code, :title, :description, :sort_order, :channel, 1)
            ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), sort_order = VALUES(sort_order), channel = VALUES(channel), is_active = 1';

        $stmt = $this->db->prepare($sql);

        foreach ($types as $index => $type) {
            $stmt->execute([
                'code' => $type['code'],
                'title' => $type['label'] ?? $type['code'],
                'description' => $type['description'] ?? null,
                'sort_order' => $type['sort_order'] ?? (($index + 1) * 10),
                'channel' => $type['channel'] ?? 'push',
            ]);
        }
    }

    public function updateSettingsForUser(int $userId, array $preferences): void
    {
        if (empty($preferences)) {
            return;
        }

        $codes = array_keys($preferences);
        $typeMap = $this->getTypeIdMap($codes);

        $sql = 'INSERT INTO user_notification_settings (user_id, notification_type_id, is_enabled, preferred_channel, last_changed_at)
            VALUES (:user_id, :type_id, :is_enabled, :preferred_channel, NOW())
            ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled), preferred_channel = VALUES(preferred_channel), last_changed_at = VALUES(last_changed_at)';

        $stmt = $this->db->prepare($sql);

        foreach ($preferences as $code => $enabled) {
            if (!isset($typeMap[$code])) {
                continue;
            }

            $stmt->execute([
                'user_id' => $userId,
                'type_id' => $typeMap[$code],
                'is_enabled' => $enabled ? 1 : 0,
                'preferred_channel' => 'push',
            ]);
        }
    }

    private function getTypeIdMap(array $codes): array
    {
        if (!$codes) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($codes), '?'));
        $stmt = $this->db->prepare("SELECT id, code FROM notification_types WHERE code IN ($placeholders)");
        $stmt->execute($codes);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['code']] = (int) $row['id'];
        }

        return $map;
    }
}

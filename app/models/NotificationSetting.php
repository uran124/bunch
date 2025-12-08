<?php
// app/models/NotificationSetting.php

class NotificationSetting extends Model
{
    public function getSettingsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT nt.code, COALESCE(uns.is_enabled, 0) AS is_enabled FROM notification_types nt LEFT JOIN user_notification_settings uns ON uns.notification_type_id = nt.id AND uns.user_id = :user_id WHERE nt.is_active = 1 ORDER BY nt.sort_order ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['code']] = (bool) $row['is_enabled'];
        }

        return $settings;
    }
}

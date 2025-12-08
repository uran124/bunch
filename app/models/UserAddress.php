<?php
// app/models/UserAddress.php

class UserAddress extends Model
{
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_primary DESC, created_at ASC');
        $stmt->execute(['user_id' => $userId]);

        $rows = $stmt->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'label' => $row['label'] ?: 'Адрес',
                'address' => self::formatAddress($row),
                'is_primary' => (bool) $row['is_primary'],
                'raw' => $row,
            ];
        }, $rows);
    }

    public static function formatAddress(array $row): string
    {
        $parts = array_filter([
            $row['city'] ?? null,
            $row['street'] ?? null,
            isset($row['house']) ? 'д. ' . $row['house'] : null,
            isset($row['building']) && $row['building'] !== '' ? 'к. ' . $row['building'] : null,
            isset($row['apartment']) && $row['apartment'] !== '' ? 'кв. ' . $row['apartment'] : null,
        ]);

        return implode(', ', $parts);
    }
}

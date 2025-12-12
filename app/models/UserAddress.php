<?php
// app/models/UserAddress.php

class UserAddress extends Model
{
    public function createForUser(int $userId, array $data): int
    {
        $addressCountStmt = $this->db->prepare('SELECT COUNT(*) FROM user_addresses WHERE user_id = :user_id AND is_archived = 0');
        $addressCountStmt->execute(['user_id' => $userId]);
        $isDefault = $addressCountStmt->fetchColumn() == 0 ? 1 : 0;

        $sql = 'INSERT INTO user_addresses (
            user_id, is_active, is_archived, last_used_at,
            settlement, street, house, apartment, address_text,
            lat, lon, location_source, geo_quality,
            zone_id, zone_calculated_at, zone_version,
            recipient_name, recipient_phone,
            entrance, floor, intercom, delivery_comment,
            label, is_default,
            fias_id, kladr_id, postal_code, region, city_district,
            last_delivery_price_hint
        ) VALUES (
            :user_id, 1, 0, NOW(),
            :settlement, :street, :house, :apartment, :address_text,
            :lat, :lon, :location_source, :geo_quality,
            :zone_id, :zone_calculated_at, :zone_version,
            :recipient_name, :recipient_phone,
            :entrance, :floor, :intercom, :delivery_comment,
            :label, :is_default,
            :fias_id, :kladr_id, :postal_code, :region, :city_district,
            :last_delivery_price_hint
        )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'settlement' => $this->emptyToNull($data['settlement'] ?? null),
            'street' => $this->emptyToNull($data['street'] ?? null),
            'house' => $this->emptyToNull($data['house'] ?? null),
            'apartment' => $this->emptyToNull($data['apartment'] ?? null),
            'address_text' => $this->emptyToNull($data['address_text'] ?? null),
            'lat' => $data['lat'] ?? null,
            'lon' => $data['lon'] ?? null,
            'location_source' => $this->emptyToNull($data['location_source'] ?? null),
            'geo_quality' => $this->emptyToNull($data['geo_quality'] ?? null),
            'zone_id' => isset($data['zone_id']) ? (int) $data['zone_id'] : null,
            'zone_calculated_at' => $this->emptyToNull($data['zone_calculated_at'] ?? null),
            'zone_version' => $this->emptyToNull($data['zone_version'] ?? null),
            'recipient_name' => $this->emptyToNull($data['recipient_name'] ?? null),
            'recipient_phone' => $this->emptyToNull($data['recipient_phone'] ?? null),
            'entrance' => $this->emptyToNull($data['entrance'] ?? null),
            'floor' => $this->emptyToNull($data['floor'] ?? null),
            'intercom' => $this->emptyToNull($data['intercom'] ?? null),
            'delivery_comment' => $this->emptyToNull($data['delivery_comment'] ?? null),
            'label' => $this->emptyToNull($data['label'] ?? null),
            'is_default' => $isDefault,
            'fias_id' => $this->emptyToNull($data['fias_id'] ?? null),
            'kladr_id' => $this->emptyToNull($data['kladr_id'] ?? null),
            'postal_code' => $this->emptyToNull($data['postal_code'] ?? null),
            'region' => $this->emptyToNull($data['region'] ?? null),
            'city_district' => $this->emptyToNull($data['city_district'] ?? null),
            'last_delivery_price_hint' => isset($data['last_delivery_price_hint']) ? (float) $data['last_delivery_price_hint'] : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function touchUsage(int $userId, int $addressId): void
    {
        $stmt = $this->db->prepare('UPDATE user_addresses SET last_used_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $addressId, 'user_id' => $userId]);
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id AND is_archived = 0 ORDER BY is_default DESC, COALESCE(last_used_at, created_at) DESC');
        $stmt->execute(['user_id' => $userId]);

        $rows = $stmt->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'label' => $row['label'] ?: 'Адрес',
                'address' => self::formatAddress($row),
                'is_primary' => (bool) ($row['is_default'] ?? false),
                'raw' => $row,
            ];
        }, $rows);
    }

    public static function formatAddress(array $row): string
    {
        if (!empty($row['address_text'])) {
            return $row['address_text'];
        }

        $parts = array_filter([
            $row['settlement'] ?? null,
            $row['street'] ?? null,
            isset($row['house']) ? 'д. ' . $row['house'] : null,
            isset($row['apartment']) && $row['apartment'] !== '' ? 'кв. ' . $row['apartment'] : null,
        ]);

        return $parts ? implode(', ', $parts) : 'Адрес не указан';
    }

    private function emptyToNull(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}

<?php
// app/models/DeliveryZone.php

class DeliveryZone extends Model
{
    private const META_ROW_ID = 1;
    private const DEFAULT_SHOP = 'bunch';

    public function getPricingVersion(): int
    {
        try {
            $stmt = $this->db->prepare('SELECT version FROM delivery_pricing_meta WHERE id = :id');
            $stmt->execute(['id' => self::META_ROW_ID]);
            $version = $stmt->fetchColumn();

            return $version !== false ? (int) $version : 1;
        } catch (Throwable $e) {
            return 1;
        }
    }

    public function getZones(bool $onlyActive = false, bool $orderByPriority = true): array
    {
        try {
            $sql = 'SELECT * FROM delivery_zones';
            $params = [];

            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }

            $sql .= $orderByPriority ? ' ORDER BY priority DESC, id ASC' : ' ORDER BY id ASC';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            if (!$rows) {
                return $this->getDefaultZones();
            }

            return array_map([$this, 'hydrateZoneRow'], $rows);
        } catch (Throwable $e) {
            return $this->getDefaultZones();
        }
    }

    public function saveZones(array $zones): int
    {
        $normalized = array_map([$this, 'normalizeZonePayload'], $zones);

        $this->db->beginTransaction();

        try {
            $seenIds = [];

            foreach ($normalized as $zone) {
                if ($zone['id'] !== null) {
                    $stmt = $this->db->prepare('UPDATE delivery_zones SET name = :name, price = :price, priority = :priority, color = :color, is_active = :is_active, polygon = :polygon WHERE id = :id');
                    $stmt->execute([
                        'name' => $zone['name'],
                        'price' => $zone['price'],
                        'priority' => $zone['priority'],
                        'color' => $zone['color'],
                        'is_active' => $zone['is_active'],
                        'polygon' => $zone['polygon'],
                        'id' => $zone['id'],
                    ]);
                    $seenIds[] = $zone['id'];
                } else {
                    $stmt = $this->db->prepare('INSERT INTO delivery_zones (name, price, priority, color, is_active, polygon) VALUES (:name, :price, :priority, :color, :is_active, :polygon)');
                    $stmt->execute([
                        'name' => $zone['name'],
                        'price' => $zone['price'],
                        'priority' => $zone['priority'],
                        'color' => $zone['color'],
                        'is_active' => $zone['is_active'],
                        'polygon' => $zone['polygon'],
                    ]);
                    $seenIds[] = (int) $this->db->lastInsertId();
                }
            }

            if (!empty($seenIds)) {
                $placeholders = implode(',', array_fill(0, count($seenIds), '?'));
                $deleteStmt = $this->db->prepare("DELETE FROM delivery_zones WHERE id NOT IN ({$placeholders})");
                $deleteStmt->execute($seenIds);
            } else {
                $this->db->exec('TRUNCATE TABLE delivery_zones');
            }

            $version = $this->incrementVersion();
            $this->db->commit();

            return $version;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTestAddresses(): array
    {
        return [
            [
                'label' => 'Красноярск, ул. Молокова, 1',
                'match' => 'молокова 1',
                'coords' => [92.8765, 56.0262],
            ],
            [
                'label' => 'Красноярск, ул. Весны, 14',
                'match' => 'весны 14',
                'coords' => [92.9008, 56.0258],
            ],
            [
                'label' => 'Красноярск, пр. Мира, 49',
                'match' => 'мира 49',
                'coords' => [92.8526, 56.0097],
            ],
            [
                'label' => 'Красноярск, ул. Карла Маркса, 93',
                'match' => 'карла маркса 93',
                'coords' => [92.8962, 56.0109],
            ],
        ];
    }

    private function incrementVersion(): int
    {
        $stmt = $this->db->prepare('INSERT INTO delivery_pricing_meta (id, shop, version) VALUES (:id, :shop, 1)
            ON DUPLICATE KEY UPDATE version = version + 1, updated_at = CURRENT_TIMESTAMP');
        $stmt->execute([
            'id' => self::META_ROW_ID,
            'shop' => self::DEFAULT_SHOP,
        ]);

        $versionStmt = $this->db->prepare('SELECT version FROM delivery_pricing_meta WHERE id = :id');
        $versionStmt->execute(['id' => self::META_ROW_ID]);
        $version = $versionStmt->fetchColumn();

        return $version !== false ? (int) $version : 1;
    }

    private function normalizeZonePayload(array $zone): array
    {
        $polygon = $this->normalizePolygon($zone['polygon'] ?? []);

        if (count($polygon) < 3) {
            throw new InvalidArgumentException('Полигон должен содержать минимум три точки.');
        }

        $id = isset($zone['id']) && $zone['id'] !== '' ? (int) $zone['id'] : null;
        $name = trim((string) ($zone['name'] ?? 'Зона доставки'));

        return [
            'id' => $id,
            'name' => $name !== '' ? $name : 'Зона доставки',
            'price' => round((float) ($zone['price'] ?? 0), 2),
            'priority' => (int) ($zone['priority'] ?? 0),
            'color' => $zone['color'] ?? '#f43f5e',
            'is_active' => !empty($zone['active']) ? 1 : 0,
            'polygon' => json_encode($polygon, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
        ];
    }

    private function normalizePolygon(array $polygon): array
    {
        $normalized = [];

        foreach ($polygon as $point) {
            if (!is_array($point) || count($point) < 2) {
                continue;
            }

            $lon = (float) $point[0];
            $lat = (float) $point[1];

            $normalized[] = [$lon, $lat];
        }

        return $normalized;
    }

    private function hydrateZoneRow(array $row): array
    {
        $decoded = json_decode($row['polygon'] ?? '[]', true) ?: [];
        $polygon = array_map(static function ($point): array {
            return [
                isset($point[0]) ? (float) $point[0] : 0.0,
                isset($point[1]) ? (float) $point[1] : 0.0,
            ];
        }, $decoded);

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'priority' => (int) $row['priority'],
            'color' => $row['color'],
            'active' => (bool) $row['is_active'],
            'polygon' => $polygon,
        ];
    }

    private function getDefaultZones(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Центр',
                'price' => 290,
                'priority' => 100,
                'color' => '#f43f5e',
                'active' => true,
                'polygon' => [
                    [92.8976, 56.0184],
                    [92.9206, 56.0184],
                    [92.9212, 56.0044],
                    [92.8981, 56.0038],
                    [92.8976, 56.0184],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Правый берег',
                'price' => 390,
                'priority' => 90,
                'color' => '#06b6d4',
                'active' => true,
                'polygon' => [
                    [92.8305, 56.0312],
                    [92.8635, 56.0312],
                    [92.8644, 56.0152],
                    [92.8320, 56.0143],
                    [92.8305, 56.0312],
                ],
            ],
            [
                'id' => 3,
                'name' => 'Север',
                'price' => 460,
                'priority' => 80,
                'color' => '#a855f7',
                'active' => true,
                'polygon' => [
                    [92.8855, 56.0402],
                    [92.9221, 56.0402],
                    [92.9227, 56.0286],
                    [92.8885, 56.0280],
                    [92.8855, 56.0402],
                ],
            ],
        ];
    }
}

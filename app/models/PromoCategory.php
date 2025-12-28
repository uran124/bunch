<?php
// app/models/PromoCategory.php

class PromoCategory extends Model
{
    protected string $table = 'promo_categories';

    private array $defaults = [
        [
            'code' => 'promo',
            'title' => 'Разовые акции',
            'is_active' => 1,
            'sort_order' => 10,
        ],
        [
            'code' => 'auction',
            'title' => 'Аукционы',
            'is_active' => 0,
            'sort_order' => 20,
        ],
        [
            'code' => 'lottery',
            'title' => 'Лотереи',
            'is_active' => 0,
            'sort_order' => 30,
        ],
    ];

    public function getAll(): array
    {
        $this->ensureDefaults();
        $stmt = $this->db->query("SELECT code, title, is_active, sort_order FROM {$this->table} ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function getMap(): array
    {
        $map = [];
        foreach ($this->getAll() as $row) {
            $map[$row['code']] = $row;
        }
        return $map;
    }

    public function updateStatuses(array $statusMap): void
    {
        $this->ensureDefaults();
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = :is_active WHERE code = :code");
        foreach ($this->defaults as $default) {
            $code = $default['code'];
            $isActive = isset($statusMap[$code]) ? (int) $statusMap[$code] : 0;
            $stmt->execute([
                'code' => $code,
                'is_active' => $isActive,
            ]);
        }
    }

    private function ensureDefaults(): void
    {
        $stmt = $this->db->query("SELECT code FROM {$this->table}");
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $existingMap = array_flip($existing);

        $insert = $this->db->prepare(
            "INSERT INTO {$this->table} (code, title, is_active, sort_order) VALUES (:code, :title, :is_active, :sort_order)"
        );

        foreach ($this->defaults as $default) {
            if (isset($existingMap[$default['code']])) {
                continue;
            }

            $insert->execute([
                'code' => $default['code'],
                'title' => $default['title'],
                'is_active' => $default['is_active'],
                'sort_order' => $default['sort_order'],
            ]);
        }
    }
}

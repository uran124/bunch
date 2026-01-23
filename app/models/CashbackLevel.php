<?php
// app/models/CashbackLevel.php

class CashbackLevel extends Model
{
    protected string $table = 'cashback_levels';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, created_at DESC");
        return $stmt->fetchAll();
    }

    public function save(array $payload): int
    {
        $id = (int) ($payload['id'] ?? 0);
        $name = $payload['name'] ?? '';
        $single = $payload['percent_single'] ?? 0;
        $pack = $payload['percent_pack'] ?? 0;
        $box = $payload['percent_box'] ?? 0;
        $promo = $payload['percent_promo'] ?? 0;
        $sortOrder = $payload['sort_order'] ?? 0;

        if ($id > 0) {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                SET name = :name,
                    percent_single = :percent_single,
                    percent_pack = :percent_pack,
                    percent_box = :percent_box,
                    percent_promo = :percent_promo,
                    sort_order = :sort_order,
                    updated_at = NOW()
                WHERE id = :id"
            );
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'percent_single' => $single,
                'percent_pack' => $pack,
                'percent_box' => $box,
                'percent_promo' => $promo,
                'sort_order' => $sortOrder,
            ]);

            return $id;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
                (name, percent_single, percent_pack, percent_box, percent_promo, sort_order, created_at, updated_at)
            VALUES
                (:name, :percent_single, :percent_pack, :percent_box, :percent_promo, :sort_order, NOW(), NOW())"
        );
        $stmt->execute([
            'name' => $name,
            'percent_single' => $single,
            'percent_pack' => $pack,
            'percent_box' => $box,
            'percent_promo' => $promo,
            'sort_order' => $sortOrder,
        ]);

        return (int) $this->db->lastInsertId();
    }
}

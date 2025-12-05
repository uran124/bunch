<?php
// app/models/Product.php

class Product extends Model
{
    protected string $table = 'products';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

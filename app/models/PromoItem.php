<?php
// app/models/PromoItem.php

class PromoItem extends Model
{
    protected string $table = 'promo_items';

    public function countActive(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE is_active = 1");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getAdminList(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getActiveList(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $payload): int
    {
        $slug = $this->generateSlug($payload['title']);

        $sql = "INSERT INTO {$this->table} (title, slug, description, price, quantity, ends_at, label, photo_url, is_active) VALUES (:title, :slug, :description, :price, :quantity, :ends_at, :label, :photo_url, :is_active)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $slug,
            'description' => $payload['description'],
            'price' => $payload['price'],
            'quantity' => $payload['quantity'],
            'ends_at' => $payload['ends_at'],
            'label' => $payload['label'],
            'photo_url' => $payload['photo_url'],
            'is_active' => $payload['is_active'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9а-яё]+/ui', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'promo';
        }

        $base = $slug;
        $index = 1;

        while ($this->slugExists($slug)) {
            $index++;
            $slug = $base . '-' . $index;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return (bool) $stmt->fetchColumn();
    }
}

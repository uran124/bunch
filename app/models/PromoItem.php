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
        $stmt = $this->db->prepare(
            "SELECT pi.*,
                COALESCE((
                    SELECT SUM(oi.qty)
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    WHERE oi.product_id = pi.product_id
                      AND o.status IN ('confirmed', 'assembled', 'delivering', 'delivered')
                ), 0) AS accepted_qty
            FROM {$this->table} pi
            WHERE pi.is_active = 1
              AND (pi.ends_at IS NULL OR pi.ends_at > NOW())
              AND (pi.quantity IS NULL OR pi.quantity > 0)
            ORDER BY pi.created_at DESC"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_values(array_filter(array_map(static function (array $row): array {
            $quantity = $row['quantity'] !== null ? (int) $row['quantity'] : null;
            $acceptedQty = (int) ($row['accepted_qty'] ?? 0);
            $remaining = $quantity !== null ? max(0, $quantity - $acceptedQty) : null;
            $row['accepted_qty'] = $acceptedQty;
            $row['remaining_qty'] = $remaining;
            return $row;
        }, $rows), static function (array $row): bool {
            if ($row['quantity'] === null) {
                return true;
            }
            return (int) ($row['remaining_qty'] ?? 0) > 0;
        }));
    }

    public function create(array $payload): int
    {
        $slug = $this->generateSlug($payload['title']);

        $sql = "INSERT INTO {$this->table} (product_id, title, slug, description, base_price, price, quantity, ends_at, label, photo_url, is_active) VALUES (:product_id, :title, :slug, :description, :base_price, :price, :quantity, :ends_at, :label, :photo_url, :is_active)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $payload['product_id'] ?? null,
            'title' => $payload['title'],
            'slug' => $slug,
            'description' => $payload['description'],
            'base_price' => $payload['base_price'],
            'price' => $payload['price'],
            'quantity' => $payload['quantity'],
            'ends_at' => $payload['ends_at'],
            'label' => $payload['label'],
            'photo_url' => $payload['photo_url'],
            'is_active' => $payload['is_active'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateItem(int $id, array $payload): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET title = :title,
                description = :description,
                base_price = :base_price,
                price = :price,
                quantity = :quantity,
                ends_at = :ends_at,
                label = :label,
                photo_url = :photo_url,
                is_active = :is_active
            WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'base_price' => $payload['base_price'],
            'price' => $payload['price'],
            'quantity' => $payload['quantity'],
            'ends_at' => $payload['ends_at'],
            'label' => $payload['label'],
            'photo_url' => $payload['photo_url'],
            'is_active' => $payload['is_active'],
        ]);
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

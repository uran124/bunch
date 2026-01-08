<?php
// app/models/Product.php

class Product extends Model
{
    protected string $table = 'products';

    public function getAll(): array
    {
        $sql = "SELECT p.*, s.variety AS supply_variety, s.flower_name AS supply_flower_name, s.country AS supply_country, s.stem_height_cm AS supply_stem_height_cm FROM {$this->table} p LEFT JOIN supplies s ON p.supply_id = s.id WHERE p.is_active = 1 ORDER BY p.sort_order ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActiveByCategory(string $category): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE is_active = 1 AND category = :category ORDER BY sort_order ASC"
        );
        $stmt->execute(['category' => $category]);

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAdminList(): array
    {
        $sql = "SELECT p.*, s.flower_name, s.variety, s.country AS supply_country, s.stem_height_cm AS supply_height, s.stem_weight_g AS supply_weight, s.photo_url AS supply_photo, s.id AS supply_id FROM {$this->table} p LEFT JOIN supplies s ON p.supply_id = s.id ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql);
        $products = $stmt->fetchAll();

        foreach ($products as &$product) {
            $product['price_tiers'] = $this->getPriceTiers((int) $product['id']);
            $product['attribute_ids'] = $this->getAttributeIds((int) $product['id']);
        }

        return $products;
    }

    public function getWithRelations(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['price_tiers'] = $this->getPriceTiers($id);
        $row['attribute_ids'] = $this->getAttributeIds($id);

        return $row;
    }

    public function createFromSupply(array $payload): int
    {
        $slug = $this->generateSlug($payload['name']);

        $sql = "INSERT INTO {$this->table} (supply_id, name, slug, description, price, article, photo_url, stem_height_cm, stem_weight_g, country, category, is_base, is_active, sort_order) VALUES (:supply_id, :name, :slug, :description, :price, :article, :photo_url, :stem_height_cm, :stem_weight_g, :country, :category, :is_base, :is_active, :sort_order)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'supply_id' => $payload['supply_id'],
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'],
            'price' => $payload['price'],
            'article' => $payload['article'],
            'photo_url' => $payload['photo_url'],
            'stem_height_cm' => $payload['stem_height_cm'],
            'stem_weight_g' => $payload['stem_weight_g'],
            'country' => $payload['country'],
            'category' => $payload['category'] ?? 'main',
            'is_base' => $payload['is_base'],
            'is_active' => $payload['is_active'],
            'sort_order' => $payload['sort_order'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createCustom(array $payload): int
    {
        $slug = $this->generateSlug($payload['name']);

        $sql = "INSERT INTO {$this->table} (name, slug, description, price, article, photo_url, category, product_type, is_base, is_active, sort_order) VALUES (:name, :slug, :description, :price, :article, :photo_url, :category, :product_type, :is_base, :is_active, :sort_order)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'],
            'price' => $payload['price'],
            'article' => $payload['article'],
            'photo_url' => $payload['photo_url'],
            'category' => $payload['category'] ?? 'main',
            'product_type' => $payload['product_type'] ?? 'regular',
            'is_base' => $payload['is_base'] ?? 0,
            'is_active' => $payload['is_active'] ?? 1,
            'sort_order' => $payload['sort_order'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9а-яё]+/ui', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'product';
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

    public function updateProduct(int $id, array $payload): void
    {
        $sql = "UPDATE {$this->table} SET supply_id = :supply_id, name = :name, description = :description, price = :price, article = :article, photo_url = :photo_url, stem_height_cm = :stem_height_cm, stem_weight_g = :stem_weight_g, country = :country, category = :category, is_active = :is_active WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'supply_id' => $payload['supply_id'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'price' => $payload['price'],
            'article' => $payload['article'],
            'photo_url' => $payload['photo_url'],
            'stem_height_cm' => $payload['stem_height_cm'],
            'stem_weight_g' => $payload['stem_weight_g'],
            'country' => $payload['country'],
            'category' => $payload['category'] ?? 'main',
            'is_active' => $payload['is_active'],
            'id' => $id,
        ]);
    }

    public function deleteProduct(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function hasBlockingRelations(int $id): bool
    {
        $checks = [
            'SELECT 1 FROM cart_items WHERE product_id = :id LIMIT 1',
            'SELECT 1 FROM order_items WHERE product_id = :id LIMIT 1',
            'SELECT 1 FROM subscriptions WHERE product_id = :id LIMIT 1',
        ];

        foreach ($checks as $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            if ($stmt->fetchColumn()) {
                return true;
            }
        }

        return false;
    }

    public function getBlockingRelations(int $id): array
    {
        $ordersStmt = $this->db->prepare(
            'SELECT DISTINCT o.id
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE oi.product_id = :id
            ORDER BY o.created_at DESC
            LIMIT 10'
        );
        $ordersStmt->execute(['id' => $id]);
        $orders = $ordersStmt->fetchAll();
        $orders = array_map(function (array $order): array {
            $orderId = (int) ($order['id'] ?? 0);
            return [
                'id' => $orderId,
                'number' => 'B-' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT),
            ];
        }, $orders);

        $subscriptionsStmt = $this->db->prepare(
            'SELECT s.id, s.user_id, s.status, u.name, u.phone
            FROM subscriptions s
            INNER JOIN users u ON u.id = s.user_id
            WHERE s.product_id = :id
            ORDER BY s.created_at DESC
            LIMIT 10'
        );
        $subscriptionsStmt->execute(['id' => $id]);
        $subscriptions = $subscriptionsStmt->fetchAll();

        $cartsStmt = $this->db->prepare(
            'SELECT DISTINCT c.id, c.user_id, u.name, u.phone
            FROM cart_items ci
            INNER JOIN carts c ON c.id = ci.cart_id
            LEFT JOIN users u ON u.id = c.user_id
            WHERE ci.product_id = :id
            ORDER BY c.updated_at DESC
            LIMIT 10'
        );
        $cartsStmt->execute(['id' => $id]);
        $carts = $cartsStmt->fetchAll();

        return [
            'orders' => $orders ?: [],
            'subscriptions' => $subscriptions ?: [],
            'carts' => $carts ?: [],
        ];
    }

    public function setAttributes(int $productId, array $attributeIds): void
    {
        $this->db->prepare('DELETE FROM product_attributes WHERE product_id = :product_id')->execute(['product_id' => $productId]);

        if (!$attributeIds) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO product_attributes (product_id, attribute_id) VALUES (:product_id, :attribute_id)');

        foreach ($attributeIds as $attributeId) {
            $stmt->execute([
                'product_id' => $productId,
                'attribute_id' => $attributeId,
            ]);
        }
    }

    public function setPriceTiers(int $productId, array $tiers): void
    {
        $this->db->prepare('DELETE FROM product_price_tiers WHERE product_id = :product_id')->execute(['product_id' => $productId]);

        if (!$tiers) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO product_price_tiers (product_id, min_qty, price) VALUES (:product_id, :min_qty, :price)');

        foreach ($tiers as $tier) {
            $stmt->execute([
                'product_id' => $productId,
                'min_qty' => $tier['min_qty'],
                'price' => $tier['price'],
            ]);
        }
    }

    public function getPriceTiers(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT min_qty, price FROM product_price_tiers WHERE product_id = :product_id ORDER BY min_qty ASC');
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll();
    }

    public function getAttributeIds(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT attribute_id FROM product_attributes WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);

        return array_column($stmt->fetchAll(), 'attribute_id');
    }

    public function getAttributesWithValues(int $productId): array
    {
        $sql = 'SELECT a.* FROM attributes a JOIN product_attributes pa ON pa.attribute_id = a.id WHERE pa.product_id = :product_id AND a.is_active = 1 ORDER BY a.name ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);
        $attributes = $stmt->fetchAll();

        if (!$attributes) {
            return [];
        }

        $attributeIds = array_column($attributes, 'id');
        $placeholders = implode(',', array_fill(0, count($attributeIds), '?'));
        $valuesStmt = $this->db->prepare(
            "SELECT * FROM attribute_values WHERE attribute_id IN ($placeholders) AND is_active = 1 ORDER BY sort_order ASC, id ASC"
        );
        $valuesStmt->execute($attributeIds);
        $values = $valuesStmt->fetchAll();

        $grouped = [];
        foreach ($values as $value) {
            $grouped[$value['attribute_id']][] = $value;
        }

        foreach ($attributes as &$attribute) {
            $attribute['values'] = $grouped[$attribute['id']] ?? [];
        }

        return $attributes;
    }

}

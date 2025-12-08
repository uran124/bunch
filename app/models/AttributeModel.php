<?php
// app/models/AttributeModel.php

class AttributeModel extends Model
{
    private string $attributesTable = 'attributes';
    private string $valuesTable = 'attribute_values';

    public function getAllWithValues(): array
    {
        $attributesStmt = $this->db->query(
            "SELECT * FROM {$this->attributesTable} ORDER BY name ASC"
        );
        $attributes = $attributesStmt->fetchAll();

        if (!$attributes) {
            return [];
        }

        $attributeIds = array_column($attributes, 'id');
        $placeholders = implode(',', array_fill(0, count($attributeIds), '?'));
        $valuesStmt = $this->db->prepare(
            "SELECT * FROM {$this->valuesTable} WHERE attribute_id IN ($placeholders) ORDER BY sort_order ASC, id ASC"
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

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->attributesTable} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['values'] = $this->getValuesForAttribute($id);

        return $row;
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                "UPDATE {$this->attributesTable} SET name = :name, description = :description, type = :type, applies_to = :applies_to, is_active = :is_active WHERE id = :id"
            );
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'],
                'type' => $data['type'],
                'applies_to' => $data['applies_to'],
                'is_active' => $data['is_active'],
                'id' => $data['id'],
            ]);

            return (int) $data['id'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->attributesTable} (name, description, type, applies_to, is_active) VALUES (:name, :description, :type, :applies_to, :is_active)"
        );
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'type' => $data['type'],
            'applies_to' => $data['applies_to'],
            'is_active' => $data['is_active'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->attributesTable} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function saveValue(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                "UPDATE {$this->valuesTable} SET value = :value, price_delta = :price_delta, photo_url = :photo_url, is_active = :is_active, sort_order = :sort_order WHERE id = :id"
            );
            $stmt->execute([
                'value' => $data['value'],
                'price_delta' => $data['price_delta'],
                'photo_url' => $data['photo_url'],
                'is_active' => $data['is_active'],
                'sort_order' => $data['sort_order'],
                'id' => $data['id'],
            ]);

            return (int) $data['id'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->valuesTable} (attribute_id, value, price_delta, photo_url, is_active, sort_order) VALUES (:attribute_id, :value, :price_delta, :photo_url, :is_active, :sort_order)"
        );
        $stmt->execute([
            'attribute_id' => $data['attribute_id'],
            'value' => $data['value'],
            'price_delta' => $data['price_delta'],
            'photo_url' => $data['photo_url'],
            'is_active' => $data['is_active'],
            'sort_order' => $data['sort_order'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteValue(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->valuesTable} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getValuesForAttribute(int $attributeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->valuesTable} WHERE attribute_id = :attribute_id ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute(['attribute_id' => $attributeId]);

        return $stmt->fetchAll();
    }
}

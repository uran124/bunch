<?php
// app/models/Cart.php

class Cart
{
    private const SESSION_KEY = 'cart_items';

    public function getItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->getItems() as $item) {
            $count += (int) ($item['qty'] ?? 0);
        }

        return $count;
    }

    public function addItem(int $productId, int $qty, array $attributeValueIds = []): array
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Количество должно быть 1 или больше');
        }

        $attributeValueIds = $this->normalizeAttributeIds($attributeValueIds);
        $items = $this->getItems();
        $key = $this->buildKey($productId, $attributeValueIds);
        $finalQty = ($items[$key]['qty'] ?? 0) + $qty;

        $newItem = $this->buildItemData($productId, $finalQty, $attributeValueIds);
        $newItem['key'] = $key;

        $items[$key] = $newItem;

        Session::set(self::SESSION_KEY, $items);

        return $newItem;
    }

    public function clear(): void
    {
        Session::remove(self::SESSION_KEY);
    }

    public function removeItem(string $key): void
    {
        $items = $this->getItems();
        unset($items[$key]);
        Session::set(self::SESSION_KEY, $items);
    }

    public function getTotals(): array
    {
        $items = $this->getItems();
        $total = 0;
        $count = 0;

        foreach ($items as $item) {
            $total += (int) floor((float) ($item['line_total'] ?? 0));
            $count += (int) ($item['qty'] ?? 0);
        }

        return [
            'total' => $total,
            'count' => $count,
        ];
    }

    private function buildKey(int $productId, array $attributeValueIds): string
    {
        sort($attributeValueIds);
        return $productId . ':' . implode('-', $attributeValueIds);
    }

    private function buildItemData(int $productId, int $qty, array $attributeValueIds): array
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Количество должно быть 1 или больше');
        }

        $attributeValueIds = $this->normalizeAttributeIds($attributeValueIds);

        $productModel = new Product();
        $product = $productModel->getById($productId);

        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            throw new RuntimeException('Товар не найден или недоступен');
        }

        $attributeDetails = $this->getAttributeDetails($attributeValueIds);

        $pricePerStem = (int) floor((float) $product['price']);
        $priceTiers = $productModel->getPriceTiers($productId);
        foreach ($priceTiers as $tier) {
            if ($qty >= (int) $tier['min_qty']) {
                $pricePerStem = (int) floor((float) $tier['price']);
            }
        }

        $bouquetDelta = 0;
        foreach ($attributeDetails as $attr) {
            if (($attr['applies_to'] ?? 'stem') === 'bouquet') {
                $bouquetDelta += (int) floor((float) $attr['price_delta']);
            } else {
                $pricePerStem += (int) floor((float) $attr['price_delta']);
            }
        }

        return [
            'product_id' => $productId,
            'name' => $product['name'],
            'qty' => $qty,
            'price_per_stem' => $pricePerStem,
            'bouquet_delta' => $bouquetDelta,
            'photo_url' => $product['photo_url'] ?? null,
            'stem_height_cm' => $product['stem_height_cm'] ?? null,
            'attributes' => $attributeDetails,
            'line_total' => ($pricePerStem * $qty) + $bouquetDelta,
        ];
    }

    public function updateItem(string $key, int $qty, array $attributeValueIds = []): array
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Количество должно быть 1 или больше');
        }

        $items = $this->getItems();
        if (!isset($items[$key])) {
            throw new RuntimeException('Позиция не найдена в корзине');
        }

        $existingAttributeIds = $this->extractAttributeIds($items[$key]['attributes'] ?? []);
        $attributeValueIds = $this->normalizeAttributeIds($attributeValueIds ?: $existingAttributeIds);

        $productId = (int) $items[$key]['product_id'];
        $newItem = $this->buildItemData($productId, $qty, $attributeValueIds);
        $newKey = $this->buildKey($productId, $this->extractAttributeIds($newItem['attributes'] ?? []));
        $newItem['key'] = $newKey;

        if ($newKey !== $key) {
            unset($items[$key]);
        }

        $items[$newKey] = $newItem;
        Session::set(self::SESSION_KEY, $items);

        return $newItem;
    }

    private function normalizeAttributeIds(array $attributeValueIds): array
    {
        $normalized = array_values(array_unique(array_map('intval', $attributeValueIds)));
        sort($normalized);

        return $normalized;
    }

    private function extractAttributeIds(array $attributeDetails): array
    {
        $ids = [];
        foreach ($attributeDetails as $detail) {
            if (isset($detail['value_id'])) {
                $ids[] = (int) $detail['value_id'];
            }
        }

        return $this->normalizeAttributeIds($ids);
    }

    private function getAttributeDetails(array $attributeValueIds): array
    {
        if (!$attributeValueIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($attributeValueIds), '?'));
        $sql = "SELECT av.id AS value_id, av.value, av.price_delta, av.is_active, a.id AS attribute_id, a.name, a.applies_to FROM attribute_values av JOIN attributes a ON av.attribute_id = a.id WHERE av.id IN ($placeholders)";

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($attributeValueIds);
        $rows = $stmt->fetchAll();

        $attributes = [];
        foreach ($rows as $row) {
            if ((int) ($row['is_active'] ?? 0) !== 1) {
                continue;
            }

            $attributes[] = [
                'attribute_id' => (int) $row['attribute_id'],
                'value_id' => (int) $row['value_id'],
                'label' => $row['name'],
                'value' => $row['value'],
                'scope' => $row['applies_to'] === 'bouquet' ? 'к букету' : 'к стеблю',
                'applies_to' => $row['applies_to'],
                'price_delta' => (int) floor((float) $row['price_delta']),
            ];
        }

        return $attributes;
    }
}

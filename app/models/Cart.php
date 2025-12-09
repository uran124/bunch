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
        return count($this->getItems());
    }

    public function addItem(int $productId, int $qty, array $attributeValueIds = []): array
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Количество должно быть 1 или больше');
        }

        $productModel = new Product();

        $product = $productModel->getById($productId);

        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            throw new RuntimeException('Товар не найден или недоступен');
        }

        $attributeValueIds = array_values(array_unique(array_map('intval', $attributeValueIds)));
        $attributeDetails = $this->getAttributeDetails($attributeValueIds);

        $items = $this->getItems();
        $key = $this->buildKey($productId, $attributeValueIds);

        $finalQty = ($items[$key]['qty'] ?? 0) + $qty;

        $pricePerStem = (float) $product['price'];
        $priceTiers = $productModel->getPriceTiers($productId);
        foreach ($priceTiers as $tier) {
            if ($finalQty >= (int) $tier['min_qty']) {
                $pricePerStem = (float) $tier['price'];
            }
        }
        $bouquetDelta = 0.0;

        foreach ($attributeDetails as $attr) {
            if (($attr['applies_to'] ?? 'stem') === 'bouquet') {
                $bouquetDelta += (float) $attr['price_delta'];
            } else {
                $pricePerStem += (float) $attr['price_delta'];
            }
        }

        $items[$key] = [
            'key' => $key,
            'product_id' => $productId,
            'name' => $product['name'],
            'qty' => $finalQty,
            'price_per_stem' => $pricePerStem,
            'bouquet_delta' => $bouquetDelta,
            'photo_url' => $product['photo_url'] ?? null,
            'attributes' => $attributeDetails,
        ];

        $items[$key]['line_total'] = ($items[$key]['price_per_stem'] * $items[$key]['qty']) + $items[$key]['bouquet_delta'];

        Session::set(self::SESSION_KEY, $items);

        return $items[$key];
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
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float) ($item['line_total'] ?? 0);
        }

        return [
            'total' => $total,
            'count' => count($items),
        ];
    }

    private function buildKey(int $productId, array $attributeValueIds): string
    {
        sort($attributeValueIds);
        return $productId . ':' . implode('-', $attributeValueIds);
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
                'price_delta' => (float) $row['price_delta'],
            ];
        }

        return $attributes;
    }
}

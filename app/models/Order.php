<?php
// app/models/Order.php

class Order extends Model
{
    public function getLatestActiveForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE user_id = :user_id AND status IN ('new', 'confirmed', 'delivering') ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->buildOrderPayload($order);
    }

    public function getActiveOrdersForUser(int $userId): array
    {
        return $this->getOrdersByStatuses($userId, ['new', 'confirmed', 'delivering']);
    }

    public function getCompletedOrdersForUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->getOrdersByStatuses($userId, ['delivered', 'cancelled'], $limit, $offset);
    }

    public function countCompletedOrdersForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND status IN ('delivered', 'cancelled')"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    private function getOrdersByStatuses(int $userId, array $statuses, int $limit = 50, int $offset = 0): array
    {
        if (empty($statuses)) {
            return [];
        }

        $placeholders = [];
        foreach ($statuses as $index => $status) {
            $placeholders[] = ':status' . $index;
        }

        $sql = sprintf(
            'SELECT * FROM orders WHERE user_id = :user_id AND status IN (%s) ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($statuses as $index => $status) {
            $stmt->bindValue(':status' . $index, $status, PDO::PARAM_STR);
        }

        $stmt->execute();

        $orders = $stmt->fetchAll();

        return array_map(function (array $order): array {
            return $this->buildOrderPayload($order);
        }, $orders);
    }

    private function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.photo_url FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id ORDER BY oi.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    public function createFromCart(int $userId, array $cartItems, array $payload): int
    {
        if (empty($cartItems)) {
            throw new RuntimeException('Корзина пуста');
        }

        $deliveryType = ($payload['mode'] ?? '') === 'delivery' ? 'delivery' : 'pickup';
        $scheduledDate = $this->normalizeDate($payload['date'] ?? null);
        $scheduledTime = $this->normalizeTime($payload['time'] ?? null);
        $addressId = isset($payload['address_id']) ? (int) $payload['address_id'] : null;
        $addressText = trim((string) ($payload['address_text'] ?? ''));
        $recipientName = trim((string) ($payload['recipient_name'] ?? ''));
        $recipientPhone = trim((string) ($payload['recipient_phone'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? ''));

        $totalAmount = 0.0;
        foreach ($cartItems as $item) {
            $totalAmount += (float) ($item['line_total'] ?? 0);
        }

        $this->db->beginTransaction();

        try {
            $orderStmt = $this->db->prepare(
                'INSERT INTO orders (user_id, address_id, total_amount, status, delivery_type, scheduled_date, scheduled_time, address_text, recipient_name, recipient_phone, comment) VALUES (:user_id, :address_id, :total_amount, :status, :delivery_type, :scheduled_date, :scheduled_time, :address_text, :recipient_name, :recipient_phone, :comment)'
            );

            $orderStmt->execute([
                'user_id' => $userId,
                'address_id' => $deliveryType === 'delivery' ? $addressId : null,
                'total_amount' => $totalAmount,
                'status' => 'new',
                'delivery_type' => $deliveryType,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'address_text' => $deliveryType === 'delivery' ? ($addressText ?: null) : null,
                'recipient_name' => $deliveryType === 'delivery' ? ($recipientName ?: null) : null,
                'recipient_phone' => $deliveryType === 'delivery' ? ($recipientPhone ?: null) : null,
                'comment' => $comment ?: null,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, qty, price) VALUES (:order_id, :product_id, :product_name, :qty, :price)'
            );
            $attrStmt = $this->db->prepare(
                'INSERT INTO order_item_attributes (order_item_id, attribute_id, attribute_value_id, applies_to, price_delta) VALUES (:order_item_id, :attribute_id, :attribute_value_id, :applies_to, :price_delta)'
            );

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => (int) $item['product_id'],
                    'product_name' => $item['name'] ?? 'Товар',
                    'qty' => (int) $item['qty'],
                    'price' => (float) ($item['price_per_stem'] ?? 0),
                ]);

                $orderItemId = (int) $this->db->lastInsertId();

                foreach ($item['attributes'] as $attr) {
                    $attrStmt->execute([
                        'order_item_id' => $orderItemId,
                        'attribute_id' => (int) ($attr['attribute_id'] ?? 0),
                        'attribute_value_id' => (int) ($attr['value_id'] ?? 0),
                        'applies_to' => $attr['applies_to'] ?? 'stem',
                        'price_delta' => (float) ($attr['price_delta'] ?? 0),
                    ]);
                }
            }

            $this->db->commit();

            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        return $dt instanceof DateTime ? $dt->format('Y-m-d') : null;
    }

    private function normalizeTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $dt = DateTime::createFromFormat('H:i', $value);
        return $dt instanceof DateTime ? $dt->format('H:i:s') : null;
    }

    private function buildOrderPayload(array $order): array
    {
        $items = $this->getItems((int) $order['id']);

        $address = null;
        if (!empty($order['address_id'])) {
            $addressStmt = $this->db->prepare('SELECT * FROM user_addresses WHERE id = :id LIMIT 1');
            $addressStmt->execute(['id' => $order['address_id']]);
            $addressRow = $addressStmt->fetch();
            $address = $addressRow ? UserAddress::formatAddress($addressRow) : null;
        } elseif (!empty($order['address_text'])) {
            $address = $order['address_text'];
        }

        return [
            'id' => (int) $order['id'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'total_amount' => (float) $order['total_amount'],
            'delivery_type' => $order['delivery_type'] ?? ($order['address_id'] ? 'delivery' : 'pickup'),
            'scheduled_date' => $order['scheduled_date'] ?? null,
            'scheduled_time' => $order['scheduled_time'] ?? null,
            'address' => $address,
            'items' => $items,
        ];
    }
}

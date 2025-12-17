<?php
// app/models/Order.php

class Order extends Model
{
    private array $statusPaymentMap = [
        'paid' => ['confirmed', 'assembled', 'delivering', 'delivered'],
        'pending' => ['new'],
        'refund' => ['cancelled'],
    ];

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

    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);

        $order = $stmt->fetch();

        return $order ?: null;
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

    public function getAdminOrders(?string $search = null, ?string $statusFilter = null, ?string $paymentFilter = null): array
    {
        $sql = "SELECT o.*, u.name AS user_name, u.phone AS user_phone FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.delivery_type <> 'subscription'";
        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= ' AND o.status = :status';
            $params['status'] = $statusFilter;
        }

        if ($paymentFilter && $paymentFilter !== 'all') {
            $statuses = $this->mapPaymentFilterToStatuses($paymentFilter);

            if (!empty($statuses)) {
                $placeholders = [];
                foreach ($statuses as $index => $status) {
                    $key = ':payment_status_' . $index;
                    $placeholders[] = $key;
                    $params[$key] = $status;
                }

                $sql .= ' AND o.status IN (' . implode(', ', $placeholders) . ')';
            }
        }

        if ($search) {
            $sql .= ' AND (o.id LIKE :search OR o.recipient_name LIKE :search OR u.name LIKE :search OR u.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY o.created_at DESC LIMIT 100';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $orders = $stmt->fetchAll();

        return array_map(function (array $order): array {
            return $this->mapAdminOrder($order);
        }, $orders);
    }

    public function getAdminOrderDetail(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, u.name AS user_name, u.phone AS user_phone FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);

        $order = $stmt->fetch();

        if (!$order || $order['delivery_type'] === 'subscription') {
            return null;
        }

        $items = $this->getItems($orderId);

        return $this->mapAdminOrder($order, $items);
    }

    public function updateAdminOrder(int $orderId, array $data): void
    {
        $allowedStatuses = ['new', 'confirmed', 'assembled', 'delivering', 'delivered', 'cancelled'];
        $allowedDeliveryTypes = ['pickup', 'delivery'];

        $status = in_array($data['status'] ?? 'new', $allowedStatuses, true) ? $data['status'] : 'new';
        $deliveryType = in_array($data['delivery_type'] ?? 'pickup', $allowedDeliveryTypes, true)
            ? $data['delivery_type']
            : 'pickup';

        $scheduledDate = $this->normalizeDate($data['scheduled_date'] ?? null);
        $scheduledTime = $this->normalizeTime($data['scheduled_time'] ?? null);

        $stmt = $this->db->prepare(
            'UPDATE orders SET status = :status, delivery_type = :delivery_type, scheduled_date = :scheduled_date, scheduled_time = :scheduled_time, address_text = :address_text, recipient_name = :recipient_name, recipient_phone = :recipient_phone, comment = :comment, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $orderId,
            'status' => $status,
            'delivery_type' => $deliveryType,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'address_text' => $this->emptyToNull($data['address_text'] ?? null),
            'recipient_name' => $this->emptyToNull($data['recipient_name'] ?? null),
            'recipient_phone' => $this->emptyToNull($data['recipient_phone'] ?? null),
            'comment' => $this->emptyToNull($data['comment'] ?? null),
        ]);
    }

    private function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.photo_url FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id ORDER BY oi.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }

    public function getItemsForOrder(int $orderId): array
    {
        return $this->getItems($orderId);
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
        $deliveryPrice = isset($payload['delivery_price']) ? (float) $payload['delivery_price'] : null;
        $zoneId = isset($payload['zone_id']) ? (int) $payload['zone_id'] : null;
        $deliveryPricingVersion = $this->emptyToNull($payload['delivery_pricing_version'] ?? null);
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
                'INSERT INTO orders (user_id, address_id, total_amount, status, delivery_type, delivery_price, zone_id, delivery_pricing_version, scheduled_date, scheduled_time, address_text, recipient_name, recipient_phone, comment) VALUES (:user_id, :address_id, :total_amount, :status, :delivery_type, :delivery_price, :zone_id, :delivery_pricing_version, :scheduled_date, :scheduled_time, :address_text, :recipient_name, :recipient_phone, :comment)'            );

            $orderStmt->execute([
                'user_id' => $userId,
                'address_id' => $deliveryType === 'delivery' ? $addressId : null,
                'total_amount' => $totalAmount,
                'status' => 'new',
                'delivery_type' => $deliveryType,
                'delivery_price' => $deliveryType === 'delivery' ? $deliveryPrice : null,
                'zone_id' => $deliveryType === 'delivery' ? $zoneId : null,
                'delivery_pricing_version' => $deliveryType === 'delivery' ? $deliveryPricingVersion : null,
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

    private function mapAdminOrder(array $order, array $items = []): array
    {
        $scheduled = $this->formatDeliveryWindow($order['scheduled_date'] ?? null, $order['scheduled_time'] ?? null);
        $items = $items ?: $this->getItems((int) $order['id']);

        $itemsPayload = array_map(function (array $item): array {
            $qty = (int) $item['qty'];
            $price = (float) $item['price'];

            return [
                'title' => $item['product_name'] ?? ($item['name'] ?? 'Товар'),
                'qty' => $qty,
                'unit' => $this->formatPrice($price),
                'total' => $this->formatPrice($price * $qty),
                'image' => $item['photo_url'] ?? '/assets/images/products/bouquet.svg',
            ];
        }, $items);

        return [
            'id' => (int) $order['id'],
            'number' => 'B-' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT),
            'customer' => $order['user_name'] ?? $order['recipient_name'] ?? 'Без имени',
            'customerPhone' => $order['user_phone'] ?? $order['recipient_phone'] ?? '',
            'sum' => $this->formatPrice((float) $order['total_amount']),
            'status' => $order['status'],
            'statusLabel' => $this->mapOrderStatus($order['status']),
            'payment' => $this->mapPaymentStatus($order['status']),
            'delivery' => $scheduled,
            'deliveryType' => $this->mapDeliveryType($order['delivery_type'] ?? 'pickup'),
            'scheduled_date' => $order['scheduled_date'] ?? null,
            'scheduled_time' => $order['scheduled_time'] ?? null,
            'address' => $order['address_text'] ?? null,
            'comment' => $order['comment'] ?? null,
            'recipient_name' => $order['recipient_name'] ?? null,
            'recipient_phone' => $order['recipient_phone'] ?? null,
            'updated_at' => $order['updated_at'] ?? $order['created_at'] ?? null,
            'items' => $itemsPayload,
        ];
    }

    private function formatPrice(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' ₽';
    }

    private function formatDeliveryWindow(?string $date, ?string $time): string
    {
        $parts = [];

        if ($date) {
            try {
                $parts[] = (new DateTimeImmutable($date))->format('d.m');
            } catch (Throwable $e) {
                $parts[] = $date;
            }
        }

        if ($time) {
            $parts[] = substr($time, 0, 5);
        }

        return $parts ? implode(', ', $parts) : '—';
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'new' => 'Новый',
            'confirmed' => 'Принят',
            'assembled' => 'Собран',
            'delivering' => 'В доставке',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменён',
            default => 'В обработке',
        };
    }

    private function mapDeliveryType(string $type): string
    {
        return match ($type) {
            'delivery' => 'Доставка',
            'subscription' => 'Подписка',
            default => 'Самовывоз',
        };
    }

    private function mapPaymentStatus(string $status): string
    {
        return match ($status) {
            'cancelled' => 'Возврат',
            'new' => 'Ожидает',
            default => 'Оплачен',
        };
    }

    private function mapPaymentFilterToStatuses(string $filter): array
    {
        return $this->statusPaymentMap[$filter] ?? [];
    }

    private function emptyToNull(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
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

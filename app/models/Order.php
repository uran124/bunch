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

        $items = $this->getItems((int) $order['id']);

        $address = null;
        if (!empty($order['address_id'])) {
            $addressStmt = $this->db->prepare('SELECT * FROM user_addresses WHERE id = :id LIMIT 1');
            $addressStmt->execute(['id' => $order['address_id']]);
            $addressRow = $addressStmt->fetch();
            $address = $addressRow ? UserAddress::formatAddress($addressRow) : null;
        }

        return [
            'id' => (int) $order['id'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'total_amount' => (float) $order['total_amount'],
            'delivery_type' => $order['address_id'] ? 'delivery' : 'pickup',
            'address' => $address,
            'items' => $items,
        ];
    }

    private function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.photo_url FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id ORDER BY oi.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }
}

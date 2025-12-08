<?php
// app/models/Subscription.php

class Subscription extends Model
{
    public function getActiveForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, p.name AS product_name, p.price AS product_price FROM subscriptions s JOIN products p ON p.id = s.product_id WHERE s.user_id = :user_id AND s.status = 'active' ORDER BY s.next_delivery_date ASC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        $subscription = $stmt->fetch();

        if (!$subscription) {
            return null;
        }

        return [
            'id' => (int) $subscription['id'],
            'plan' => $subscription['plan'],
            'qty' => (int) $subscription['qty'],
            'product_name' => $subscription['product_name'],
            'product_price' => (float) $subscription['product_price'],
            'next_delivery_date' => $subscription['next_delivery_date'],
        ];
    }
}

<?php
// app/models/DeliveryDistanceRate.php

class DeliveryDistanceRate extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM delivery_distance_rates ORDER BY min_km ASC, max_km ASC');
        return $stmt->fetchAll() ?: [];
    }

    public function saveRanges(array $ranges): void
    {
        $this->db->exec('DELETE FROM delivery_distance_rates');

        if ($ranges) {
            $stmt = $this->db->prepare('INSERT INTO delivery_distance_rates (min_km, max_km, price) VALUES (:min_km, :max_km, :price)');
            foreach ($ranges as $range) {
                $stmt->execute([
                    'min_km' => $range['min_km'],
                    'max_km' => $range['max_km'],
                    'price' => $range['price'],
                ]);
            }
        }
    }
}

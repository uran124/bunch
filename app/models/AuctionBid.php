<?php
// app/models/AuctionBid.php

class AuctionBid extends Model
{
    public function getCurrentBid(int $lotId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, amount, user_id, created_at FROM auction_bids WHERE lot_id = :lot_id AND status = 'active' ORDER BY amount DESC, created_at DESC LIMIT 1"
        );
        $stmt->execute(['lot_id' => $lotId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getRecentBids(int $lotId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.id, b.amount, b.created_at, u.phone FROM auction_bids b JOIN users u ON u.id = b.user_id WHERE b.lot_id = :lot_id AND b.status = 'active' ORDER BY b.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':lot_id', $lotId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(static function (array $row): array {
            $phone = preg_replace('/\D+/', '', (string) $row['phone']);
            $last4 = $phone !== '' ? substr($phone, -4) : '----';

            return [
                'id' => (int) $row['id'],
                'amount' => (float) $row['amount'],
                'created_at' => $row['created_at'],
                'phone_last4' => $last4,
            ];
        }, $rows);
    }
}

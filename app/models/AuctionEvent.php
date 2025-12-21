<?php
// app/models/AuctionEvent.php

class AuctionEvent extends Model
{
    public function log(int $lotId, string $action, ?int $bidId = null, ?int $userId = null, ?string $note = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO auction_events (lot_id, bid_id, user_id, action, note) VALUES (:lot_id, :bid_id, :user_id, :action, :note)'
        );
        $stmt->execute([
            'lot_id' => $lotId,
            'bid_id' => $bidId,
            'user_id' => $userId,
            'action' => $action,
            'note' => $note,
        ]);
    }
}

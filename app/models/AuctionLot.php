<?php
// app/models/AuctionLot.php

class AuctionLot extends Model
{
    private const EXTEND_MINUTES = 5;
    private const CANCEL_WINDOW_MINUTES = 60;

    public function createLot(array $payload): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO auction_lots (title, description, image, store_price, start_price, bid_step, blitz_price, starts_at, ends_at, original_ends_at, status) VALUES (:title, :description, :image, :store_price, :start_price, :bid_step, :blitz_price, :starts_at, :ends_at, :original_ends_at, :status)'
        );
        $stmt->execute([
            'title' => $payload['title'],
            'description' => $payload['description'],
            'image' => $payload['image'],
            'store_price' => $payload['store_price'],
            'start_price' => $payload['start_price'],
            'bid_step' => $payload['bid_step'],
            'blitz_price' => $payload['blitz_price'],
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
            'original_ends_at' => $payload['ends_at'],
            'status' => $payload['status'] ?? 'draft',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAdminList(): array
    {
        $sql = <<<'SQL'
SELECT l.*, u.phone AS winner_phone
FROM auction_lots l
LEFT JOIN users u ON u.id = l.winner_user_id
ORDER BY l.created_at DESC
SQL;
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $bidModel = new AuctionBid();

        return array_map(function (array $row) use ($bidModel): array {
            $this->finalizeIfEnded((int) $row['id']);
            $currentBid = $bidModel->getCurrentBid((int) $row['id']);
            $currentPrice = $currentBid ? (float) $currentBid['amount'] : (float) $row['start_price'];

            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'status' => $row['status'],
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
                'current_price' => $currentPrice,
                'blitz_price' => $row['blitz_price'],
                'winner_last4' => $this->getLast4($row['winner_phone'] ?? ''),
            ];
        }, $rows);
    }

    public function getPromoList(): array
    {
        $stmt = $this->db->query("SELECT * FROM auction_lots WHERE status != 'cancelled' ORDER BY starts_at DESC");
        $rows = $stmt->fetchAll();
        $bidModel = new AuctionBid();

        return array_map(function (array $row) use ($bidModel): array {
            $this->finalizeIfEnded((int) $row['id']);
            $currentBid = $bidModel->getCurrentBid((int) $row['id']);
            $currentPrice = $currentBid ? (float) $currentBid['amount'] : (float) $row['start_price'];
            $statusLabel = $this->formatStatus($row);
            $timeLabel = $this->formatTimeLabel($row);

            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'photo' => $row['image'],
                'store_price' => (float) $row['store_price'],
                'start_price' => (float) $row['start_price'],
                'bid_step' => (float) $row['bid_step'],
                'blitz_price' => $row['blitz_price'] !== null ? (float) $row['blitz_price'] : null,
                'current_price' => $currentPrice,
                'status_label' => $statusLabel,
                'time_label' => $timeLabel,
                'status' => $row['status'],
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
            ];
        }, $rows);
    }

    public function getLotDetails(int $lotId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM auction_lots WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $this->finalizeIfEnded($lotId);
        $stmt = $this->db->prepare('SELECT * FROM auction_lots WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $bidModel = new AuctionBid();
        $currentBid = $bidModel->getCurrentBid($lotId);
        $currentPrice = $currentBid ? (float) $currentBid['amount'] : (float) $row['start_price'];

        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image' => $row['image'],
            'store_price' => (float) $row['store_price'],
            'start_price' => (float) $row['start_price'],
            'bid_step' => (float) $row['bid_step'],
            'blitz_price' => $row['blitz_price'] !== null ? (float) $row['blitz_price'] : null,
            'status' => $row['status'],
            'starts_at' => $row['starts_at'],
            'ends_at' => $row['ends_at'],
            'current_price' => $currentPrice,
            'min_bid' => $currentBid ? $currentPrice + (float) $row['bid_step'] : (float) $row['start_price'],
        ];
    }

    public function placeBid(int $lotId, int $userId, ?float $amount): array
    {
        $lot = $this->getLotDetails($lotId);
        if (!$lot) {
            throw new RuntimeException('Лот не найден');
        }

        if ($lot['status'] !== 'active') {
            throw new RuntimeException('Лот не активен');
        }

        $now = new DateTime();
        if ($lot['starts_at'] && $now < new DateTime($lot['starts_at'])) {
            throw new RuntimeException('Аукцион ещё не стартовал');
        }

        if ($lot['ends_at'] && $now > new DateTime($lot['ends_at'])) {
            throw new RuntimeException('Аукцион завершён');
        }

        $currentBidModel = new AuctionBid();
        $currentBid = $currentBidModel->getCurrentBid($lotId);
        $currentPrice = $currentBid ? (float) $currentBid['amount'] : (float) $lot['start_price'];
        $minBid = $currentBid ? $currentPrice + (float) $lot['bid_step'] : (float) $lot['start_price'];
        $finalAmount = $amount !== null ? (float) $amount : $minBid;

        if ($finalAmount < $minBid) {
            throw new RuntimeException(sprintf('Минимальная ставка: %.2f ₽ (шаг: %.2f ₽)', $minBid, $lot['bid_step']));
        }

        $stmt = $this->db->prepare(
            'INSERT INTO auction_bids (lot_id, user_id, amount) VALUES (:lot_id, :user_id, :amount)'
        );
        $stmt->execute([
            'lot_id' => $lotId,
            'user_id' => $userId,
            'amount' => $finalAmount,
        ]);
        $bidId = (int) $this->db->lastInsertId();

        $this->extendIfNeeded($lotId, $lot['ends_at']);

        (new AuctionEvent())->log($lotId, 'bid_created', $bidId, $userId, null);

        return [
            'bid_id' => $bidId,
            'amount' => $finalAmount,
        ];
    }

    public function blitz(int $lotId, int $userId): array
    {
        $lot = $this->getLotDetails($lotId);
        if (!$lot) {
            throw new RuntimeException('Лот не найден');
        }

        if ($lot['status'] !== 'active') {
            throw new RuntimeException('Лот не активен');
        }

        if ($lot['blitz_price'] === null) {
            throw new RuntimeException('Блиц-цена не задана');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO auction_bids (lot_id, user_id, amount) VALUES (:lot_id, :user_id, :amount)'
        );
        $stmt->execute([
            'lot_id' => $lotId,
            'user_id' => $userId,
            'amount' => $lot['blitz_price'],
        ]);
        $bidId = (int) $this->db->lastInsertId();

        $update = $this->db->prepare(
            "UPDATE auction_lots SET status = 'finished', winner_user_id = :user_id, winning_bid_id = :bid_id, ends_at = NOW() WHERE id = :id"
        );
        $update->execute([
            'user_id' => $userId,
            'bid_id' => $bidId,
            'id' => $lotId,
        ]);

        (new AuctionEvent())->log($lotId, 'blitz', $bidId, $userId, null);

        return [
            'bid_id' => $bidId,
            'amount' => (float) $lot['blitz_price'],
        ];
    }

    public function cancelBid(int $bidId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, l.ends_at FROM auction_bids b JOIN auction_lots l ON l.id = b.lot_id WHERE b.id = :bid_id AND b.user_id = :user_id'
        );
        $stmt->execute([
            'bid_id' => $bidId,
            'user_id' => $userId,
        ]);
        $bid = $stmt->fetch();

        if (!$bid) {
            throw new RuntimeException('Ставка не найдена');
        }

        if ($bid['status'] === 'cancelled') {
            throw new RuntimeException('Ставка уже отменена');
        }

        if ($bid['ends_at']) {
            $endsAt = new DateTime($bid['ends_at']);
            $limit = (clone $endsAt)->modify('-' . self::CANCEL_WINDOW_MINUTES . ' minutes');
            if (new DateTime() > $limit) {
                throw new RuntimeException('Отмена ставки доступна не позднее чем за 1 час до окончания');
            }
        }

        $update = $this->db->prepare(
            "UPDATE auction_bids SET status = 'cancelled', cancelled_at = NOW(), cancel_reason = :reason WHERE id = :id"
        );
        $update->execute([
            'reason' => 'Отменено пользователем',
            'id' => $bidId,
        ]);

        (new AuctionEvent())->log((int) $bid['lot_id'], 'bid_cancelled', $bidId, $userId, null);
    }

    public function finalizeIfEnded(int $lotId): void
    {
        $stmt = $this->db->prepare('SELECT * FROM auction_lots WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
        $lot = $stmt->fetch();

        if (!$lot || $lot['status'] !== 'active' || !$lot['ends_at']) {
            return;
        }

        $now = new DateTime();
        $endsAt = new DateTime($lot['ends_at']);
        if ($now <= $endsAt) {
            return;
        }

        $bidModel = new AuctionBid();
        $currentBid = $bidModel->getCurrentBid($lotId);

        $winnerUserId = $currentBid ? (int) $currentBid['user_id'] : null;
        $winningBidId = $currentBid ? (int) $currentBid['id'] : null;

        $update = $this->db->prepare(
            "UPDATE auction_lots SET status = 'finished', winner_user_id = :winner_user_id, winning_bid_id = :winning_bid_id WHERE id = :id"
        );
        $update->execute([
            'winner_user_id' => $winnerUserId,
            'winning_bid_id' => $winningBidId,
            'id' => $lotId,
        ]);

        (new AuctionEvent())->log($lotId, 'finished', $winningBidId, $winnerUserId, null);
    }

    private function extendIfNeeded(int $lotId, ?string $endsAt): void
    {
        if (!$endsAt) {
            return;
        }

        $now = new DateTime();
        $endTime = new DateTime($endsAt);
        $diffSeconds = $endTime->getTimestamp() - $now->getTimestamp();
        if ($diffSeconds > self::EXTEND_MINUTES * 60) {
            return;
        }

        $newEnd = (clone $now)->modify('+' . self::EXTEND_MINUTES . ' minutes');
        $stmt = $this->db->prepare('UPDATE auction_lots SET ends_at = :ends_at WHERE id = :id');
        $stmt->execute([
            'ends_at' => $newEnd->format('Y-m-d H:i:s'),
            'id' => $lotId,
        ]);
    }

    private function formatStatus(array $lot): string
    {
        if ($lot['status'] === 'finished') {
            return 'Завершён';
        }

        if ($lot['status'] === 'cancelled') {
            return 'Отменён';
        }

        $now = new DateTime();
        if ($lot['starts_at'] && $now < new DateTime($lot['starts_at'])) {
            return 'Скоро старт';
        }

        return 'Идёт торг';
    }

    private function formatTimeLabel(array $lot): string
    {
        $now = new DateTime();
        if ($lot['starts_at'] && $now < new DateTime($lot['starts_at'])) {
            return 'Старт ' . (new DateTime($lot['starts_at']))->format('d.m H:i');
        }

        if ($lot['ends_at']) {
            $end = new DateTime($lot['ends_at']);
            if ($now > $end) {
                return 'Завершён ' . $end->format('d.m H:i');
            }

            $seconds = $end->getTimestamp() - $now->getTimestamp();
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return sprintf('До завершения %02d:%02d', $hours, $minutes);
        }

        return 'Время уточняется';
    }

    private function getLast4(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '----';
        }
        return substr($digits, -4);
    }
}

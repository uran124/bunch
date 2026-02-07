<?php
// app/models/AuctionLot.php

class AuctionLot extends Model
{
    private const EXTEND_MINUTES = 3;
    private const CANCEL_WINDOW_MINUTES = 60;

    public function createLot(array $payload): int
    {
        $productModel = new Product();

        $this->db->beginTransaction();

        try {
            $productId = $productModel->createCustom([
                'name' => $payload['title'],
                'alt_name' => null,
                'description' => $payload['description'],
                'price' => $payload['start_price'],
                'article' => null,
                'photo_url' => $payload['image'],
                'photo_url_secondary' => null,
                'photo_url_tertiary' => null,
                'category' => 'main',
                'product_type' => 'auction',
                'is_base' => 0,
                'is_active' => 1,
                'sort_order' => 0,
            ]);

            $stmt = $this->db->prepare(
                'INSERT INTO auction_lots (product_id, title, description, image, store_price, start_price, bid_step, blitz_price, starts_at, ends_at, original_ends_at, status) VALUES (:product_id, :title, :description, :image, :store_price, :start_price, :bid_step, :blitz_price, :starts_at, :ends_at, :original_ends_at, :status)'
            );
            $stmt->execute([
                'product_id' => $productId,
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

            $lotId = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $lotId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
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
            if ($row['status'] === 'active' && $row['ends_at']) {
                $endsAt = new DateTime($row['ends_at']);
                if (new DateTime() > $endsAt) {
                    $row['status'] = 'finished';
                }
            }
            if ($row['status'] === 'finished' && empty($row['winner_phone'])) {
                $refresh = $this->db->prepare(
                    'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount
                    FROM auction_lots l
                    LEFT JOIN users u ON u.id = l.winner_user_id
                    LEFT JOIN auction_bids b ON b.id = l.winning_bid_id
                    WHERE l.id = :id'
                );
                $refresh->execute(['id' => (int) $row['id']]);
                $row = $refresh->fetch() ?: $row;
            }
            $currentBid = $bidModel->getCurrentBid((int) $row['id']);
            $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : (int) floor((float) $row['start_price']);
            $currentBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;

            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'status' => $row['status'],
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
                'current_price' => $currentPrice,
                'blitz_price' => $row['blitz_price'] !== null ? (int) floor((float) $row['blitz_price']) : null,
                'winner_last4' => $this->getLast4($row['winner_phone'] ?? ''),
            ];
        }, $rows);
    }

    public function getAdminLotDetails(int $lotId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount FROM auction_lots l LEFT JOIN users u ON u.id = l.winner_user_id LEFT JOIN auction_bids b ON b.id = l.winning_bid_id WHERE l.id = :id'
        );
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $this->finalizeIfEnded($lotId);
        $stmt = $this->db->prepare(
            'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount FROM auction_lots l LEFT JOIN users u ON u.id = l.winner_user_id LEFT JOIN auction_bids b ON b.id = l.winning_bid_id WHERE l.id = :id'
        );
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $bidModel = new AuctionBid();
        $currentBid = $bidModel->getCurrentBid($lotId);
        $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : (int) floor((float) $row['start_price']);
        $currentBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;
        $bidCount = $bidModel->countLotBids($lotId);

        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image' => $row['image'],
            'store_price' => (int) floor((float) $row['store_price']),
            'start_price' => (int) floor((float) $row['start_price']),
            'bid_step' => (int) floor((float) $row['bid_step']),
            'blitz_price' => $row['blitz_price'] !== null ? (int) floor((float) $row['blitz_price']) : null,
            'status' => $row['status'],
            'starts_at' => $row['starts_at'],
            'ends_at' => $row['ends_at'],
            'original_ends_at' => $row['original_ends_at'],
            'current_price' => $currentPrice,
            'winner_last4' => $this->getLast4($row['winner_phone'] ?? ''),
            'winning_amount' => $row['winning_amount'] !== null ? (int) floor((float) $row['winning_amount']) : null,
        ];
    }

    public function updateLot(int $lotId, array $payload): void
    {
        $stmt = $this->db->prepare(
            'UPDATE auction_lots SET title = :title, description = :description, image = :image, store_price = :store_price, start_price = :start_price, bid_step = :bid_step, blitz_price = :blitz_price, starts_at = :starts_at, ends_at = :ends_at, original_ends_at = :original_ends_at, status = :status WHERE id = :id'
        );
        $stmt->execute([
            'id' => $lotId,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'image' => $payload['image'],
            'store_price' => $payload['store_price'],
            'start_price' => $payload['start_price'],
            'bid_step' => $payload['bid_step'],
            'blitz_price' => $payload['blitz_price'],
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
            'original_ends_at' => $payload['original_ends_at'],
            'status' => $payload['status'],
        ]);

        $stmt = $this->db->prepare('SELECT product_id FROM auction_lots WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
        $productId = (int) $stmt->fetchColumn();
        if ($productId > 0) {
            $productModel = new Product();
            $productModel->updateCustom($productId, [
                'name' => $payload['title'],
                'alt_name' => null,
                'description' => $payload['description'],
                'price' => $payload['start_price'],
                'photo_url' => $payload['image'],
                'photo_url_secondary' => null,
                'photo_url_tertiary' => null,
                'category' => 'main',
                'product_type' => 'auction',
                'is_active' => 1,
            ]);
        }
    }

    public function getPromoList(): array
    {
        $stmt = $this->db->query(
            "SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount
            FROM auction_lots l
            LEFT JOIN users u ON u.id = l.winner_user_id
            LEFT JOIN auction_bids b ON b.id = l.winning_bid_id
            WHERE l.status != 'cancelled'
            ORDER BY l.starts_at DESC"
        );
        $rows = $stmt->fetchAll();
        $bidModel = new AuctionBid();
        $now = new DateTimeImmutable();
        $recentThreshold = $now->modify('-7 days');
        $entries = [];

        foreach ($rows as $index => $row) {
            $this->finalizeIfEnded((int) $row['id']);
            if ($row['status'] === 'active' && $row['ends_at']) {
                $endsAt = new DateTime($row['ends_at']);
                if (new DateTime() > $endsAt) {
                    $row['status'] = 'finished';
                }
            }
            if ($row['status'] === 'finished' && empty($row['winner_phone'])) {
                $refresh = $this->db->prepare(
                    'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount
                    FROM auction_lots l
                    LEFT JOIN users u ON u.id = l.winner_user_id
                    LEFT JOIN auction_bids b ON b.id = l.winning_bid_id
                    WHERE l.id = :id'
                );
                $refresh->execute(['id' => (int) $row['id']]);
                $row = $refresh->fetch() ?: $row;
            }

            $endsAt = $row['ends_at'] ? new DateTimeImmutable($row['ends_at']) : null;
            $isRecentlyFinished = $endsAt && $row['status'] === 'finished' && $endsAt >= $recentThreshold && $endsAt <= $now;
            if ($row['status'] !== 'active' && !$isRecentlyFinished) {
                continue;
            }

            $currentBid = $bidModel->getCurrentBid((int) $row['id']);
            $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : (int) floor((float) $row['start_price']);
            $currentBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;
            $statusLabel = $this->formatStatus($row);
            $timeLabel = $this->formatTimeLabel($row);
            $bidCount = $bidModel->countLotBids((int) $row['id']);

            $entries[] = [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'photo' => $row['image'],
                'store_price' => (int) floor((float) $row['store_price']),
                'start_price' => (int) floor((float) $row['start_price']),
                'bid_step' => (int) floor((float) $row['bid_step']),
                'blitz_price' => $row['blitz_price'] !== null ? (int) floor((float) $row['blitz_price']) : null,
                'current_price' => $currentPrice,
                'current_bid_user_id' => $currentBidUserId,
                'status_label' => $statusLabel,
                'time_label' => $timeLabel,
                'status' => $row['status'],
                'winner_last4' => $this->getLast4($row['winner_phone'] ?? ''),
                'winning_amount' => $row['winning_amount'] !== null ? (int) floor((float) $row['winning_amount']) : null,
                'bid_count' => $bidCount,
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
                'is_recently_finished' => $isRecentlyFinished,
                'sort_index' => $index,
            ];
        }

        usort($entries, static function (array $left, array $right): int {
            if ($left['is_recently_finished'] === $right['is_recently_finished']) {
                return $left['sort_index'] <=> $right['sort_index'];
            }

            return $left['is_recently_finished'] ? 1 : -1;
        });

        foreach ($entries as &$entry) {
            unset($entry['sort_index']);
        }
        unset($entry);

        return array_values($entries);
    }

    public function getLotDetails(int $lotId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount
            FROM auction_lots l
            LEFT JOIN users u ON u.id = l.winner_user_id
            LEFT JOIN auction_bids b ON b.id = l.winning_bid_id
            WHERE l.id = :id'
        );
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $this->finalizeIfEnded($lotId);
        $stmt = $this->db->prepare(
            'SELECT l.*, u.phone AS winner_phone, b.amount AS winning_amount
            FROM auction_lots l
            LEFT JOIN users u ON u.id = l.winner_user_id
            LEFT JOIN auction_bids b ON b.id = l.winning_bid_id
            WHERE l.id = :id'
        );
        $stmt->execute(['id' => $lotId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $bidModel = new AuctionBid();
        $currentBid = $bidModel->getCurrentBid($lotId);
        $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : (int) floor((float) $row['start_price']);
        $currentBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;
        $bidCount = $bidModel->countLotBids($lotId);

        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image' => $row['image'],
            'store_price' => (int) floor((float) $row['store_price']),
            'start_price' => (int) floor((float) $row['start_price']),
            'bid_step' => (int) floor((float) $row['bid_step']),
            'blitz_price' => $row['blitz_price'] !== null ? (int) floor((float) $row['blitz_price']) : null,
            'status' => $row['status'],
            'starts_at' => $row['starts_at'],
            'ends_at' => $row['ends_at'],
            'current_price' => $currentPrice,
            'current_bid_user_id' => $currentBidUserId,
            'bid_count' => $bidCount,
            'min_bid' => $currentBid ? $currentPrice + (int) floor((float) $row['bid_step']) : (int) floor((float) $row['start_price']),
            'winner_last4' => $this->getLast4($row['winner_phone'] ?? ''),
            'winning_amount' => $row['winning_amount'] !== null ? (int) floor((float) $row['winning_amount']) : null,
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
        $previousBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;
        $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : (int) floor((float) $lot['start_price']);
        $minBid = $currentBid ? $currentPrice + (int) floor((float) $lot['bid_step']) : (int) floor((float) $lot['start_price']);
        $finalAmount = $amount !== null ? (int) floor((float) $amount) : $minBid;

        if ($finalAmount < $minBid) {
            throw new RuntimeException(sprintf('Минимальная ставка: %d ₽ (шаг: %d ₽)', $minBid, (int) floor((float) $lot['bid_step'])));
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

        if ($previousBidUserId && $previousBidUserId !== $userId) {
            $this->notifyOutbid($previousBidUserId);
        }

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

        $currentBidModel = new AuctionBid();
        $currentBid = $currentBidModel->getCurrentBid($lotId);
        $previousBidUserId = $currentBid ? (int) $currentBid['user_id'] : null;

        $stmt = $this->db->prepare(
            'INSERT INTO auction_bids (lot_id, user_id, amount) VALUES (:lot_id, :user_id, :amount)'
        );
        $stmt->execute([
            'lot_id' => $lotId,
            'user_id' => $userId,
            'amount' => (int) floor((float) $lot['blitz_price']),
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

        $this->updateWinnerProductPrice($lotId, (int) floor((float) $lot['blitz_price']));
        if ($previousBidUserId && $previousBidUserId !== $userId) {
            $this->notifyOutbid($previousBidUserId);
        }

        (new AuctionEvent())->log($lotId, 'blitz', $bidId, $userId, null);

        return [
            'bid_id' => $bidId,
            'amount' => (int) floor((float) $lot['blitz_price']),
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

        if ($winningBidId && $currentBid) {
            $this->updateWinnerProductPrice($lotId, (int) floor((float) $currentBid['amount']));
        }

        (new AuctionEvent())->log($lotId, 'finished', $winningBidId, $winnerUserId, null);
    }

    public function getPendingWinnerLots(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, product_id
            FROM auction_lots
            WHERE status = 'finished'
              AND winner_user_id = :user_id
              AND winner_cart_added_at IS NULL
              AND product_id IS NOT NULL"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'product_id' => (int) $row['product_id'],
            ];
        }, $rows);
    }

    public function markWinnerCartAdded(int $lotId): void
    {
        $stmt = $this->db->prepare('UPDATE auction_lots SET winner_cart_added_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
    }

    public function getUserActiveParticipation(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.id, l.title, l.ends_at, l.status, MAX(b.amount) AS user_amount
            FROM auction_bids b
            JOIN auction_lots l ON l.id = b.lot_id
            WHERE b.user_id = :user_id
              AND b.status = 'active'
              AND l.status = 'active'
            GROUP BY l.id, l.title, l.ends_at, l.status
            ORDER BY l.ends_at DESC, l.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $bidModel = new AuctionBid();

        return array_map(function (array $row) use ($bidModel): array {
            $currentBid = $bidModel->getCurrentBid((int) $row['id']);
            $currentPrice = $currentBid ? (int) floor((float) $currentBid['amount']) : null;

            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'ends_at' => $row['ends_at'],
                'status' => $row['status'],
                'user_amount' => $row['user_amount'] !== null ? (int) floor((float) $row['user_amount']) : null,
                'current_price' => $currentPrice,
            ];
        }, $rows);
    }

    public function getUserHistoryParticipation(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.id, l.title, l.ends_at, l.status, l.winner_user_id, wb.amount AS winning_amount, MAX(b.amount) AS user_amount
            FROM auction_bids b
            JOIN auction_lots l ON l.id = b.lot_id
            LEFT JOIN auction_bids wb ON wb.id = l.winning_bid_id
            WHERE b.user_id = :user_id
              AND b.status = 'active'
              AND l.status = 'finished'
            GROUP BY l.id, l.title, l.ends_at, l.status, l.winner_user_id, wb.amount
            ORDER BY l.ends_at DESC, l.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row) use ($userId): array {
            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'ends_at' => $row['ends_at'],
                'status' => $row['status'],
                'user_amount' => $row['user_amount'] !== null ? (int) floor((float) $row['user_amount']) : null,
                'winning_amount' => $row['winning_amount'] !== null ? (int) floor((float) $row['winning_amount']) : null,
                'is_winner' => (int) ($row['winner_user_id'] ?? 0) === $userId,
            ];
        }, $rows);
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

    private function updateWinnerProductPrice(int $lotId, int $price): void
    {
        $stmt = $this->db->prepare('SELECT product_id FROM auction_lots WHERE id = :id');
        $stmt->execute(['id' => $lotId]);
        $productId = (int) $stmt->fetchColumn();
        if ($productId <= 0) {
            return;
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);
        if (!$product) {
            return;
        }

        $productModel->updateCustom($productId, [
            'name' => $product['name'],
            'alt_name' => $product['alt_name'] ?? null,
            'description' => $product['description'],
            'price' => $price,
            'photo_url' => $product['photo_url'],
            'photo_url_secondary' => $product['photo_url_secondary'] ?? null,
            'photo_url_tertiary' => $product['photo_url_tertiary'] ?? null,
            'category' => $product['category'] ?? 'main',
            'product_type' => $product['product_type'] ?? 'auction',
            'is_active' => (int) ($product['is_active'] ?? 1),
        ]);
    }

    private function notifyOutbid(int $userId): void
    {
        $userModel = new User();
        $user = $userModel->findById($userId);
        $chatId = (int) ($user['telegram_chat_id'] ?? 0);
        if ($chatId <= 0) {
            return;
        }

        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $token = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');
        if ($token === '') {
            return;
        }

        $telegram = new Telegram($token);
        $telegram->sendMessage(
            $chatId,
            'Вашу ставку перебили! Перейти на страницу с аукционом https://bunchflowers.ru/promo'
        );
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

<?php
// app/models/Lottery.php

class Lottery extends Model
{
    private const RESERVE_MINUTES = 10;

    public function createLottery(array $payload): int
    {
        $productModel = new Product();

        $this->db->beginTransaction();

        try {
            $productId = $productModel->createCustom([
                'name' => $payload['title'],
                'alt_name' => null,
                'description' => $payload['prize_description'],
                'price' => $payload['ticket_price'],
                'article' => null,
                'photo_url' => $payload['photo_url'],
                'photo_url_secondary' => null,
                'photo_url_tertiary' => null,
                'category' => 'main',
                'product_type' => 'lottery',
                'is_base' => 0,
                'is_active' => 1,
                'sort_order' => 0,
            ]);

            $stmt = $this->db->prepare(
                'INSERT INTO lotteries (product_id, prize_description, ticket_price, tickets_total, draw_at, status) VALUES (:product_id, :prize_description, :ticket_price, :tickets_total, :draw_at, :status)'
            );
            $stmt->execute([
                'product_id' => $productId,
                'prize_description' => $payload['prize_description'],
                'ticket_price' => $payload['ticket_price'],
                'tickets_total' => $payload['tickets_total'],
                'draw_at' => $payload['draw_at'],
                'status' => $payload['status'] ?? 'active',
            ]);

            $lotteryId = (int) $this->db->lastInsertId();

            $ticketStmt = $this->db->prepare(
                'INSERT INTO lottery_tickets (lottery_id, ticket_number) VALUES (:lottery_id, :ticket_number)'
            );
            $logStmt = $this->db->prepare(
                "INSERT INTO lottery_ticket_logs (ticket_id, action) VALUES (:ticket_id, 'created')"
            );

            for ($i = 1; $i <= $payload['tickets_total']; $i++) {
                $ticketStmt->execute([
                    'lottery_id' => $lotteryId,
                    'ticket_number' => $i,
                ]);
                $ticketId = (int) $this->db->lastInsertId();
                $logStmt->execute(['ticket_id' => $ticketId]);
            }

            $this->db->commit();
            return $lotteryId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPromoList(): array
    {
        $sql = <<<'SQL'
SELECT l.*, p.name, p.photo_url
FROM lotteries l
JOIN products p ON p.id = l.product_id
ORDER BY l.created_at DESC
SQL;

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        return array_values(array_filter(array_map(function (array $row): array {
            $row['status'] = $this->finalizeIfReady($row);
            $stats = $this->getTicketStats((int) $row['id'], (int) $row['tickets_total']);
            $drawAt = $row['draw_at'] ? new DateTime($row['draw_at']) : null;

            return [
                'id' => (int) $row['id'],
                'title' => $row['name'],
                'photo' => $row['photo_url'],
                'prize_description' => $row['prize_description'],
                'ticket_price' => (float) $row['ticket_price'],
                'tickets_total' => (int) $row['tickets_total'],
                'tickets_free' => $stats['free'],
                'tickets_reserved' => $stats['reserved'],
                'tickets_paid' => $stats['paid'],
                'draw_at' => $drawAt ? $drawAt->format('d.m H:i') : 'Дата уточняется',
                'draw_at_iso' => $drawAt ? $drawAt->format(DateTimeInterface::ATOM) : null,
                'status' => $this->resolveStatus($row['status'], $stats),
            ];
        }, $rows), static function (array $row): bool {
            return $row['status'] === 'active';
        }));
    }

    public function getAdminList(): array
    {
        $sql = <<<'SQL'
SELECT l.*, p.name, p.photo_url
FROM lotteries l
JOIN products p ON p.id = l.product_id
ORDER BY l.created_at DESC
SQL;

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row): array {
            $row['status'] = $this->finalizeIfReady($row, false);
            $stats = $this->getTicketStats((int) $row['id'], (int) $row['tickets_total']);
            $drawAt = $row['draw_at'] ? new DateTime($row['draw_at']) : null;

            return [
                'id' => (int) $row['id'],
                'title' => $row['name'],
                'photo' => $row['photo_url'],
                'ticket_price' => (float) $row['ticket_price'],
                'tickets_total' => (int) $row['tickets_total'],
                'tickets_free' => $stats['free'],
                'tickets_reserved' => $stats['reserved'],
                'tickets_paid' => $stats['paid'],
                'draw_at' => $drawAt ? $drawAt->format('d.m.Y H:i') : 'Дата уточняется',
                'draw_at_raw' => $row['draw_at'],
                'status' => $this->resolveStatus($row['status'], $stats),
            ];
        }, $rows);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, p.name, p.photo_url FROM lotteries l JOIN products p ON p.id = l.product_id WHERE l.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['status'] = $this->finalizeIfReady($row);
        $stats = $this->getTicketStats((int) $row['id'], (int) $row['tickets_total']);

        return [
            'id' => (int) $row['id'],
            'title' => $row['name'],
            'photo' => $row['photo_url'],
            'prize_description' => $row['prize_description'],
            'ticket_price' => (float) $row['ticket_price'],
            'tickets_total' => (int) $row['tickets_total'],
            'tickets_free' => $stats['free'],
            'tickets_reserved' => $stats['reserved'],
            'tickets_paid' => $stats['paid'],
            'draw_at' => $row['draw_at'],
            'status_raw' => $row['status'],
            'status' => $this->resolveStatus($row['status'], $stats),
        ];
    }

    public function updateLottery(int $lotteryId, array $payload): void
    {
        $stmt = $this->db->prepare('SELECT * FROM lotteries WHERE id = :id');
        $stmt->execute(['id' => $lotteryId]);
        $existing = $stmt->fetch();
        if (!$existing) {
            throw new RuntimeException('Розыгрыш не найден');
        }

        $productId = (int) $existing['product_id'];
        $productModel = new Product();
        $productModel->updateCustom($productId, [
            'name' => $payload['title'],
            'alt_name' => null,
            'description' => $payload['prize_description'],
            'price' => $payload['ticket_price'],
            'photo_url' => $payload['photo_url'],
            'photo_url_secondary' => null,
            'photo_url_tertiary' => null,
            'category' => 'main',
            'product_type' => 'lottery',
            'is_active' => 1,
        ]);

        $stmt = $this->db->prepare(
            'UPDATE lotteries
            SET prize_description = :prize_description,
                ticket_price = :ticket_price,
                tickets_total = :tickets_total,
                draw_at = :draw_at,
                status = :status
            WHERE id = :id'
        );
        $stmt->execute([
            'id' => $lotteryId,
            'prize_description' => $payload['prize_description'],
            'ticket_price' => $payload['ticket_price'],
            'tickets_total' => $payload['tickets_total'],
            'draw_at' => $payload['draw_at'],
            'status' => $payload['status'] ?? 'active',
        ]);

        $currentTotal = (int) $existing['tickets_total'];
        $newTotal = (int) $payload['tickets_total'];
        if ($newTotal > $currentTotal) {
            $ticketStmt = $this->db->prepare(
                'INSERT INTO lottery_tickets (lottery_id, ticket_number) VALUES (:lottery_id, :ticket_number)'
            );
            $logStmt = $this->db->prepare(
                "INSERT INTO lottery_ticket_logs (ticket_id, action) VALUES (:ticket_id, 'created')"
            );
            for ($i = $currentTotal + 1; $i <= $newTotal; $i++) {
                $ticketStmt->execute([
                    'lottery_id' => $lotteryId,
                    'ticket_number' => $i,
                ]);
                $ticketId = (int) $this->db->lastInsertId();
                $logStmt->execute(['ticket_id' => $ticketId]);
            }
        } elseif ($newTotal < $currentTotal) {
            throw new RuntimeException('Нельзя уменьшить количество билетов у активного розыгрыша');
        }
    }

    public function getReserveTtlMinutes(): int
    {
        return self::RESERVE_MINUTES;
    }

    private function getTicketStats(int $lotteryId, int $total): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, COUNT(*) AS cnt FROM lottery_tickets WHERE lottery_id = :lottery_id GROUP BY status'
        );
        $stmt->execute(['lottery_id' => $lotteryId]);
        $rows = $stmt->fetchAll();

        $stats = [
            'total' => $total,
            'free' => $total,
            'reserved' => 0,
            'paid' => 0,
        ];

        foreach ($rows as $row) {
            $status = $row['status'];
            $count = (int) $row['cnt'];
            $stats[$status] = $count;
        }

        $stats['free'] = max(0, $total - $stats['reserved'] - $stats['paid']);

        return $stats;
    }

    private function resolveStatus(string $status, array $stats): string
    {
        if ($status === 'finished') {
            return 'finished';
        }

        if ($stats['free'] <= 0) {
            return 'sold_out';
        }

        return 'active';
    }

    private function finalizeIfReady(array $lotteryRow, bool $notify = true): string
    {
        $status = $lotteryRow['status'] ?? 'active';
        if ($status === 'finished') {
            return 'finished';
        }

        $stats = $this->getTicketStats((int) $lotteryRow['id'], (int) $lotteryRow['tickets_total']);
        $drawAt = $lotteryRow['draw_at'] ? new DateTime($lotteryRow['draw_at']) : null;
        $isTimeOver = $drawAt ? new DateTime() >= $drawAt : false;
        $shouldFinish = $stats['free'] <= 0 || $isTimeOver;

        if (!$shouldFinish) {
            return $status;
        }

        $stmt = $this->db->prepare("UPDATE lotteries SET status = 'finished' WHERE id = :id");
        $stmt->execute(['id' => (int) $lotteryRow['id']]);

        if ($notify) {
            $this->notifyParticipants((int) $lotteryRow['id']);
        }

        return 'finished';
    }

    private function notifyParticipants(int $lotteryId): void
    {
        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $token = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');
        if ($token === '') {
            return;
        }

        $stmt = $this->db->prepare(
            'SELECT DISTINCT u.telegram_chat_id
            FROM lottery_tickets t
            JOIN users u ON u.id = t.user_id
            WHERE t.lottery_id = :lottery_id AND t.user_id IS NOT NULL AND u.telegram_chat_id IS NOT NULL'
        );
        $stmt->execute(['lottery_id' => $lotteryId]);
        $chatIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$chatIds) {
            return;
        }

        $telegram = new Telegram($token);
        $message = 'Розыгрыш состоялся! https://bunchflowers.ru/?page=promo';

        foreach ($chatIds as $chatId) {
            $telegram->sendMessage((int) $chatId, $message);
        }
    }
}

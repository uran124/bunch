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
                'description' => $payload['prize_description'],
                'price' => $payload['ticket_price'],
                'article' => null,
                'photo_url' => $payload['photo_url'],
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

        return array_map(function (array $row): array {
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
                'draw_at' => $drawAt ? $drawAt->format('d.m H:i') : 'Дата уточняется',
                'status' => $this->resolveStatus($row['status'], $stats),
            ];
        }, $rows);
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

        $stats = $this->getTicketStats((int) $row['id'], (int) $row['tickets_total']);

        return [
            'id' => (int) $row['id'],
            'title' => $row['name'],
            'photo' => $row['photo_url'],
            'ticket_price' => (float) $row['ticket_price'],
            'tickets_total' => (int) $row['tickets_total'],
            'tickets_free' => $stats['free'],
            'tickets_reserved' => $stats['reserved'],
            'tickets_paid' => $stats['paid'],
            'draw_at' => $row['draw_at'],
            'status' => $this->resolveStatus($row['status'], $stats),
        ];
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
}

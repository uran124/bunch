<?php
// app/models/LotteryTicket.php

class LotteryTicket extends Model
{
    private const RESERVE_MINUTES = 10;

    public function listTickets(int $lotteryId, ?int $userId = null): array
    {
        $this->releaseExpired($lotteryId);

        $stmt = $this->db->prepare(
            'SELECT id, ticket_number, status, phone_last4, user_id FROM lottery_tickets WHERE lottery_id = :lottery_id ORDER BY ticket_number ASC'
        );
        $stmt->execute(['lottery_id' => $lotteryId]);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row) use ($userId): array {
            return [
                'id' => (int) $row['id'],
                'ticket_number' => (int) $row['ticket_number'],
                'status' => $row['status'],
                'phone_last4' => $row['phone_last4'],
                'is_mine' => $userId !== null && (int) $row['user_id'] === $userId,
            ];
        }, $rows);
    }

    public function reserveTicket(int $lotteryId, ?int $ticketNumber, int $userId, string $phoneLast4): array
    {
        $this->db->beginTransaction();

        try {
            $this->releaseExpired($lotteryId);

            if ($ticketNumber !== null) {
                $stmt = $this->db->prepare(
                    "SELECT id, ticket_number FROM lottery_tickets WHERE lottery_id = :lottery_id AND ticket_number = :ticket_number AND status = 'free' FOR UPDATE"
                );
                $stmt->execute([
                    'lottery_id' => $lotteryId,
                    'ticket_number' => $ticketNumber,
                ]);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT id, ticket_number FROM lottery_tickets WHERE lottery_id = :lottery_id AND status = 'free' ORDER BY RAND() LIMIT 1 FOR UPDATE"
                );
                $stmt->execute(['lottery_id' => $lotteryId]);
            }

            $ticket = $stmt->fetch();

            if (!$ticket) {
                throw new RuntimeException('Свободные билеты закончились');
            }

            $update = $this->db->prepare(
                "UPDATE lottery_tickets SET status = 'reserved', user_id = :user_id, phone_last4 = :phone_last4, reserved_at = NOW() WHERE id = :id AND status = 'free'"
            );
            $update->execute([
                'user_id' => $userId,
                'phone_last4' => $phoneLast4,
                'id' => $ticket['id'],
            ]);

            if ($update->rowCount() === 0) {
                throw new RuntimeException('Билет уже занят');
            }

            $this->logAction((int) $ticket['id'], 'reserved', $userId);

            $this->db->commit();
            return [
                'id' => (int) $ticket['id'],
                'ticket_number' => (int) $ticket['ticket_number'],
                'status' => 'reserved',
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markPaid(int $ticketId, int $userId, string $phoneLast4): void
    {
        $stmt = $this->db->prepare(
            "UPDATE lottery_tickets SET status = 'paid', paid_at = NOW(), phone_last4 = :phone_last4 WHERE id = :id AND status = 'reserved' AND user_id = :user_id"
        );
        $stmt->execute([
            'phone_last4' => $phoneLast4,
            'id' => $ticketId,
            'user_id' => $userId,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Оплата недоступна для этого билета');
        }

        $this->logAction($ticketId, 'paid', $userId);
    }

    public function releaseExpired(int $lotteryId): void
    {
        $threshold = (new DateTime())
            ->modify('-' . self::RESERVE_MINUTES . ' minutes')
            ->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "SELECT id, user_id FROM lottery_tickets WHERE lottery_id = :lottery_id AND status = 'reserved' AND reserved_at < :threshold"
        );
        $stmt->execute([
            'lottery_id' => $lotteryId,
            'threshold' => $threshold,
        ]);
        $expired = $stmt->fetchAll();

        if (!$expired) {
            return;
        }

        $this->db->beginTransaction();

        try {
            $reset = $this->db->prepare(
                'UPDATE lottery_tickets SET status = \"free\", user_id = NULL, phone_last4 = NULL, reserved_at = NULL WHERE id = :id'
            );
            foreach ($expired as $ticket) {
                $reset->execute(['id' => $ticket['id']]);
                $this->logAction((int) $ticket['id'], 'released', $ticket['user_id'] ? (int) $ticket['user_id'] : null);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function logAction(int $ticketId, string $action, ?int $userId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lottery_ticket_logs (ticket_id, action, user_id) VALUES (:ticket_id, :action, :user_id)'
        );
        $stmt->execute([
            'ticket_id' => $ticketId,
            'action' => $action,
            'user_id' => $userId,
        ]);
    }
}

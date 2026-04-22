<?php
// app/models/CashbackTransaction.php

class CashbackTransaction extends Model
{
    private static bool $tableEnsured = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function add(int $userId, ?int $orderId, string $type, int $amount, ?string $description = null): int
    {
        if ($userId <= 0 || $amount <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO cashback_transactions (user_id, order_id, type, amount, description, created_at) VALUES (:user_id, :order_id, :type, :amount, :description, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function hasEarnForOrder(int $orderId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM cashback_transactions WHERE order_id = :order_id AND type = 'earn'"
        );
        $stmt->execute(['order_id' => $orderId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getEarnedForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, order_id, amount, description, created_at FROM cashback_transactions WHERE user_id = :user_id AND type = 'earn' ORDER BY created_at DESC, id DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function rollbackOrderEarn(int $orderId): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id, user_id, amount FROM cashback_transactions WHERE order_id = :order_id AND type = 'earn' FOR UPDATE"
            );
            $stmt->execute(['order_id' => $orderId]);
            $rows = $stmt->fetchAll();

            if ($rows === []) {
                $this->db->commit();
                return 0;
            }

            $totalRollback = 0;
            foreach ($rows as $row) {
                $userId = (int) ($row['user_id'] ?? 0);
                $amount = (int) ($row['amount'] ?? 0);
                if ($userId <= 0 || $amount <= 0) {
                    continue;
                }
                $totalRollback += $amount;
                $updateStmt = $this->db->prepare(
                    'UPDATE users SET tulip_balance = GREATEST(tulip_balance - :amount, 0), updated_at = NOW() WHERE id = :user_id'
                );
                $updateStmt->execute([
                    'amount' => $amount,
                    'user_id' => $userId,
                ]);
            }

            $deleteStmt = $this->db->prepare(
                "DELETE FROM cashback_transactions WHERE order_id = :order_id AND type = 'earn'"
            );
            $deleteStmt->execute(['order_id' => $orderId]);

            $this->db->commit();
            return $totalRollback;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteEarnForUser(int $userId, int $transactionId): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id, amount FROM cashback_transactions WHERE id = :id AND user_id = :user_id AND type = 'earn' LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([
                'id' => $transactionId,
                'user_id' => $userId,
            ]);
            $row = $stmt->fetch();

            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            $amount = (int) ($row['amount'] ?? 0);
            $balanceStmt = $this->db->prepare('SELECT tulip_balance FROM users WHERE id = :id FOR UPDATE');
            $balanceStmt->execute(['id' => $userId]);
            $balance = (int) $balanceStmt->fetchColumn();

            if ($amount > $balance) {
                $this->db->rollBack();
                return false;
            }

            $updateStmt = $this->db->prepare(
                'UPDATE users SET tulip_balance = tulip_balance - :amount, updated_at = NOW() WHERE id = :id'
            );
            $updateStmt->execute([
                'amount' => $amount,
                'id' => $userId,
            ]);

            $deleteStmt = $this->db->prepare('DELETE FROM cashback_transactions WHERE id = :id');
            $deleteStmt->execute(['id' => $transactionId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function ensureTable(): void
    {
        if (self::$tableEnsured || $this->db->inTransaction()) {
            return;
        }

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS cashback_transactions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                order_id INT UNSIGNED NULL,
                type ENUM('earn', 'spend') NOT NULL,
                amount INT NOT NULL,
                description VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cashback_tx_user (user_id, created_at),
                INDEX idx_cashback_tx_order (order_id, type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        self::$tableEnsured = true;
    }
}


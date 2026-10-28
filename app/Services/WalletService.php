<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

class WalletService
{
    /**
     * Atomically debits a user's wallet with row locking and transaction logging
     */
    public static function debit(
        int $userId,
        float $amount,
        string $type,
        ?int $referenceId,
        ?string $referenceType,
        string $description
    ): float {
        if ($amount <= 0) {
            throw new RuntimeException("Debit amount must be greater than zero.");
        }

        return Database::transaction(function (PDO $pdo) use ($userId, $amount, $type, $referenceId, $referenceType, $description) {
            // Row lock on the user to prevent concurrent race conditions
            $stmt = $pdo->prepare("SELECT balance, spent FROM users WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new RuntimeException("User not found.");
            }

            $currentBalance = (float)$user['balance'];
            if ($currentBalance < $amount) {
                throw new RuntimeException("Insufficient wallet balance. Please add funds to proceed.");
            }

            $newBalance = $currentBalance - $amount;
            $newSpent = (float)$user['spent'] + $amount;

            // Update user balance
            $update = $pdo->prepare("
                UPDATE users 
                SET balance = :balance, spent = :spent, updated_at = NOW() 
                WHERE id = :id AND balance >= :amount
            ");
            $success = $update->execute([
                'balance' => $newBalance,
                'spent' => $newSpent,
                'id' => $userId,
                'amount' => $amount,
            ]);

            if (!$success || $update->rowCount() === 0) {
                throw new RuntimeException("Concurrent transaction conflict. Please retry.");
            }

            // Record immutable ledger entry
            $log = $pdo->prepare("
                INSERT INTO wallet_transactions 
                (user_id, amount, balance_before, balance_after, type, reference_id, reference_type, description, created_at)
                VALUES 
                (:user_id, :amount, :balance_before, :balance_after, :type, :ref_id, :ref_type, :description, NOW())
            ");
            $log->execute([
                'user_id' => $userId,
                'amount' => -$amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'type' => $type,
                'ref_id' => $referenceId,
                'ref_type' => $referenceType,
                'description' => $description,
            ]);

            return $newBalance;
        });
    }

    /**
     * Atomically credits a user's wallet with row locking and transaction logging
     */
    public static function credit(
        int $userId,
        float $amount,
        string $type,
        ?int $referenceId,
        ?string $referenceType,
        string $description
    ): float {
        if ($amount <= 0) {
            throw new RuntimeException("Credit amount must be greater than zero.");
        }

        return Database::transaction(function (PDO $pdo) use ($userId, $amount, $type, $referenceId, $referenceType, $description) {
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new RuntimeException("User not found.");
            }

            $currentBalance = (float)$user['balance'];
            $newBalance = $currentBalance + $amount;

            $update = $pdo->prepare("UPDATE users SET balance = :balance, updated_at = NOW() WHERE id = :id");
            $update->execute([
                'balance' => $newBalance,
                'id' => $userId,
            ]);

            $log = $pdo->prepare("
                INSERT INTO wallet_transactions 
                (user_id, amount, balance_before, balance_after, type, reference_id, reference_type, description, created_at)
                VALUES 
                (:user_id, :amount, :balance_before, :balance_after, :type, :ref_id, :ref_type, :description, NOW())
            ");
            $log->execute([
                'user_id' => $userId,
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'type' => $type,
                'ref_id' => $referenceId,
                'ref_type' => $referenceType,
                'description' => $description,
            ]);

            return $newBalance;
        });
    }

    /**
     * Manual admin balance adjustment with audit trail
     */
    public static function adminAdjust(int $userId, float $amount, string $reason, int $adminId): float
    {
        if ($amount > 0) {
            return self::credit($userId, $amount, 'manual_adjustment', $adminId, 'admin_user', "Admin Adjustment: " . $reason);
        } elseif ($amount < 0) {
            return self::debit($userId, abs($amount), 'manual_adjustment', $adminId, 'admin_user', "Admin Adjustment: " . $reason);
        }
        return (float)Database::pdo()->query("SELECT balance FROM users WHERE id = " . (int)$userId)->fetchColumn();
    }
}

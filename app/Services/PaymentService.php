<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Security;
use PDO;
use RuntimeException;

class PaymentService
{
    public static function createPayment(int $userId, int $methodId, float $amount): array
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $methodId]);
        $method = $stmt->fetch();

        if (!$method) {
            throw new RuntimeException("Selected payment method is currently unavailable.");
        }

        if ($amount < (float)$method['min_amount']) {
            throw new RuntimeException("Minimum deposit amount is $" . number_format((float)$method['min_amount'], 2));
        }

        if ($amount > (float)$method['max_amount']) {
            throw new RuntimeException("Maximum deposit amount is $" . number_format((float)$method['max_amount'], 2));
        }

        $feePercent = (float)$method['fee_percentage'];
        $fee = round(($amount * $feePercent) / 100, 4);
        $netAmount = round($amount + $fee, 4);

        $bonusPercent = (float)$method['bonus_percentage'];
        $bonusAmount = round(($amount * $bonusPercent) / 100, 4);

        $txnCode = 'TXN-' . strtoupper(bin2hex(random_bytes(6))) . '-' . date('ymd');

        $insert = $pdo->prepare("
            INSERT INTO payments 
            (user_id, payment_method_id, transaction_code, amount, fee, net_amount, bonus_amount, status, created_at)
            VALUES 
            (:uid, :mid, :code, :amount, :fee, :net, :bonus, 'pending', NOW())
        ");
        $insert->execute([
            'uid' => $userId,
            'mid' => $methodId,
            'code' => $txnCode,
            'amount' => $amount,
            'fee' => $fee,
            'net' => $netAmount,
            'bonus' => $bonusAmount,
        ]);

        $paymentId = (int)$pdo->lastInsertId();

        return [
            'payment_id' => $paymentId,
            'transaction_code' => $txnCode,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'bonus_amount' => $bonusAmount,
            'method_name' => $method['name'],
            'method_code' => $method['code'],
            'config' => json_decode($method['config_json'] ?? '{}', true) ?: [],
        ];
    }

    /**
     * Idempotently completes a payment and credits the user's wallet
     */
    public static function completePayment(string $transactionCode, array $gatewayData = []): bool
    {
        return Database::transaction(function (PDO $pdo) use ($transactionCode, $gatewayData) {
            $stmt = $pdo->prepare("
                SELECT p.*, pm.name as method_name 
                FROM payments p
                INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
                WHERE p.transaction_code = :code 
                FOR UPDATE
            ");
            $stmt->execute(['code' => $transactionCode]);
            $payment = $stmt->fetch();

            if (!$payment) {
                return false;
            }

            // Idempotency: If already completed, return true without double crediting
            if ($payment['status'] === 'completed') {
                return true;
            }

            $creditTotal = (float)$payment['amount'] + (float)$payment['bonus_amount'];

            // Update payment record
            $up = $pdo->prepare("
                UPDATE payments 
                SET status = 'completed', gateway_response = :resp, updated_at = NOW() 
                WHERE id = :id
            ");
            $up->execute([
                'resp' => json_encode($gatewayData),
                'id' => $payment['id'],
            ]);

            // Credit wallet atomically
            WalletService::credit(
                (int)$payment['user_id'],
                $creditTotal,
                'deposit',
                (int)$payment['id'],
                'payment',
                "Deposit via {$payment['method_name']} [Ref: {$transactionCode}]" .
                ($payment['bonus_amount'] > 0 ? " (Bonus: +$" . number_format((float)$payment['bonus_amount'], 2) . ")" : "")
            );

            // Handle referral commission if applicable
            ReferralService::awardDepositCommission((int)$payment['user_id'], (float)$payment['amount']);

            return true;
        });
    }

    public static function failPayment(string $transactionCode, string $reason): void
    {
        $stmt = Database::pdo()->prepare("
            UPDATE payments 
            SET status = 'failed', gateway_response = :reason, updated_at = NOW() 
            WHERE transaction_code = :code AND status = 'pending'
        ");
        $stmt->execute([
            'reason' => json_encode(['failure_reason' => $reason]),
            'code' => $transactionCode,
        ]);
    }
}

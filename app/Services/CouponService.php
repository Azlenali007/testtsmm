<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

class CouponService
{
    public static function redeem(int $userId, string $code): float
    {
        $code = strtoupper(trim($code));

        return Database::transaction(function (PDO $pdo) use ($userId, $code) {
            $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = :code AND status = 'active' FOR UPDATE");
            $stmt->execute(['code' => $code]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                throw new RuntimeException("Invalid or inactive coupon code.");
            }

            if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < time()) {
                throw new RuntimeException("This coupon code has expired.");
            }

            if ((int)$coupon['used_count'] >= (int)$coupon['max_uses']) {
                throw new RuntimeException("This coupon has reached its maximum redemptions limit.");
            }

            // Check if user already redeemed
            $usedCheck = $pdo->prepare("SELECT id FROM coupon_usage WHERE coupon_id = :cid AND user_id = :uid LIMIT 1");
            $usedCheck->execute(['cid' => $coupon['id'], 'uid' => $userId]);
            if ($usedCheck->fetch()) {
                throw new RuntimeException("You have already redeemed this coupon code.");
            }

            $creditAmount = (float)$coupon['value'];

            // Increment usage
            $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = :id")->execute(['id' => $coupon['id']]);

            // Record usage
            $pdo->prepare("INSERT INTO coupon_usage (coupon_id, user_id, discount_amount, created_at) VALUES (:cid, :uid, :amt, NOW())")
                ->execute(['cid' => $coupon['id'], 'uid' => $userId, 'amt' => $creditAmount]);

            // Credit wallet
            WalletService::credit(
                $userId,
                $creditAmount,
                'coupon_credit',
                (int)$coupon['id'],
                'coupon',
                "Coupon Redeemed: {$code} (+$" . number_format($creditAmount, 2) . ")"
            );

            return $creditAmount;
        });
    }
}

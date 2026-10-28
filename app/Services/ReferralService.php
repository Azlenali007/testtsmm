<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class ReferralService
{
    public static function awardDepositCommission(int $userId, float $depositAmount): void
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("SELECT * FROM referrals WHERE referred_id = :uid AND status = 'active' LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $referral = $stmt->fetch();

        if (!$referral) {
            return;
        }

        $rate = (float)$referral['commission_rate'];
        if ($rate <= 0) {
            return;
        }

        $commission = round(($depositAmount * $rate) / 100, 4);
        if ($commission <= 0) {
            return;
        }

        $referrerId = (int)$referral['referrer_id'];

        // Credit referrer wallet
        WalletService::credit(
            $referrerId,
            $commission,
            'referral_payout',
            $userId,
            'referral',
            "Referral commission ({$rate}%) from user #{$userId} deposit of $" . number_format($depositAmount, 2)
        );

        // Update total earned
        $up = $pdo->prepare("UPDATE referrals SET total_earned = total_earned + :comm WHERE id = :id");
        $up->execute(['comm' => $commission, 'id' => $referral['id']]);
    }
}

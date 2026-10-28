<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class AffiliateController
{
    public function index(): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        // Get total referred users count and earnings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_referred, COALESCE(SUM(total_earned), 0) as total_earnings
            FROM referrals 
            WHERE referrer_id = :uid
        ");
        $stmt->execute(['uid' => $user['id']]);
        $stats = $stmt->fetch();

        // Payout history
        $payoutStmt = $pdo->prepare("SELECT * FROM referral_payouts WHERE user_id = :uid ORDER BY id DESC");
        $payoutStmt->execute(['uid' => $user['id']]);
        $payouts = $payoutStmt->fetchAll();

        // Get system referral commission setting
        $commRate = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_commission_rate'")->fetchColumn() ?: 5.0);
        $minPayout = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_min_payout'")->fetchColumn() ?: 20.0);

        $referralLink = (require __DIR__ . '/../../config/app.php')['url'] . '/register?ref=' . $user['username'];

        View::render('user/affiliates', [
            'title' => 'Affiliate & Referral Program',
            'user' => $user,
            'stats' => $stats,
            'payouts' => $payouts,
            'commissionRate' => $commRate,
            'minPayout' => $minPayout,
            'referralLink' => $referralLink,
        ], 'main');
    }

    public function requestPayout(): void
    {
        $request = new Request();
        $user = Auth::user();
        $amount = (float)$request->input('amount');
        $details = trim((string)$request->input('payment_details'));

        $pdo = Database::pdo();
        $minPayout = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_min_payout'")->fetchColumn() ?: 20.0);

        if ($amount < $minPayout) {
            Session::setFlash('error', "Minimum referral payout is $" . number_format($minPayout, 2));
            Response::redirect('/affiliates');
        }

        if (empty($details)) {
            Session::setFlash('error', "Please specify payment details (PayPal email, Crypto address, etc.).");
            Response::redirect('/affiliates');
        }

        $pdo->prepare("
            INSERT INTO referral_payouts (user_id, amount, payment_details, status, created_at)
            VALUES (:uid, :amount, :details, 'pending', NOW())
        ")->execute([
            'uid' => $user['id'],
            'amount' => $amount,
            'details' => $details,
        ]);

        Session::setFlash('success', 'Your payout request has been submitted for administrative review.');
        Response::redirect('/affiliates');
    }
}

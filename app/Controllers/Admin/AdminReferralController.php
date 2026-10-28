<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\WalletService;

class AdminReferralController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $payouts = $pdo->query("
            SELECT rp.*, u.username, u.email as user_email
            FROM referral_payouts rp
            INNER JOIN users u ON u.id = rp.user_id
            ORDER BY FIELD(rp.status, 'pending', 'approved', 'rejected'), rp.id DESC
        ")->fetchAll();

        $referralPairs = $pdo->query("
            SELECT r.*, u1.username as referrer_name, u2.username as referred_name
            FROM referrals r
            INNER JOIN users u1 ON u1.id = r.referrer_id
            INNER JOIN users u2 ON u2.id = r.referred_id
            ORDER BY r.id DESC
            LIMIT 25
        ")->fetchAll();

        View::render('admin/referrals', [
            'title' => 'Affiliate Payouts & Referrals',
            'payouts' => $payouts,
            'referrals' => $referralPairs,
        ], 'admin');
    }

    public function approve(int $payoutId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT * FROM referral_payouts WHERE id = :id AND status = 'pending' LIMIT 1");
        $stmt->execute(['id' => $payoutId]);
        $payout = $stmt->fetch();

        if (!$payout) {
            Session::setFlash('error', 'Payout request not found or already processed.');
            Response::redirect('/admin/referrals');
        }

        $pdo->prepare("UPDATE referral_payouts SET status = 'approved', updated_at = NOW() WHERE id = :id")
            ->execute(['id' => $payoutId]);

        Security::logAudit("Admin approved referral payout #{$payoutId} for $" . $payout['amount']);
        Session::setFlash('success', "Payout #{$payoutId} approved.");
        Response::redirect('/admin/referrals');
    }

    public function reject(int $payoutId): void
    {
        $request = new Request();
        $reason = trim((string)$request->input('reason', 'Rejected by administrator'));

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT * FROM referral_payouts WHERE id = :id AND status = 'pending' LIMIT 1");
        $stmt->execute(['id' => $payoutId]);
        $payout = $stmt->fetch();

        if (!$payout) {
            Session::setFlash('error', 'Payout request not found or already processed.');
            Response::redirect('/admin/referrals');
        }

        $pdo->prepare("UPDATE referral_payouts SET status = 'rejected', admin_notes = :notes, updated_at = NOW() WHERE id = :id")
            ->execute(['notes' => $reason, 'id' => $payoutId]);

        Security::logAudit("Admin rejected referral payout #{$payoutId}");
        Session::setFlash('success', "Payout #{$payoutId} rejected.");
        Response::redirect('/admin/referrals');
    }
}

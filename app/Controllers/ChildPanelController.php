<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\WalletService;

class ChildPanelController
{
    public function index(): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("SELECT * FROM child_panels WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute(['uid' => $user['id']]);
        $panels = $stmt->fetchAll();

        $price = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'child_panel_price'")->fetchColumn() ?: 25.0);

        View::render('user/child_panels', [
            'title' => 'Rent a Child Panel',
            'user' => $user,
            'panels' => $panels,
            'monthlyPrice' => $price,
        ], 'main');
    }

    public function store(): void
    {
        $request = new Request();
        $user = Auth::user();

        $domain = strtolower(trim((string)$request->input('domain')));
        $adminUser = trim((string)$request->input('admin_user'));
        $adminPass = (string)$request->input('admin_pass');

        if (empty($domain) || empty($adminUser) || strlen($adminPass) < 8) {
            Session::setFlash('error', 'Please enter a valid domain, admin username, and a password with at least 8 characters.');
            Response::redirect('/child-panels');
        }

        $pdo = Database::pdo();
        $price = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'child_panel_price'")->fetchColumn() ?: 25.0);

        // Check if domain exists
        $check = $pdo->prepare("SELECT id FROM child_panels WHERE domain = :domain LIMIT 1");
        $check->execute(['domain' => $domain]);
        if ($check->fetch()) {
            Session::setFlash('error', 'The domain ' . htmlspecialchars($domain) . ' is already registered.');
            Response::redirect('/child-panels');
        }

        try {
            // Debit wallet for 1st month
            WalletService::debit(
                (int)$user['id'],
                $price,
                'order_charge',
                null,
                'child_panel',
                "Child Panel Rental for domain: {$domain}"
            );

            $renewalDate = date('Y-m-d', strtotime('+30 days'));
            $passHash = password_hash($adminPass, PASSWORD_BCRYPT);

            $insert = $pdo->prepare("
                INSERT INTO child_panels 
                (user_id, domain, admin_user, admin_pass_hash, monthly_fee, status, renewal_date, created_at)
                VALUES 
                (:uid, :domain, :admin, :pass, :fee, 'pending', :renewal, NOW())
            ");
            $insert->execute([
                'uid' => $user['id'],
                'domain' => $domain,
                'admin' => $adminUser,
                'pass' => $passHash,
                'fee' => $price,
                'renewal' => $renewalDate,
            ]);

            Session::setFlash('success', "Child panel order for {$domain} placed successfully! Please point your nameservers to ns1.apexpanel.com and ns2.apexpanel.com.");
            Response::redirect('/child-panels');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Response::redirect('/child-panels');
        }
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;

class WalletController
{
    public function index(): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        // Fetch active payment methods
        $methods = $pdo->query("SELECT *, min_amount as min_deposit, max_amount as max_deposit FROM payment_methods WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

        // Fetch recent payments / deposits
        $paymentsStmt = $pdo->prepare("
            SELECT p.*, pm.name as method_name 
            FROM payments p 
            INNER JOIN payment_methods pm ON pm.id = p.payment_method_id 
            WHERE p.user_id = :uid 
            ORDER BY p.id DESC 
            LIMIT 10
        ");
        $paymentsStmt->execute(['uid' => $user['id']]);
        $payments = $paymentsStmt->fetchAll();

        // Fetch wallet transaction ledger entries
        $ledgerStmt = $pdo->prepare("
            SELECT * FROM wallet_transactions 
            WHERE user_id = :uid 
            ORDER BY id DESC 
            LIMIT 25
        ");
        $ledgerStmt->execute(['uid' => $user['id']]);
        $transactions = $ledgerStmt->fetchAll();

        View::render('user/wallet', [
            'title' => 'Wallet & Deposit Funds',
            'user' => $user,
            'paymentMethods' => $methods,
            'recentPayments' => $payments,
            'transactions' => $transactions,
        ], 'main');
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\WalletService;

class AdminUserController
{
    public function index(): void
    {
        $request = new Request();
        $search = trim((string)$request->get('search', ''));
        $status = strtolower((string)$request->get('status', 'all'));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();
        $query = "SELECT u.*, (SELECT r.name FROM roles r INNER JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = u.id LIMIT 1) as role_name FROM users u WHERE 1=1";
        $params = [];

        if ($status !== 'all' && in_array($status, ['active', 'suspended', 'pending'])) {
            $query .= " AND u.status = :status";
            $params['status'] = $status;
        }

        if ($search !== '') {
            $query .= " AND (u.username LIKE :s OR u.email LIKE :s OR u.name LIKE :s OR u.phone LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }

        $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as c";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalUsers = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalUsers / $perPage);

        $query .= " ORDER BY u.id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        View::render('admin/users', [
            'title' => 'User Management',
            'users' => $users,
            'search' => $search,
            'status' => $status,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
        ], 'admin');
    }

    public function edit(int $userId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            Session::setFlash('error', 'User not found.');
            Response::redirect('/admin/users');
        }

        // Fetch user's recent transactions
        $txns = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = :id ORDER BY id DESC LIMIT 10");
        $txns->execute(['id' => $userId]);

        View::render('admin/user_edit', [
            'title' => 'Edit User #' . $userId . ' — ' . $user['username'],
            'targetUser' => $user,
            'transactions' => $txns->fetchAll(),
        ], 'admin');
    }

    public function update(int $userId): void
    {
        $request = new Request();
        $name = trim((string)$request->input('name'));
        $email = trim((string)$request->input('email'));
        $phone = trim((string)$request->input('phone'));
        $status = (string)$request->input('status', 'active');
        $customRate = max(0.0, min(99.0, (float)$request->input('custom_rate', 0.0)));

        $pdo = Database::pdo();

        // Check email collision
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :e AND id != :id LIMIT 1");
        $check->execute(['e' => $email, 'id' => $userId]);
        if ($check->fetch()) {
            Session::setFlash('error', 'That email address is already taken by another client.');
            Response::redirect('/admin/users/edit/' . $userId);
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = :name, email = :email, phone = :phone, status = :status, custom_rate = :rate, updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'status' => in_array($status, ['active', 'suspended', 'pending']) ? $status : 'active',
            'rate' => $customRate,
            'id' => $userId,
        ]);

        Security::logAudit("Admin updated user #{$userId}", Auth::id(), "Email: {$email}, Status: {$status}");
        Session::setFlash('success', 'User profile updated successfully.');
        Response::redirect('/admin/users/edit/' . $userId);
    }

    public function adjustBalance(?int $userId = null): void
    {
        $request = new Request();
        $userId = $userId ?? (int)$request->input('user_id');
        $amount = (float)$request->input('amount');
        $action = (string)$request->input('action', (string)$request->input('action_type', 'add')); // add/credit or deduct/debit
        $reason = trim((string)$request->input('reason', 'Administrative Adjustment'));

        if ($amount <= 0 || $userId <= 0) {
            Session::setFlash('error', 'Valid user and adjustment amount greater than zero are required.');
            Response::redirect('/admin/users');
        }

        try {
            $effectiveAmount = ($action === 'deduct' || $action === 'debit') ? -$amount : $amount;
            WalletService::adminAdjust($userId, $effectiveAmount, $reason, (int)Auth::id());

            Security::logAudit("Admin adjusted balance for user #{$userId}", Auth::id(), "Amount: {$effectiveAmount}, Reason: {$reason}");
            Session::setFlash('success', 'Wallet balance successfully adjusted with immutable ledger record.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/users');
    }
}

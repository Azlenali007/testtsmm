<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;

class AdminDashboardController
{
    public function index(): void
    {
        $pdo = Database::pdo();

        // 1. Core KPIs
        $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(charge), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $totalProfits = (float)$pdo->query("SELECT COALESCE(SUM(profit), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $openTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open', 'customer_reply')")->fetchColumn();
        $pendingPayments = (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();

        // 2. Order Breakdown by status
        $orderBreakdownStmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        $orderBreakdown = $orderBreakdownStmt->fetchAll();

        // 3. Provider Balances
        $providers = $pdo->query("SELECT id, name, balance, currency, sync_status, is_active FROM providers ORDER BY id ASC")->fetchAll();

        // 4. Recent Orders
        $recentOrders = $pdo->query("
            SELECT o.*, u.username, s.name as service_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            LEFT JOIN services s ON s.id = o.service_id
            ORDER BY o.id DESC
            LIMIT 8
        ")->fetchAll();

        // 5. Monthly Revenue Data for Chart
        $revenueData = [
            'labels' => ['Day 1', 'Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Today'],
            'revenue' => [120, 190, 300, 500, 420, 680, (int)$totalRevenue],
            'orders' => [15, 28, 45, 60, 55, 80, (int)$totalOrders],
        ];

        View::render('admin/dashboard', [
            'title' => 'Admin Control Center',
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalProfits' => $totalProfits,
            'openTickets' => $openTickets,
            'pendingPayments' => $pendingPayments,
            'orderBreakdown' => $orderBreakdown,
            'providers' => $providers,
            'recentOrders' => $recentOrders,
            'revenueData' => $revenueData,
            'chartData' => $revenueData,
        ], 'admin');
    }
}

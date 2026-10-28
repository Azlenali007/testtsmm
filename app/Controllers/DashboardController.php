<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        // Fetch User KPI Stats
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status IN ('pending', 'processing', 'inprogress') THEN 1 ELSE 0 END) as active_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
            FROM orders 
            WHERE user_id = :uid
        ");
        $statsStmt->execute(['uid' => $user['id']]);
        $stats = $statsStmt->fetch();

        // Fetch Active Broadcast Announcements
        $broadcastStmt = $pdo->query("SELECT * FROM broadcasts WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY id DESC LIMIT 3");
        $broadcasts = $broadcastStmt->fetchAll();

        // Fetch Categories & Services for Quick Order Form
        $catStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
        $categories = $catStmt->fetchAll();

        $svcStmt = $pdo->query("SELECT id, category_id, name, price, min_quantity, max_quantity, description, type, dripfeed_enabled FROM services WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
        $services = $svcStmt->fetchAll();

        // Fetch Recent Orders (last 5)
        $recentStmt = $pdo->prepare("
            SELECT o.*, s.name as service_name, s.refill_enabled as service_refill_enabled 
            FROM orders o 
            LEFT JOIN services s ON s.id = o.service_id 
            WHERE o.user_id = :uid 
            ORDER BY o.id DESC 
            LIMIT 5
        ");
        $recentStmt->execute(['uid' => $user['id']]);
        $recentOrders = $recentStmt->fetchAll();

        View::render('user/dashboard', [
            'title' => 'Dashboard Overview',
            'user' => $user,
            'stats' => $stats,
            'broadcasts' => $broadcasts,
            'categories' => $categories,
            'services' => $services,
            'recentOrders' => $recentOrders,
        ], 'main');
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\OrderService;

class AdminOrderController
{
    public function index(): void
    {
        $request = new Request();
        $status = strtolower((string)$request->get('status', 'all'));
        $search = trim((string)$request->get('search', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();
        $query = "
            SELECT o.*, u.username, u.email as user_email, s.name as service_name, p.name as provider_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            LEFT JOIN services s ON s.id = o.service_id
            LEFT JOIN providers p ON p.id = o.provider_id
            WHERE 1=1
        ";
        $params = [];

        if ($status !== 'all' && in_array($status, ['pending', 'processing', 'inprogress', 'completed', 'partial', 'canceled', 'fail'])) {
            $query .= " AND o.status = :status";
            $params['status'] = $status;
        }

        if ($search !== '') {
            $query .= " AND (o.id = :s_id OR u.username LIKE :s_text OR o.link LIKE :s_text OR o.provider_order_id LIKE :s_text)";
            $params['s_id'] = is_numeric($search) ? (int)$search : 0;
            $params['s_text'] = '%' . $search . '%';
        }

        $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as count_tbl";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalOrders = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalOrders / $perPage);

        $query .= " ORDER BY o.id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Status counts for tabs
        $counts = [
            'all' => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
            'processing' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn(),
            'inprogress' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'inprogress'")->fetchColumn(),
            'completed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
            'partial' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'partial'")->fetchColumn(),
            'canceled' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'canceled'")->fetchColumn(),
            'fail' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'fail'")->fetchColumn(),
        ];

        View::render('admin/orders', [
            'title' => 'Order Management & Fulfillment',
            'orders' => $orders,
            'currentStatus' => $status,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrders,
            'statusCounts' => $counts,
        ], 'admin');
    }

    public function updateStatus(int $orderId): void
    {
        $request = new Request();
        $newStatus = strtolower((string)$request->input('status'));
        $startCount = (int)$request->input('start_count', 0);
        $remains = (int)$request->input('remains', 0);

        if ($newStatus === 'canceled') {
            OrderService::cancelAndRefund($orderId, 'Canceled by Administrator');
            Security::logAudit("Admin canceled and refunded order #{$orderId}");
            Session::setFlash('success', "Order #{$orderId} was canceled and refunded to client.");
            Response::redirect('/admin/orders');
        }

        if ($newStatus === 'partial') {
            OrderService::processPartial($orderId, $remains);
            Security::logAudit("Admin set order #{$orderId} to partial with {$remains} remains");
            Session::setFlash('success', "Order #{$orderId} marked partial and refunded proportionally.");
            Response::redirect('/admin/orders');
        }

        $stmt = Database::pdo()->prepare("
            UPDATE orders 
            SET status = :status, start_count = :start, remains = :remains, updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            'status' => in_array($newStatus, ['pending', 'processing', 'inprogress', 'completed', 'fail']) ? $newStatus : 'pending',
            'start' => $startCount,
            'remains' => $remains,
            'id' => $orderId,
        ]);

        Security::logAudit("Admin updated order #{$orderId} status to {$newStatus}");
        Session::setFlash('success', "Order #{$orderId} updated.");
        Response::redirect('/admin/orders');
    }

    public function resendToProvider(int $orderId): void
    {
        $dispatched = OrderService::dispatchToProvider($orderId);
        if ($dispatched) {
            Session::setFlash('success', "Order #{$orderId} was successfully sent to provider.");
        } else {
            Session::setFlash('error', "Failed to dispatch order #{$orderId}. Check error logs.");
        }
        Response::redirect('/admin/orders');
    }
}

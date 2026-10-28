<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\OrderService;

class OrderController
{
    public function index(): void
    {
        $user = Auth::user();
        $request = new Request();
        $status = strtolower((string)$request->get('status', 'all'));
        $search = trim((string)$request->get('search', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();

        $query = "
            SELECT o.*, s.name as service_name, s.refill_enabled as service_refill_enabled
            FROM orders o
            LEFT JOIN services s ON s.id = o.service_id
            WHERE o.user_id = :uid
        ";
        $params = ['uid' => $user['id']];

        if ($status !== 'all' && in_array($status, ['pending', 'processing', 'inprogress', 'completed', 'partial', 'canceled', 'fail'])) {
            $query .= " AND o.status = :status";
            $params['status'] = $status;
        }

        if ($search !== '') {
            $query .= " AND (o.id = :search_id OR o.link LIKE :search_link)";
            $params['search_id'] = is_numeric($search) ? (int)$search : 0;
            $params['search_link'] = '%' . $search . '%';
        }

        // Count total for pagination
        $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as count_table";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalOrders = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalOrders / $perPage);

        // Fetch paginated results
        $query .= " ORDER BY o.id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        View::render('user/orders', [
            'title' => 'My Orders',
            'orders' => $orders,
            'currentStatus' => $status,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrders,
        ], 'main');
    }

    public function newOrder(): void
    {
        $pdo = Database::pdo();

        $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC")->fetchAll();
        $services = $pdo->query("SELECT id, category_id, name, price, min_quantity, max_quantity, description, type, dripfeed_enabled, refill_enabled FROM services WHERE status = 'active' ORDER BY sort_order ASC, name ASC")->fetchAll();

        View::render('user/new_order', [
            'title' => 'Place New Order',
            'categories' => $categories,
            'services' => $services,
            'preselectedService' => (int)(new Request())->get('service_id', 0),
        ], 'main');
    }

    public function store(): void
    {
        $request = new Request();
        $user = Auth::user();

        $serviceId = (int)$request->input('service_id');
        $link = trim((string)$request->input('link'));
        $quantity = (int)$request->input('quantity', 0);
        $comments = $request->input('comments');
        $runs = (int)$request->input('runs', 1);
        $interval = (int)$request->input('interval', 0);

        try {
            $order = OrderService::createOrder(
                (int)$user['id'],
                $serviceId,
                $link,
                $quantity,
                $comments,
                $runs,
                $interval
            );

            if ($request->isAjax()) {
                Response::success('Order placed successfully!', [
                    'order' => $order,
                    'new_balance' => (float)Database::pdo()->query("SELECT balance FROM users WHERE id = {$user['id']}")->fetchColumn(),
                ]);
            }

            Session::setFlash('success', "Order #{$order['order_id']} placed successfully!");
            Response::redirect('/orders');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                Response::error($e->getMessage());
            }

            Session::setFlash('error', $e->getMessage());
            Response::redirect('/order/new');
        }
    }

    public function massOrder(): void
    {
        View::render('user/mass_order', ['title' => 'Mass Order Placement'], 'main');
    }

    public function storeMass(): void
    {
        $request = new Request();
        $user = Auth::user();
        $massOrders = trim((string)$request->input('mass_orders'));

        if (empty($massOrders)) {
            Session::setFlash('error', 'Please enter at least one order line.');
            Response::redirect('/order/mass');
        }

        $lines = explode("\n", str_replace("\r", "", $massOrders));
        $successCount = 0;
        $failedLines = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode('|', $line);
            if (count($parts) < 3) {
                $failedLines[] = "Line " . ($index + 1) . ": Invalid format. Must be service_id|quantity|link";
                continue;
            }

            $serviceId = (int)trim($parts[0]);
            $quantity = (int)trim($parts[1]);
            $link = trim($parts[2]);

            try {
                OrderService::createOrder((int)$user['id'], $serviceId, $link, $quantity);
                $successCount++;
            } catch (\Throwable $e) {
                $failedLines[] = "Line " . ($index + 1) . " (Service {$serviceId}): " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            Session::setFlash('success', "Successfully placed {$successCount} order(s).");
        }
        if (!empty($failedLines)) {
            Session::setFlash('error', implode("<br>", array_slice($failedLines, 0, 5)));
        }

        Response::redirect('/orders');
    }

    public function requestRefill(?int $orderId = null): void
    {
        $request = new Request();
        $user = Auth::user();
        $orderId = $orderId ?? (int)$request->input('order_id');

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT o.*, s.refill_enabled, s.refill_days 
            FROM orders o
            INNER JOIN services s ON s.id = o.service_id
            WHERE o.id = :id AND o.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['id' => $orderId, 'uid' => $user['id']]);
        $order = $stmt->fetch();

        if (!$order || empty($order['refill_enabled'])) {
            if ($request->isAjax()) {
                Response::error('This order is not eligible for refill.');
            }
            Session::setFlash('error', 'This order is not eligible for refill.');
            Response::redirect('/orders');
        }

        // Check if pending refill exists
        $refCheck = $pdo->prepare("SELECT id FROM refills WHERE order_id = :oid AND status IN ('pending', 'processing') LIMIT 1");
        $refCheck->execute(['oid' => $orderId]);
        if ($refCheck->fetch()) {
            if ($request->isAjax()) {
                Response::error('A refill request is already pending for this order.');
            }
            Session::setFlash('error', 'A refill request is already pending for this order.');
            Response::redirect('/orders');
        }

        $pdo->prepare("INSERT INTO refills (order_id, user_id, status, created_at) VALUES (:oid, :uid, 'pending', NOW())")
            ->execute(['oid' => $orderId, 'uid' => $user['id']]);

        if ($request->isAjax()) {
            Response::success('Refill request submitted successfully.');
        }

        Session::setFlash('success', "Refill requested for order #{$orderId}.");
        Response::redirect('/orders');
    }

    public function cancelOrder(?int $orderId = null): void
    {
        $request = new Request();
        $user = Auth::user();
        $orderId = $orderId ?? (int)$request->input('order_id');

        try {
            $stmt = Database::pdo()->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid LIMIT 1");
            $stmt->execute(['id' => $orderId, 'uid' => $user['id']]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new \RuntimeException('Order not found.');
            }

            if ($order['status'] !== 'pending') {
                throw new \RuntimeException('Only pending orders can be canceled directly.');
            }

            OrderService::cancelAndRefund($orderId, 'User requested cancellation');
            Session::setFlash('success', "Order #{$orderId} has been canceled and funds returned to your balance.");
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        Response::redirect('/orders');
    }
}

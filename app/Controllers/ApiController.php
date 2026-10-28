<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\OrderService;

class ApiController
{
    public function handle(): void
    {
        $request = new Request();
        $key = (string)$request->input('key');
        $action = strtolower((string)$request->input('action'));

        if (empty($key)) {
            Response::json(['error' => 'API key is required.'], 401);
        }

        // Authenticate client by API key
        $stmt = Database::pdo()->prepare("SELECT * FROM users WHERE apikey = :key AND status != 'suspended' LIMIT 1");
        $stmt->execute(['key' => $key]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::json(['error' => 'Invalid or inactive API key.'], 401);
        }

        switch ($action) {
            case 'services':
                $this->getServices();
                break;

            case 'add':
                $this->addOrder($user, $request);
                break;

            case 'status':
                $this->getStatus($user, $request);
                break;

            case 'refill':
                $this->requestRefill($user, $request);
                break;

            case 'refill_status':
                $this->getRefillStatus($user, $request);
                break;

            case 'balance':
                Response::json([
                    'balance' => number_format((float)$user['balance'], 2, '.', ''),
                    'currency' => 'USD',
                ]);
                break;

            default:
                Response::json(['error' => 'Invalid action parameter specified.'], 400);
        }
    }

    private function getServices(): void
    {
        $services = Database::pdo()->query("
            SELECT s.id as service, s.name, s.type, c.name as category, s.price as rate, s.min_quantity as min, s.max_quantity as max, s.refill_enabled as refill, s.dripfeed_enabled as dripfeed
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            WHERE s.status = 'active' AND c.status = 'active'
            ORDER BY c.sort_order ASC, s.sort_order ASC
        ")->fetchAll();

        $formatted = [];
        foreach ($services as $s) {
            $formatted[] = [
                'service' => (int)$s['service'],
                'name' => $s['name'],
                'type' => ucfirst(str_replace('_', ' ', $s['type'])),
                'category' => $s['category'],
                'rate' => number_format((float)$s['rate'], 4, '.', ''),
                'min' => (int)$s['min'],
                'max' => (int)$s['max'],
                'refill' => (bool)$s['refill'],
                'cancel' => false,
            ];
        }

        Response::json($formatted);
    }

    private function addOrder(array $user, Request $request): void
    {
        $serviceId = (int)$request->input('service');
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

            Response::json(['order' => (int)$order['order_id']]);
        } catch (\Throwable $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    private function getStatus(array $user, Request $request): void
    {
        $orderId = $request->input('order');
        $ordersParam = $request->input('orders');

        $pdo = Database::pdo();

        // Multiple orders check
        if (!empty($ordersParam)) {
            $ids = array_filter(array_map('intval', explode(',', (string)$ordersParam)));
            if (empty($ids)) {
                Response::json(['error' => 'No valid order IDs provided.'], 400);
            }

            $inClause = implode(',', $ids);
            $stmt = $pdo->prepare("SELECT id, charge, start_count, status, remains FROM orders WHERE id IN ({$inClause}) AND user_id = :uid");
            $stmt->execute(['uid' => $user['id']]);
            $rows = $stmt->fetchAll();

            $result = [];
            foreach ($rows as $row) {
                $result[(string)$row['id']] = [
                    'charge' => number_format((float)$row['charge'], 4, '.', ''),
                    'start_count' => (int)$row['start_count'],
                    'status' => ucfirst($row['status']),
                    'remains' => (int)$row['remains'],
                    'currency' => 'USD',
                ];
            }
            Response::json($result);
        }

        if (empty($orderId)) {
            Response::json(['error' => 'Order ID is required.'], 400);
        }

        $stmt = $pdo->prepare("SELECT id, charge, start_count, status, remains FROM orders WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => (int)$orderId, 'uid' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            Response::json(['error' => 'Order not found.'], 404);
        }

        Response::json([
            'charge' => number_format((float)$row['charge'], 4, '.', ''),
            'start_count' => (int)$row['start_count'],
            'status' => ucfirst($row['status']),
            'remains' => (int)$row['remains'],
            'currency' => 'USD',
        ]);
    }

    private function requestRefill(array $user, Request $request): void
    {
        $orderId = (int)$request->input('order');
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("
            SELECT o.*, s.refill_enabled 
            FROM orders o
            INNER JOIN services s ON s.id = o.service_id
            WHERE o.id = :id AND o.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['id' => $orderId, 'uid' => $user['id']]);
        $order = $stmt->fetch();

        if (!$order || empty($order['refill_enabled'])) {
            Response::json(['error' => 'Order not eligible for refill.'], 400);
        }

        $pdo->prepare("INSERT INTO refills (order_id, user_id, status, created_at) VALUES (:oid, :uid, 'pending', NOW())")
            ->execute(['oid' => $orderId, 'uid' => $user['id']]);

        Response::json(['refill' => (int)$pdo->lastInsertId()]);
    }

    private function getRefillStatus(array $user, Request $request): void
    {
        $refillId = (int)$request->input('refill');
        $stmt = Database::pdo()->prepare("SELECT status FROM refills WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute(['id' => $refillId, 'uid' => $user['id']]);
        $status = $stmt->fetchColumn();

        if (!$status) {
            Response::json(['error' => 'Refill request not found.'], 404);
        }

        Response::json(['status' => ucfirst((string)$status)]);
    }
}

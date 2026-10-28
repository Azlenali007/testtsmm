<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Security;
use PDO;
use RuntimeException;

class OrderService
{
    /**
     * Atomically creates an order, validates parameters, and deducts user balance
     */
    public static function createOrder(
        int $userId,
        int $serviceId,
        string $link,
        int $quantity,
        ?string $comments = null,
        int $runs = 1,
        int $interval = 0
    ): array {
        $pdo = Database::pdo();

        // 1. Fetch user
        $userStmt = $pdo->prepare("SELECT id, balance, custom_rate, status FROM users WHERE id = :id FOR UPDATE");
        $userStmt->execute(['id' => $userId]);
        $user = $userStmt->fetch();

        if (!$user || $user['status'] !== 'active') {
            throw new RuntimeException("Account is not active or authorized to place orders.");
        }

        // 2. Fetch service
        $svcStmt = $pdo->prepare("SELECT * FROM services WHERE id = :id AND status = 'active' LIMIT 1");
        $svcStmt->execute(['id' => $serviceId]);
        $service = $svcStmt->fetch();

        if (!$service) {
            throw new RuntimeException("The selected service is currently disabled or unavailable.");
        }

        // 3. Process custom comments quantity
        if (in_array($service['type'], ['custom_comments', 'package_comments'])) {
            if (empty($comments)) {
                throw new RuntimeException("Please enter comments (one per line).");
            }
            $lines = array_filter(array_map('trim', explode("\n", $comments)));
            $quantity = count($lines);
            $comments = implode("\n", $lines);
        }

        // 4. Validate quantity limits
        if ($quantity < (int)$service['min_quantity']) {
            throw new RuntimeException("Minimum order quantity is {$service['min_quantity']}.");
        }
        if ($quantity > (int)$service['max_quantity']) {
            throw new RuntimeException("Maximum order quantity is {$service['max_quantity']}.");
        }

        // 5. Handle Drip-feed calculations
        $isDripfeed = ($runs > 1 && $interval > 0 && !empty($service['dripfeed_enabled']));
        $totalQuantity = $isDripfeed ? ($quantity * $runs) : $quantity;

        if ($isDripfeed && $totalQuantity > (int)$service['max_quantity']) {
            throw new RuntimeException("Total drip-feed quantity ({$totalQuantity}) exceeds service maximum ({$service['max_quantity']}).");
        }

        // 6. Calculate charge
        $unitPrice = (float)$service['price'];
        if ($service['type'] === 'package') {
            $baseCharge = $unitPrice;
        } else {
            $baseCharge = ($unitPrice / 1000) * $totalQuantity;
        }

        // Apply custom user discount if applicable
        $customRate = (float)$user['custom_rate'];
        if ($customRate > 0 && $customRate < 100) {
            $discount = ($baseCharge * $customRate) / 100;
            $charge = round($baseCharge - $discount, 4);
        } else {
            $charge = round($baseCharge, 4);
        }

        // 7. Atomic transaction: Debit wallet & insert order
        $orderId = Database::transaction(function (PDO $pdo) use ($userId, $serviceId, $service, $link, $quantity, $charge, $comments, $runs, $interval, $totalQuantity) {
            // Debit wallet
            WalletService::debit(
                $userId,
                $charge,
                'order_charge',
                null,
                'order',
                "Order for {$service['name']} (Qty: {$totalQuantity})"
            );

            // Insert order record
            $insert = $pdo->prepare("
                INSERT INTO orders 
                (user_id, service_id, provider_id, link, quantity, charge, profit, status, comments, 
                 dripfeed_runs, dripfeed_interval, dripfeed_total_quantity, start_count, remains, created_at)
                VALUES 
                (:uid, :sid, :pid, :link, :qty, :charge, :profit, 'pending', :comments, 
                 :runs, :interval, :total_qty, 0, :remains, NOW())
            ");
            $insert->execute([
                'uid' => $userId,
                'sid' => $serviceId,
                'pid' => $service['provider_id'],
                'link' => Security::sanitize($link),
                'qty' => $quantity,
                'charge' => $charge,
                'profit' => $charge, // calculated against provider charge upon sync
                'comments' => $comments,
                'runs' => $runs,
                'interval' => $interval,
                'total_qty' => $totalQuantity,
                'remains' => $totalQuantity,
            ]);

            $oid = (int)$pdo->lastInsertId();

            // Link wallet transaction reference
            $pdo->prepare("UPDATE wallet_transactions SET reference_id = :oid WHERE user_id = :uid AND reference_id IS NULL ORDER BY id DESC LIMIT 1")
                ->execute(['oid' => $oid, 'uid' => $userId]);

            return $oid;
        });

        // 8. Attempt immediate provider dispatch if provider configured and not dripfeed
        if (!empty($service['provider_id']) && !empty($service['provider_service_id']) && !$isDripfeed) {
            self::dispatchToProvider($orderId);
        }

        return [
            'order_id' => $orderId,
            'service_name' => $service['name'],
            'quantity' => $totalQuantity,
            'charge' => $charge,
            'status' => 'pending',
        ];
    }

    /**
     * Forwards an internal order to the remote provider API
     */
    public static function dispatchToProvider(int $orderId): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT o.*, s.provider_service_id, s.type as service_type
            FROM orders o
            INNER JOIN services s ON s.id = o.service_id
            WHERE o.id = :id AND o.provider_id IS NOT NULL AND o.provider_order_id IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        try {
            $provider = ProviderService::getClient((int)$order['provider_id']);
            $params = [
                'service' => $order['provider_service_id'],
                'link' => $order['link'],
                'quantity' => $order['quantity'],
            ];

            if (!empty($order['comments'])) {
                $params['comments'] = $order['comments'];
            }

            $response = $provider->addOrder($params);

            if (isset($response['order'])) {
                $remoteId = (string)$response['order'];
                $update = $pdo->prepare("UPDATE orders SET provider_order_id = :p_oid, status = 'processing', updated_at = NOW() WHERE id = :id");
                $update->execute(['p_oid' => $remoteId, 'id' => $orderId]);
                return true;
            } elseif (isset($response['error'])) {
                $update = $pdo->prepare("UPDATE orders SET error_message = :err, updated_at = NOW() WHERE id = :id");
                $update->execute(['err' => substr((string)$response['error'], 0, 255), 'id' => $orderId]);
                return false;
            }
        } catch (\Throwable $e) {
            $update = $pdo->prepare("UPDATE orders SET error_message = :err, updated_at = NOW() WHERE id = :id");
            $update->execute(['err' => substr($e->getMessage(), 0, 255), 'id' => $orderId]);
        }

        return false;
    }

    /**
     * Atomically cancels an order and credits the full charge back to the user
     */
    public static function cancelAndRefund(int $orderId, string $reason = 'Order Canceled'): bool
    {
        return Database::transaction(function (PDO $pdo) use ($orderId, $reason) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException("Order #{$orderId} not found.");
            }

            if (in_array($order['status'], ['completed', 'canceled'])) {
                return false; // Already finalized
            }

            $refundAmount = (float)$order['charge'];

            // Update order status
            $up = $pdo->prepare("UPDATE orders SET status = 'canceled', remains = 0, error_message = :reason, updated_at = NOW() WHERE id = :id");
            $up->execute(['reason' => $reason, 'id' => $orderId]);

            // Credit balance atomically
            if ($refundAmount > 0) {
                WalletService::credit(
                    (int)$order['user_id'],
                    $refundAmount,
                    'order_refund',
                    $orderId,
                    'order',
                    "Refund for Canceled Order #{$orderId}"
                );
            }

            return true;
        });
    }

    /**
     * Marks order as partial and refunds the remaining unfilled proportion atomically
     */
    public static function processPartial(int $orderId, int $remains): bool
    {
        return Database::transaction(function (PDO $pdo) use ($orderId, $remains) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order || $order['status'] === 'canceled' || $order['status'] === 'partial') {
                return false;
            }

            $totalQty = (int)$order['quantity'];
            if ($remains <= 0 || $totalQty <= 0) {
                $pdo->prepare("UPDATE orders SET status = 'completed', remains = 0, updated_at = NOW() WHERE id = :id")
                    ->execute(['id' => $orderId]);
                return true;
            }

            $unfilledRatio = min(1.0, $remains / $totalQty);
            $refundAmount = round((float)$order['charge'] * $unfilledRatio, 4);

            $up = $pdo->prepare("UPDATE orders SET status = 'partial', remains = :remains, updated_at = NOW() WHERE id = :id");
            $up->execute(['remains' => $remains, 'id' => $orderId]);

            if ($refundAmount > 0) {
                WalletService::credit(
                    (int)$order['user_id'],
                    $refundAmount,
                    'order_refund',
                    $orderId,
                    'order',
                    "Partial Refund for Order #{$orderId} ({$remains}/{$totalQty} remains)"
                );
            }

            return true;
        });
    }
}

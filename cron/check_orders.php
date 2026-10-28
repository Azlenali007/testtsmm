<?php
declare(strict_types=1);

/**
 * Cron Job: Order Status Synchronization & Automated Refunds
 * Interval: Run every 1-5 minutes
 * Usage: php cron/check_orders.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\OrderService;
use App\Services\ProviderService;

// Prevent overlapping runs using file lock
$lockFile = __DIR__ . '/../storage/cron_orders.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo "[!] Another instance of check_orders is already running. Exiting.\n";
    exit(0);
}

$startTime = microtime(true);
$pdo = Database::pdo();
$processed = 0;
$updated = 0;

echo "[*] Starting order status synchronization: " . date('Y-m-d H:i:s') . "\n";

try {
    // Select active orders that have a provider ID and remote order ID
    $stmt = $pdo->query("
        SELECT id, provider_id, provider_order_id, status, quantity, charge 
        FROM orders 
        WHERE provider_id IS NOT NULL 
          AND provider_order_id IS NOT NULL 
          AND status IN ('pending', 'processing', 'inprogress')
        LIMIT 100
    ");
    $orders = $stmt->fetchAll();

    foreach ($orders as $order) {
        $processed++;
        $orderId = (int)$order['id'];
        $providerId = (int)$order['provider_id'];
        $remoteId = (string)$order['provider_order_id'];

        try {
            $provider = ProviderService::getClient($providerId);
            $statusRes = $provider->getOrderStatus($remoteId);

            if (isset($statusRes['status'])) {
                $rawStatus = strtolower(str_replace(' ', '', (string)$statusRes['status']));
                $remains = (int)($statusRes['remains'] ?? 0);
                $startCount = (int)($statusRes['start_count'] ?? 0);

                if (in_array($rawStatus, ['completed', 'complete'])) {
                    $pdo->prepare("UPDATE orders SET status = 'completed', remains = 0, start_count = :sc, updated_at = NOW() WHERE id = :id")
                        ->execute(['sc' => $startCount, 'id' => $orderId]);
                    $updated++;
                } elseif (in_array($rawStatus, ['canceled', 'cancel', 'refunded'])) {
                    OrderService::cancelAndRefund($orderId, 'Provider status: Canceled');
                    $updated++;
                } elseif ($rawStatus === 'partial') {
                    OrderService::processPartial($orderId, $remains);
                    $updated++;
                } elseif (in_array($rawStatus, ['inprogress', 'in_progress'])) {
                    $pdo->prepare("UPDATE orders SET status = 'inprogress', start_count = :sc, remains = :rem, updated_at = NOW() WHERE id = :id")
                        ->execute(['sc' => $startCount, 'rem' => $remains, 'id' => $orderId]);
                    $updated++;
                } elseif ($rawStatus === 'processing') {
                    $pdo->prepare("UPDATE orders SET status = 'processing', start_count = :sc, updated_at = NOW() WHERE id = :id")
                        ->execute(['sc' => $startCount, 'id' => $orderId]);
                    $updated++;
                }
            }
        } catch (\Throwable $e) {
            echo "[!] Error syncing Order #{$orderId}: " . $e->getMessage() . "\n";
        }
    }

    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $msg = "Checked {$processed} orders, updated {$updated}. Execution time: {$elapsedMs}ms";

    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('check_orders', 'success', :msg, :ms, NOW())")
        ->execute(['msg' => $msg, 'ms' => $elapsedMs]);

    echo "[+] {$msg}\n";
} catch (\Throwable $e) {
    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('check_orders', 'failed', :msg, :ms, NOW())")
        ->execute(['msg' => $e->getMessage(), 'ms' => $elapsedMs]);
    echo "[-] Fatal cron failure: " . $e->getMessage() . "\n";
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

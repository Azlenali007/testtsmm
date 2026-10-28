<?php
declare(strict_types=1);

/**
 * Cron Job: Drip-Feed Order Batch Dispatcher
 * Interval: Run every 5 minutes
 * Usage: php cron/dripfeed.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\OrderService;

$lockFile = __DIR__ . '/../storage/cron_dripfeed.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    exit(0);
}

$startTime = microtime(true);
$pdo = Database::pdo();
$dispatched = 0;

try {
    // Find active dripfeed orders that have remaining runs
    $stmt = $pdo->query("
        SELECT * FROM orders 
        WHERE dripfeed_runs > 1 
          AND dripfeed_delivery < dripfeed_runs 
          AND status IN ('pending', 'processing', 'inprogress')
    ");
    $dripOrders = $stmt->fetchAll();

    foreach ($dripOrders as $order) {
        $orderId = (int)$order['id'];
        $nextDelivery = (int)$order['dripfeed_delivery'] + 1;

        // Dispatch next batch
        $success = OrderService::dispatchToProvider($orderId);
        if ($success) {
            $pdo->prepare("UPDATE orders SET dripfeed_delivery = :del, updated_at = NOW() WHERE id = :id")
                ->execute(['del' => $nextDelivery, 'id' => $orderId]);
            $dispatched++;
        }
    }

    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $msg = "Drip-feed cron processed. Dispatched batches: {$dispatched}";

    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('dripfeed', 'success', :msg, :ms, NOW())")
        ->execute(['msg' => $msg, 'ms' => $elapsedMs]);

    echo "[+] {$msg}\n";
} catch (\Throwable $e) {
    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('dripfeed', 'failed', :msg, :ms, NOW())")
        ->execute(['msg' => $e->getMessage(), 'ms' => $elapsedMs]);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

<?php
declare(strict_types=1);

/**
 * Cron Job: Refill Status Checker
 * Interval: Run every 10-15 minutes
 * Usage: php cron/refill.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\ProviderService;

$lockFile = __DIR__ . '/../storage/cron_refill.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    exit(0);
}

$startTime = microtime(true);
$pdo = Database::pdo();
$checked = 0;

try {
    $stmt = $pdo->query("
        SELECT r.*, o.provider_id, o.provider_order_id 
        FROM refills r 
        INNER JOIN orders o ON o.id = r.order_id 
        WHERE r.status IN ('pending', 'processing')
    ");
    $refills = $stmt->fetchAll();

    foreach ($refills as $refill) {
        $checked++;
        $refillId = (int)$refill['id'];
        $providerId = (int)$refill['provider_id'];

        // If provider supports refill API, request it
        if ($providerId && !empty($refill['provider_order_id'])) {
            try {
                $provider = ProviderService::getClient($providerId);
                if (empty($refill['provider_refill_id'])) {
                    $res = $provider->requestRefill((string)$refill['provider_order_id']);
                    if (isset($res['refill'])) {
                        $pdo->prepare("UPDATE refills SET provider_refill_id = :prid, status = 'processing', updated_at = NOW() WHERE id = :id")
                            ->execute(['prid' => (string)$res['refill'], 'id' => $refillId]);
                    }
                } else {
                    $statusRes = $provider->getRefillStatus((string)$refill['provider_refill_id']);
                    if (isset($statusRes['status'])) {
                        $st = strtolower((string)$statusRes['status']);
                        if (in_array($st, ['completed', 'success', 'done'])) {
                            $pdo->prepare("UPDATE refills SET status = 'completed', updated_at = NOW() WHERE id = :id")->execute(['id' => $refillId]);
                        } elseif (in_array($st, ['rejected', 'fail', 'failed'])) {
                            $pdo->prepare("UPDATE refills SET status = 'rejected', updated_at = NOW() WHERE id = :id")->execute(['id' => $refillId]);
                        }
                    }
                }
            } catch (\Throwable) {
                // non-blocking
            }
        }
    }

    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $msg = "Refill cron executed. Checked: {$checked}";

    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('refill', 'success', :msg, :ms, NOW())")
        ->execute(['msg' => $msg, 'ms' => $elapsedMs]);

    echo "[+] {$msg}\n";
} catch (\Throwable $e) {
    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('refill', 'failed', :msg, :ms, NOW())")
        ->execute(['msg' => $e->getMessage(), 'ms' => $elapsedMs]);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

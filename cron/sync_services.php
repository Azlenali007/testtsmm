<?php
declare(strict_types=1);

/**
 * Cron Job: Provider Service Rate & Availability Synchronization
 * Interval: Run every 15-30 minutes
 * Usage: php cron/sync_services.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\ProviderService;

$lockFile = __DIR__ . '/../storage/cron_sync.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo "[!] Another instance of sync_services is running. Exiting.\n";
    exit(0);
}

$startTime = microtime(true);
$pdo = Database::pdo();

echo "[*] Starting provider service sync: " . date('Y-m-d H:i:s') . "\n";

try {
    $providers = $pdo->query("SELECT id, name FROM providers WHERE is_active = 1")->fetchAll();
    $totalUpdated = 0;

    foreach ($providers as $provider) {
        $pId = (int)$provider['id'];
        echo " -> Syncing provider: {$provider['name']} (#{$pId})...\n";
        $res = ProviderService::syncServices($pId, 25.0);
        if ($res['success']) {
            $totalUpdated += ($res['imported'] + $res['updated']);
        }
    }

    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $msg = "Synced " . count($providers) . " providers. Total services touched: {$totalUpdated}";

    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('sync_services', 'success', :msg, :ms, NOW())")
        ->execute(['msg' => $msg, 'ms' => $elapsedMs]);

    echo "[+] {$msg}\n";
} catch (\Throwable $e) {
    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('sync_services', 'failed', :msg, :ms, NOW())")
        ->execute(['msg' => $e->getMessage(), 'ms' => $elapsedMs]);
    echo "[-] Error: " . $e->getMessage() . "\n";
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

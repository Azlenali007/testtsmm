<?php
declare(strict_types=1);

/**
 * Cron Job: Daily Platform Metrics Compilation
 * Interval: Daily at midnight (0 0 * * *)
 * Usage: php cron/daily_reports.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$lockFile = __DIR__ . '/../storage/cron_reports.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    exit(0);
}

$startTime = microtime(true);
$pdo = Database::pdo();

try {
    $todayOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $todayRevenue = (float)$pdo->query("SELECT COALESCE(SUM(charge), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'canceled'")->fetchColumn();
    $todayDeposits = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn();

    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $msg = "Daily Report: Orders: {$todayOrders}, Revenue: \${$todayRevenue}, Deposits: \${$todayDeposits}";

    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('daily_reports', 'success', :msg, :ms, NOW())")
        ->execute(['msg' => $msg, 'ms' => $elapsedMs]);

    echo "[+] {$msg}\n";
} catch (\Throwable $e) {
    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
    $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time_ms, created_at) VALUES ('daily_reports', 'failed', :msg, :ms, NOW())")
        ->execute(['msg' => $e->getMessage(), 'ms' => $elapsedMs]);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

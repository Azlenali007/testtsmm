<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;

class AdminLogController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $auditLogs = $pdo->query("
            SELECT aal.*, u.username 
            FROM admin_audit_logs aal
            LEFT JOIN users u ON u.id = aal.user_id
            ORDER BY aal.id DESC
            LIMIT 50
        ")->fetchAll();

        $cronLogs = $pdo->query("SELECT * FROM cron_logs ORDER BY id DESC LIMIT 50")->fetchAll();

        View::render('admin/logs', [
            'title' => 'Audit & System Execution Logs',
            'auditLogs' => $auditLogs,
            'cronLogs' => $cronLogs,
        ], 'admin');
    }
}

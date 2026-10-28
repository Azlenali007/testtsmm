<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;

class AdminChildPanelController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $panels = $pdo->query("
            SELECT cp.*, u.username, u.email as user_email
            FROM child_panels cp
            INNER JOIN users u ON u.id = cp.user_id
            ORDER BY cp.id DESC
        ")->fetchAll();

        View::render('admin/child_panels', [
            'title' => 'Child Panel Subscriptions',
            'panels' => $panels,
        ], 'admin');
    }

    public function updateStatus(int $panelId): void
    {
        $status = (string)(new Request())->input('status');
        if (!in_array($status, ['pending', 'active', 'suspended', 'terminated'])) {
            Session::setFlash('error', 'Invalid status.');
            Response::redirect('/admin/child-panels');
        }

        Database::pdo()->prepare("UPDATE child_panels SET status = :status, updated_at = NOW() WHERE id = :id")
            ->execute(['status' => $status, 'id' => $panelId]);

        Security::logAudit("Admin updated child panel #{$panelId} to {$status}");
        Session::setFlash('success', "Child panel status updated to {$status}.");
        Response::redirect('/admin/child-panels');
    }
}

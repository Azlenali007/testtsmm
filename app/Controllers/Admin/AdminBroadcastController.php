<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;

class AdminBroadcastController
{
    public function index(): void
    {
        $broadcasts = Database::pdo()->query("SELECT * FROM broadcasts ORDER BY id DESC")->fetchAll();
        View::render('admin/broadcasts', [
            'title' => 'Broadcast Announcements & Alerts',
            'broadcasts' => $broadcasts,
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();
        $title = trim((string)$request->input('title'));
        $content = trim((string)$request->input('content'));
        $type = (string)$request->input('type', 'info');
        $expiry = trim((string)$request->input('expiry_date')) ?: null;

        if (empty($title) || empty($content)) {
            Session::setFlash('error', 'Title and message content are required.');
            Response::redirect('/admin/broadcasts');
        }

        $stmt = Database::pdo()->prepare("
            INSERT INTO broadcasts (title, content, type, is_active, display_pages, expiry_date, created_at)
            VALUES (:title, :content, :type, 1, 'all', :exp, NOW())
        ");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'type' => in_array($type, ['info', 'warning', 'success', 'announcement']) ? $type : 'info',
            'exp' => $expiry,
        ]);

        Security::logAudit("Admin posted announcement: {$title}");
        Session::setFlash('success', 'Announcement published successfully.');
        Response::redirect('/admin/broadcasts');
    }

    public function delete(int $id): void
    {
        Database::pdo()->prepare("DELETE FROM broadcasts WHERE id = :id")->execute(['id' => $id]);
        Security::logAudit("Admin deleted broadcast #{$id}");
        Session::setFlash('success', "Announcement deleted.");
        Response::redirect('/admin/broadcasts');
    }
}

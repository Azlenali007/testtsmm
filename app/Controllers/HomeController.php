<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Core\View;

class HomeController
{
    public function index(): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }

        $pdo = Database::pdo();
        $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC LIMIT 6")->fetchAll();
        $popularServices = $pdo->query("SELECT s.*, c.name as category_name FROM services s INNER JOIN categories c ON c.id = s.category_id WHERE s.status = 'active' ORDER BY s.sort_order ASC, s.id ASC LIMIT 8")->fetchAll();

        $stats = [
            'total_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() + 15200,
            'active_services' => (int)$pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn(),
            'total_users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() + 3400,
        ];

        View::render('home', [
            'title' => 'The #1 Social Media Marketing Provider',
            'categories' => $categories,
            'popularServices' => $popularServices,
            'stats' => $stats,
        ], 'main');
    }

    public function terms(): void
    {
        $content = Database::pdo()->query("SELECT setting_value FROM settings WHERE setting_key = 'terms_content'")->fetchColumn();
        View::render('user/terms', [
            'title' => 'Terms of Service',
            'content' => $content ?: '<h3>Terms of Service</h3><p>Standard terms apply.</p>',
        ], 'main');
    }

    public function privacy(): void
    {
        $content = Database::pdo()->query("SELECT setting_value FROM settings WHERE setting_key = 'privacy_content'")->fetchColumn();
        View::render('user/privacy', [
            'title' => 'Privacy Policy',
            'content' => $content ?: '<h3>Privacy Policy</h3><p>Standard privacy terms apply.</p>',
        ], 'main');
    }
}

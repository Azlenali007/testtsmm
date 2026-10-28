<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class ServiceController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC")->fetchAll();
        $services = $pdo->query("
            SELECT s.*, c.name as category_name 
            FROM services s 
            INNER JOIN categories c ON c.id = s.category_id 
            WHERE s.status = 'active' AND c.status = 'active' 
            ORDER BY c.sort_order ASC, s.sort_order ASC, s.name ASC
        ")->fetchAll();

        // Group services by category name
        $categorized = [];
        foreach ($services as $service) {
            $categorized[$service['category_name']][] = $service;
        }

        View::render('user/services', [
            'title' => 'Services & Pricing',
            'categories' => $categories,
            'categorizedServices' => $categorized,
        ], 'main');
    }

    public function detail(int $serviceId): void
    {
        $stmt = Database::pdo()->prepare("
            SELECT s.*, c.name as category_name 
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            WHERE s.id = :id AND s.status = 'active'
            LIMIT 1
        ");
        $stmt->execute(['id' => $serviceId]);
        $service = $stmt->fetch();

        if (!$service) {
            Response::error('Service not found or inactive', 404);
        }

        Response::success('Service details retrieved', [
            'service' => $service,
        ]);
    }
}

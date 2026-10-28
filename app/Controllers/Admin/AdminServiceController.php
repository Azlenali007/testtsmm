<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;

class AdminServiceController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();
        $services = $pdo->query("
            SELECT s.*, c.name as category_name, p.name as provider_name 
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            LEFT JOIN providers p ON p.id = s.provider_id
            ORDER BY c.sort_order ASC, s.sort_order ASC, s.name ASC
        ")->fetchAll();
        $providers = $pdo->query("SELECT id, name FROM providers WHERE is_active = 1")->fetchAll();

        View::render('admin/services', [
            'title' => 'Service Catalog & Categories',
            'categories' => $categories,
            'services' => $services,
            'providers' => $providers,
        ], 'admin');
    }

    public function storeService(): void
    {
        $request = new Request();
        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $providerId = $request->input('provider_id') ? (int)$request->input('provider_id') : null;
        $providerServiceId = trim((string)$request->input('provider_service_id')) ?: null;
        $price = (float)$request->input('price', 0.0);
        $min = (int)$request->input('min_quantity', 10);
        $max = (int)$request->input('max_quantity', 10000);
        $type = (string)$request->input('type', 'default');
        $description = trim((string)$request->input('description'));
        $dripfeed = $request->input('dripfeed_enabled') ? 1 : 0;
        $refill = $request->input('refill_enabled') ? 1 : 0;
        $refillDays = (int)$request->input('refill_days', 30);
        $status = (string)$request->input('status', 'active');

        if (empty($name) || $categoryId <= 0) {
            Session::setFlash('error', 'Service name and category are required.');
            Response::redirect('/admin/services');
        }

        $stmt = Database::pdo()->prepare("
            INSERT INTO services 
            (category_id, provider_id, provider_service_id, name, description, type, price, min_quantity, max_quantity, 
             dripfeed_enabled, refill_enabled, refill_days, status, created_at)
            VALUES 
            (:cid, :pid, :psid, :name, :desc, :type, :price, :min, :max, :drip, :refill, :rdays, :status, NOW())
        ");
        $stmt->execute([
            'cid' => $categoryId,
            'pid' => $providerId,
            'psid' => $providerServiceId,
            'name' => $name,
            'desc' => $description,
            'type' => $type,
            'price' => $price,
            'min' => $min,
            'max' => $max,
            'drip' => $dripfeed,
            'refill' => $refill,
            'rdays' => $refillDays,
            'status' => $status,
        ]);

        Security::logAudit("Admin created service: {$name}");
        Session::setFlash('success', "Service '{$name}' created successfully.");
        Response::redirect('/admin/services');
    }

    public function storeCategory(): void
    {
        $request = new Request();
        $name = trim((string)$request->input('name'));
        $icon = trim((string)$request->input('icon', 'folder'));
        $sort = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            Session::setFlash('error', 'Category name is required.');
            Response::redirect('/admin/services');
        }

        $stmt = Database::pdo()->prepare("INSERT INTO categories (name, icon, sort_order, status, created_at) VALUES (:name, :icon, :sort, 'active', NOW())");
        $stmt->execute(['name' => $name, 'icon' => $icon, 'sort' => $sort]);

        Security::logAudit("Admin created category: {$name}");
        Session::setFlash('success', "Category '{$name}' created successfully.");
        Response::redirect('/admin/services');
    }

    public function deleteService(int $serviceId): void
    {
        Database::pdo()->prepare("DELETE FROM services WHERE id = :id")->execute(['id' => $serviceId]);
        Security::logAudit("Admin deleted service #{$serviceId}");
        Session::setFlash('success', "Service #{$serviceId} deleted successfully.");
        Response::redirect('/admin/services');
    }

    public function deleteCategory(int $categoryId): void
    {
        Database::pdo()->prepare("DELETE FROM categories WHERE id = :id")->execute(['id' => $categoryId]);
        Security::logAudit("Admin deleted category #{$categoryId}");
        Session::setFlash('success', "Category #{$categoryId} deleted.");
        Response::redirect('/admin/services');
    }

    public function bulkMargin(): void
    {
        $request = new Request();
        $percentage = (float)$request->input('margin_percentage');

        if ($percentage <= -90 || $percentage > 500) {
            Session::setFlash('error', 'Invalid percentage margin adjustment.');
            Response::redirect('/admin/services');
        }

        $multiplier = 1 + ($percentage / 100);
        $stmt = Database::pdo()->prepare("UPDATE services SET price = ROUND(price * :mult, 4), updated_at = NOW() WHERE price > 0");
        $stmt->execute(['mult' => $multiplier]);

        Security::logAudit("Admin adjusted all service prices by {$percentage}%");
        Session::setFlash('success', "Successfully adjusted all service rates by {$percentage}%.");
        Response::redirect('/admin/services');
    }
}

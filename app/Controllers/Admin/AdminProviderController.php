<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\ProviderService;

class AdminProviderController
{
    public function index(): void
    {
        $providers = Database::pdo()->query("SELECT * FROM providers ORDER BY id ASC")->fetchAll();
        View::render('admin/providers', [
            'title' => 'SMM API Providers',
            'providers' => $providers,
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();
        $name = trim((string)$request->input('name'));
        $apiUrl = trim((string)$request->input('api_url'));
        $apiKey = trim((string)$request->input('api_key'));

        if (empty($name) || empty($apiUrl) || empty($apiKey)) {
            Session::setFlash('error', 'Provider name, API URL, and API key are required.');
            Response::redirect('/admin/providers');
        }

        $stmt = Database::pdo()->prepare("
            INSERT INTO providers (name, api_url, api_key, sync_status, is_active, created_at)
            VALUES (:name, :url, :key, 'Ready', 1, NOW())
        ");
        $stmt->execute([
            'name' => $name,
            'url' => $apiUrl,
            'key' => $apiKey,
        ]);

        Security::logAudit("Admin added SMM provider: {$name}");
        Session::setFlash('success', "Provider '{$name}' added successfully.");
        Response::redirect('/admin/providers');
    }

    public function sync(int $providerId): void
    {
        $request = new Request();
        $margin = max(0.0, (float)$request->input('margin_percentage', 25.0));

        try {
            $res = ProviderService::syncServices($providerId, $margin);
            Session::setFlash('success', $res['message']);
        } catch (\Throwable $e) {
            Session::setFlash('error', "Sync failed: " . $e->getMessage());
        }

        Response::redirect('/admin/providers');
    }

    public function testBalance(int $providerId): void
    {
        try {
            $res = ProviderService::checkBalance($providerId);
            if (isset($res['balance'])) {
                Session::setFlash('success', "Balance verified: {$res['balance']} " . ($res['currency'] ?? 'USD'));
            } else {
                Session::setFlash('error', "Provider returned: " . json_encode($res));
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/providers');
    }

    public function delete(int $providerId): void
    {
        Database::pdo()->prepare("DELETE FROM providers WHERE id = :id")->execute(['id' => $providerId]);
        Security::logAudit("Admin deleted provider #{$providerId}");
        Session::setFlash('success', "Provider #{$providerId} deleted.");
        Response::redirect('/admin/providers');
    }
}

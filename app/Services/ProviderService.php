<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Modules\Providers\StandardSmmProvider;
use PDO;
use RuntimeException;

class ProviderService
{
    public static function getClient(int $providerId): StandardSmmProvider
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM providers WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            throw new RuntimeException("API Provider #{$providerId} is not active or configured.");
        }

        return new StandardSmmProvider($provider['api_url'], $provider['api_key']);
    }

    public static function checkBalance(int $providerId): array
    {
        $client = self::getClient($providerId);
        $res = $client->getBalance();

        if (isset($res['balance'])) {
            $update = Database::pdo()->prepare("UPDATE providers SET balance = :bal, currency = :cur, sync_status = 'Connected', updated_at = NOW() WHERE id = :id");
            $update->execute([
                'bal' => (float)$res['balance'],
                'cur' => $res['currency'] ?? 'USD',
                'id' => $providerId,
            ]);
        }

        return $res;
    }

    /**
     * Synchronizes provider catalog into services table with an optional profit margin percentage
     */
    public static function syncServices(int $providerId, float $marginPercentage = 25.0): array
    {
        $client = self::getClient($providerId);
        $remoteServices = $client->getServices();

        if (isset($remoteServices['error'])) {
            return ['success' => false, 'message' => $remoteServices['error']];
        }

        $pdo = Database::pdo();
        $imported = 0;
        $updated = 0;

        // Ensure default category exists
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Imported Services' LIMIT 1");
        $catStmt->execute();
        $defaultCatId = $catStmt->fetchColumn();

        if (!$defaultCatId) {
            $newCat = $pdo->prepare("INSERT INTO categories (name, icon, sort_order, status) VALUES ('Imported Services', 'cloud', 99, 'active')");
            $newCat->execute();
            $defaultCatId = (int)$pdo->lastInsertId();
        }

        foreach ($remoteServices as $service) {
            if (!isset($service['service'], $service['name'], $service['rate'])) {
                continue;
            }

            $providerServiceId = (string)$service['service'];
            $name = (string)$service['name'];
            $baseRate = (float)$service['rate'];
            $calculatedPrice = round($baseRate * (1 + ($marginPercentage / 100)), 4);
            $min = (int)($service['min'] ?? 10);
            $max = (int)($service['max'] ?? 100000);
            $dripfeed = !empty($service['dripfeed']) ? 1 : 0;
            $refill = !empty($service['refill']) ? 1 : 0;

            // Check if service already exists
            $check = $pdo->prepare("SELECT id, price FROM services WHERE provider_id = :p_id AND provider_service_id = :ps_id LIMIT 1");
            $check->execute(['p_id' => $providerId, 'ps_id' => $providerServiceId]);
            $existing = $check->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("
                    UPDATE services 
                    SET min_quantity = :min, max_quantity = :max, dripfeed_enabled = :drip, refill_enabled = :refill, updated_at = NOW() 
                    WHERE id = :id
                ");
                $stmt->execute([
                    'min' => $min,
                    'max' => $max,
                    'drip' => $dripfeed,
                    'refill' => $refill,
                    'id' => $existing['id'],
                ]);
                $updated++;
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO services 
                    (category_id, provider_id, provider_service_id, name, description, type, price, min_quantity, max_quantity, dripfeed_enabled, refill_enabled, sort_order, status)
                    VALUES 
                    (:cat_id, :p_id, :ps_id, :name, :desc, 'default', :price, :min, :max, :drip, :refill, 0, 'active')
                ");
                $stmt->execute([
                    'cat_id' => $defaultCatId,
                    'p_id' => $providerId,
                    'ps_id' => $providerServiceId,
                    'name' => $name,
                    'desc' => "Automated service imported from provider #{$providerId}.",
                    'price' => $calculatedPrice,
                    'min' => $min,
                    'max' => $max,
                    'drip' => $dripfeed,
                    'refill' => $refill,
                ]);
                $imported++;
            }
        }

        $pdo->prepare("UPDATE providers SET sync_status = 'Synchronized (" . date('H:i') . ")', updated_at = NOW() WHERE id = :id")
            ->execute(['id' => $providerId]);

        return [
            'success' => true,
            'message' => "Synchronization finished: {$imported} new services imported, {$updated} updated.",
            'imported' => $imported,
            'updated' => $updated,
        ];
    }
}

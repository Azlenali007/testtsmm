<?php
declare(strict_types=1);

namespace Modules\Providers;

use RuntimeException;

class StandardSmmProvider implements ProviderInterface
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct(string $apiUrl, string $apiKey, int $timeout = 30)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    private function post(array $data): array
    {
        $data['key'] = $this->apiKey;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ApexSMM-Engine/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("Provider cURL Connection Error: " . $error);
        }

        $decoded = json_decode((string)$response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            // Some providers return plain error strings
            return [
                'error' => "HTTP {$httpCode}: Unexpected non-JSON response from provider",
                'raw_response' => substr((string)$response, 0, 500),
            ];
        }

        return $decoded;
    }

    public function getBalance(): array
    {
        return $this->post(['action' => 'balance']);
    }

    public function getServices(): array
    {
        return $this->post(['action' => 'services']);
    }

    public function addOrder(array $params): array
    {
        $postData = array_merge(['action' => 'add'], $params);
        return $this->post($postData);
    }

    public function getOrderStatus(string $orderId): array
    {
        return $this->post(['action' => 'status', 'order' => $orderId]);
    }

    public function getMultipleOrderStatuses(array $orderIds): array
    {
        return $this->post(['action' => 'status', 'orders' => implode(',', $orderIds)]);
    }

    public function requestRefill(string $orderId): array
    {
        return $this->post(['action' => 'refill', 'order' => $orderId]);
    }

    public function getRefillStatus(string $refillId): array
    {
        return $this->post(['action' => 'refill_status', 'refill' => $refillId]);
    }
}

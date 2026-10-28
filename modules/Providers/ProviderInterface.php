<?php
declare(strict_types=1);

namespace Modules\Providers;

interface ProviderInterface
{
    public function getBalance(): array;
    public function getServices(): array;
    public function addOrder(array $params): array;
    public function getOrderStatus(string $orderId): array;
    public function getMultipleOrderStatuses(array $orderIds): array;
    public function requestRefill(string $orderId): array;
    public function getRefillStatus(string $refillId): array;
}

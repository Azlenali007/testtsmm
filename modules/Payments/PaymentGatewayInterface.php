<?php
declare(strict_types=1);

namespace Modules\Payments;

interface PaymentGatewayInterface
{
    /**
     * Initializes payment and returns checkout parameters or redirect URL
     */
    public function initiatePayment(array $payment, array $config): array;

    /**
     * Verifies incoming webhook/IPN callback and returns verification status & details
     */
    public function handleWebhook(array $payload, array $config): array;
}

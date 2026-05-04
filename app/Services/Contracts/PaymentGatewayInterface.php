<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment for a specific amount.
     */
    public function process(float $amount, array $params): bool;
}

<?php

namespace App\Factories;

use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\StripePaymentGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * Create and return a payment gateway instance based on the provided driver.
     *
     * @throws InvalidArgumentException
     */
    public static function make(string $driver): PaymentGatewayInterface
    {
        return match ($driver) {
            'stripe' => new StripePaymentGateway(),
            // Future implementations:
            // 'paypal' => new PayPalPaymentGateway(),
            // 'square' => new SquarePaymentGateway(),
            default => throw new InvalidArgumentException("Driver [{$driver}] not supported."),
        };
    }
}

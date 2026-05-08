<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('payment.stripe.secret'));
    }

    /**
     * Process a payment through Stripe.
     *
     * @param float $amount The payment amount in cents
     * @param array $params Payment parameters including:
     *                      - token: Stripe token or source
     *                      - email: Customer email
     *                      - description: Payment description
     *
     * @return bool True if payment was successful
     *
     * @throws ApiErrorException
     */
    public function process(float $amount, array $params): bool
    {
        try {
            Charge::create([
                'amount' => (int) $amount,
                'currency' => 'usd',
                'source' => $params['token'] ?? 'tok_visa',
                'description' => $params['description'] ?? 'Order payment',
                'receipt_email' => $params['email'] ?? null,
                'metadata' => [
                    'order_id' => $params['order_id'] ?? null,
                ],
            ]);

            return true;
        } catch (ApiErrorException $e) {
            \Log::error('Stripe payment error: ' . $e->getMessage());

            return false;
        }
    }
}

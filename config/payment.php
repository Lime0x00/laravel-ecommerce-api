<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway driver used for
    | processing payments. Supported drivers: "stripe", "paypal"
    |
    */

    'default' => env('PAYMENT_GATEWAY_DRIVER', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    */

    'stripe' => [
        'key' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration (Placeholder for future implementation)
    |--------------------------------------------------------------------------
    */

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
];

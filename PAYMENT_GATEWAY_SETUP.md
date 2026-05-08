# Payment Gateway Integration Implementation Guide

## Overview
This implementation adds Stripe payment gateway integration to the Laravel e-commerce API using the Factory pattern. It includes order completion event listeners that send confirmation emails.

## Files Created

### 1. Configuration
- **config/payment.php** - Payment gateway configuration with Stripe and PayPal placeholders

### 2. Payment Gateway Implementation
- **app/Services/StripePaymentGateway.php** - Implements PaymentGatewayInterface for Stripe
- **app/Factories/PaymentGatewayFactory.php** - UPDATED: Factory to instantiate payment gateways by driver

### 3. Email & Events
- **app/Services/OrderConfirmationMail.php** - Mailable class for order confirmation emails
- **app/Providers/EventServiceProvider.php** - Registers OrderPlaced event with SendOrderConfirmation listener
- **app/Listeners/SendOrderConfirmation.php** - UPDATED: Handles email sending on order placement

### 4. Database Migration
- **database/migrations/2026_05_08_223000_add_payment_fields_to_orders_table.php** - Adds payment_id and payment_status columns to orders table

### 5. Views (Needs Manual Creation)
- **resources/views/emails/order-confirmation.blade.php** - HTML email template for order confirmations
  
  NOTE: Due to directory constraints, this file needs to be manually created. Create the directory structure:
  ```
  resources/views/emails/
  ```
  Then create `order-confirmation.blade.php` in that directory. The template is provided below.

## Files Updated

1. **app/Models/Order.php** - Added payment_id and payment_status to $fillable
2. **app/Services/OrderService.php** - Added payment processing and OrderPlaced event dispatch
3. **app/Http/Requests/CheckoutRequest.php** - Added payment_token and payment_email validation rules
4. **bootstrap/providers.php** - Registered EventServiceProvider
5. **.env.example** - Added Stripe and PayPal configuration keys

## Configuration Steps

### 1. Install Stripe SDK
Run the following command in your project directory:
```bash
composer require stripe/stripe-php
```

### 2. Update .env File
Add these environment variables to your `.env` file:
```env
PAYMENT_GATEWAY_DRIVER=stripe

# Stripe Configuration
STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY_HERE
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY_HERE
```

Replace `YOUR_PUBLIC_KEY_HERE` and `YOUR_SECRET_KEY_HERE` with your actual Stripe test keys from https://dashboard.stripe.com/test/apikeys

### 3. Run Migrations
Execute the new migration to add payment fields to the orders table:
```bash
php artisan migrate
```

### 4. Create Email View Directory and Template
Create the directory structure:
```bash
mkdir -p resources/views/emails
```

Create the file `resources/views/emails/order-confirmation.blade.php` with the following content:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; margin: 20px 0; }
        .order-details { background-color: #fff; padding: 15px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .order-items { margin: 20px 0; }
        .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; padding: 10px 0; }
        .footer { text-align: center; color: #999; font-size: 0.9em; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
        </div>

        <div class="content">
            <p>Dear {{ $customerName }},</p>
            <p>Thank you for your order! We're excited to get your items ready and shipped out to you.</p>

            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Order Date:</strong> {{ $orderDate }}</p>
                <p><strong>Status:</strong> <span style="color: #4CAF50; font-weight: bold;">{{ ucfirst($order->status) }}</span></p>
                <p><strong>Shipping Address:</strong> {{ $order->shipping_address }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            </div>

            <h3>Order Items</h3>
            <div class="order-items">
                @foreach ($order->items as $item)
                    <div class="item">
                        <span>{{ $item->product->name }} (x{{ $item->quantity }})</span>
                        <span>${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                    </div>
                @endforeach
                <div class="total">
                    Total: ${{ $totalAmount }}
                </div>
            </div>

            <p>We will send you a shipping confirmation once your order is dispatched. You can track your order using the order ID above.</p>

            <p>If you have any questions, please don't hesitate to contact our customer support team.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} E-Commerce Platform. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
```

## How It Works

### Checkout Flow
1. User sends checkout request with:
   - `shipping_address` (required)
   - `payment_method` (required: 'stripe', 'paypal', etc.)
   - `payment_token` (optional: Stripe token from frontend)
   - `payment_email` (optional: for receipts)

2. OrderService.checkout() method:
   - Validates cart is not empty
   - Retrieves PaymentGateway using Factory based on payment_method
   - Processes payment through the gateway
   - Creates order if payment succeeds
   - Dispatches OrderPlaced event
   - Clears cart

3. OrderPlaced Event triggers SendOrderConfirmation Listener:
   - Listener sends OrderConfirmationMail to user's email
   - Email includes order details, items, and total amount

### Factory Pattern Usage
```php
// In OrderService or any payment-processing context
$paymentGateway = PaymentGatewayFactory::make('stripe');
$success = $paymentGateway->process($amount, $params);
```

This allows easy switching between payment gateways without changing business logic:
```php
// Future: Switch to PayPal
PaymentGatewayFactory::make('paypal');

// Future: Switch to Square
PaymentGatewayFactory::make('square');
```

## Queue Setup for Emails
Emails are sent via the queue system (ShouldQueue interface). Ensure your queue worker is running:

```bash
php artisan queue:listen
```

Or in production, use a proper queue driver like Redis.

## Testing the Implementation

### Local Testing with Stripe Test Keys
Use Stripe's test card numbers:
- Success: `4242 4242 4242 4242`
- Failure: `4000 0000 0000 0002`

### Test Email Sending
If using `log` mailer (default in .env.example):
```bash
tail -f storage/logs/laravel.log
```

You'll see the email content in the logs instead of actually sending.

## Future Enhancements

1. **PayPal Integration** - Implement PayPalPaymentGateway class
2. **Square Integration** - Implement SquarePaymentGateway class
3. **Webhook Handling** - Add webhooks for payment status updates
4. **Payment History** - Track all payment transactions
5. **Refund Processing** - Add refund capabilities
6. **Invoice Generation** - Generate PDF invoices on order completion
7. **SMS Notifications** - Send SMS alerts on order placement
8. **Order Status Emails** - Send updates when order status changes

## Troubleshooting

### Payment Processing Fails
- Check Stripe API keys are correctly set in .env
- Verify payment_token is valid if using Stripe.js frontend integration
- Check Laravel logs: `tail -f storage/logs/laravel.log`

### Emails Not Sending
- Verify MAIL_MAILER is set appropriately (.env)
- If using 'log', check logs for email content
- Ensure queue worker is running: `php artisan queue:listen`
- Check Mail configuration in config/mail.php

### Database Errors
- Run migrations: `php artisan migrate`
- Check database connection in config/database.php

## References
- Stripe PHP SDK: https://github.com/stripe/stripe-php
- Laravel Mailable: https://laravel.com/docs/mail#generating-mailables
- Laravel Events: https://laravel.com/docs/events
- Factory Pattern: https://refactoring.guru/design-patterns/factory-method

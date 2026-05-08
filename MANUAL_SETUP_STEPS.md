# Manual Steps to Complete Implementation

After the automated code has been added, follow these steps to complete the payment gateway integration:

## Step 1: Create Directories (Windows PowerShell)

Run these commands in PowerShell:

```powershell
$basePath = "e:\backend .net\project\laravel-ecommerce-api.worktrees\copilot-add-payment-gateway-factory-integration"

# Create app/Mail directory
New-Item -ItemType Directory -Path "$basePath\app\Mail" -Force | Out-Null

# Create resources/views/emails directory  
New-Item -ItemType Directory -Path "$basePath\resources\views\emails" -Force | Out-Null

Write-Host "Directories created successfully!"
```

## Step 2: Create Email View File

Create the file: `resources/views/emails/order-confirmation.blade.php`

**Content**:
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

## Step 3: Install Composer Dependencies

Run in the project root directory:

```bash
composer install
```

This will install:
- `stripe/stripe-php` - Stripe SDK
- All existing Laravel dependencies

## Step 4: Run Database Migration

```bash
php artisan migrate
```

This creates the following changes to the `orders` table:
- Adds `payment_id` column (nullable string)
- Adds `payment_status` column (string, default: 'pending')

## Step 5: Update .env File

Copy `.env.example` to `.env` if not already done, then add/update:

```env
# Payment Gateway Configuration
PAYMENT_GATEWAY_DRIVER=stripe

# Stripe Test Keys (from https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY_HERE
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY_HERE

# Optional: PayPal Configuration
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox
```

**Get Stripe Test Keys:**
1. Go to https://dashboard.stripe.com/test/apikeys
2. Copy your Restricted API key (starts with `sk_test_`)
3. Copy your Publishable key (starts with `pk_test_`)
4. Paste into .env

## Step 6: Clear Laravel Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 7: Start the Application

```bash
# In one terminal - Start Laravel Server
php artisan serve

# In another terminal - Start Queue Worker
php artisan queue:listen

# Optional: In another terminal - Watch logs
php artisan pail
```

## Step 8: Verify Installation

### Check Database Migration
```bash
php artisan tinker
>>> DB::table('orders')->getColumns()
```

You should see `payment_id` and `payment_status` columns.

### Check Service Providers
```bash
php artisan tinker
>>> app('events')
>>> app(\App\Services\StripePaymentGateway::class)
```

Should not throw errors.

### Check Factory
```bash
php artisan tinker
>>> \App\Factories\PaymentGatewayFactory::make('stripe')
# Should return StripePaymentGateway instance
```

## Step 9: Test Payment Processing

### Using Postman or cURL

1. **Create a Cart and Add Items** (existing endpoint)
```bash
POST /api/cart/add
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "product_id": 1,
  "quantity": 2
}
```

2. **Checkout with Payment**
```bash
POST /api/orders/checkout
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "shipping_address": "123 Main St, City, State 12345",
  "payment_method": "stripe",
  "payment_token": "tok_visa",
  "payment_email": "customer@example.com"
}
```

**Expected Response (Success)**:
```json
{
  "success": true,
  "message": "Checkout completed successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "total_price": 50.00,
    "payment_method": "stripe",
    "payment_id": "ch_1234567890abcdef",
    "payment_status": "completed",
    "created_at": "2026-05-08T22:38:52.000000Z"
  }
}
```

### Check Email Was Queued
```bash
php artisan tinker
>>> DB::table('jobs')->select('payload')->latest()->first()
# Should show a queued job for sending email
```

## Step 10: Test Stripe Payment Cards

Use these test card numbers in your Stripe.js implementation:

**Success Cards**:
- `4242 4242 4242 4242` - Visa
- `5555 5555 5555 4444` - Mastercard
- `3782 822463 10005` - American Express

**Failure Cards**:
- `4000 0000 0000 0002` - Declined
- `4000 0000 0000 9995` - Insufficient funds
- `4000 0002 5000 3155` - Lost card

**Special Cases**:
- Use any future date for expiry
- Use any 3-digit number for CVC

## Step 11: Monitor Logs

Watch for emails and payment processing:

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Check specific payment errors
php artisan logs | grep -i stripe
```

## Step 12: Verify Email Sending

### If using 'log' mailer (default)
Check `storage/logs/laravel.log`:
```
[2026-05-08 22:38:52] local.DEBUG: Message sent from hello@example.com
```

### If using SMTP/Sendmail
Check your email provider's logs or test inbox.

## Troubleshooting

### Issue: "Class not found" errors
```bash
# Clear and regenerate autoload
composer dump-autoload -o
php artisan config:cache
```

### Issue: "table orders has no column payment_id"
```bash
# Run migrations
php artisan migrate
```

### Issue: "Stripe API key not set"
- Verify STRIPE_SECRET_KEY in .env
- Run: `php artisan config:cache`
- Check: `php artisan config:get payment.stripe.secret`

### Issue: Emails not sending
1. Ensure queue worker is running: `php artisan queue:listen`
2. Check job queue: `DB::table('jobs')->count()`
3. Check logs: `storage/logs/laravel.log`

### Issue: "Factory driver not supported"
- Verify `payment_method` in request is 'stripe'
- Check PaymentGatewayFactory::make() supports the driver

## Performance Optimization (Production)

For production environments:

### 1. Use Redis for Queue
```env
QUEUE_CONNECTION=redis
```

### 2. Use Supervisor for Queue Worker
```bash
sudo apt-get install supervisor
```

Create `/etc/supervisor/conf.d/laravel-queue.conf`:
```
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue.log
```

### 3. Configure Stripe Webhooks
- Set up webhook endpoint at `/api/webhooks/stripe`
- Handle payment confirmation events
- Update order status based on webhook

## Next Steps

1. **Implement Stripe.js** on frontend for client-side tokenization
2. **Add Refund Processing** endpoint
3. **Implement Webhook** handling for async payment updates
4. **Add Payment History** to user dashboard
5. **Generate PDF Invoices** on order completion

---

**Estimated Time to Complete**: 30 minutes
**Prerequisites**: PHP 8.3+, Laravel 13+, MySQL/PostgreSQL, Stripe Account (free)

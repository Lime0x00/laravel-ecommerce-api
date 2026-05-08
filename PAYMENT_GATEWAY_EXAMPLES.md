# Payment Gateway Integration - Quick Reference

## Architecture Overview

```
User Checkout Request
    ↓
CheckoutRequest Validation
    ↓
OrderService::checkout()
    ├─ Validate cart
    ├─ Calculate total
    ├─ PaymentGatewayFactory::make() → StripePaymentGateway
    ├─ StripePaymentGateway::process()
    ├─ Create Order
    ├─ OrderPlaced Event Dispatch
    └─ Clear Cart
         ↓
    EventServiceProvider Routes Event to Listener
         ↓
    SendOrderConfirmation::handle()
         ↓
    OrderConfirmationMail::send()
         ↓
    Email Queued/Sent
```

## Code Examples

### 1. Checkout Request Structure

```json
{
  "shipping_address": "123 Main St, City, State 12345",
  "payment_method": "stripe",
  "payment_token": "tok_visa",
  "payment_email": "customer@example.com"
}
```

### 2. OrderService Checkout Flow

```php
// OrderService::checkout()
public function checkout(int $userId, array $payload): Order
{
    // 1. Validate cart
    $cart = $this->cartRepository->getCartWithItems($userId, null);
    
    // 2. Calculate total
    $totalPrice = $cart->items->sum(function (CartItem $item) {
        return $item->unit_price * $item->quantity;
    });
    
    // 3. Get payment gateway using Factory
    $paymentMethod = $payload['payment_method'] ?? 'stripe';
    $paymentGateway = PaymentGatewayFactory::make($paymentMethod);
    
    // 4. Process payment
    $paymentSuccessful = $paymentGateway->process(
        amount: $totalPrice * 100, // Convert to cents
        params: [
            'token' => $payload['payment_token'],
            'email' => $payload['payment_email'],
            'description' => "Order payment for user {$userId}",
        ]
    );
    
    if (!$paymentSuccessful) {
        throw ValidationException::withMessages([
            'payment' => ['Payment processing failed.'],
        ]);
    }
    
    // 5. Create order
    $order = $this->orderRepository->createFromCart($userId, $cart, [
        'shipping_address' => $payload['shipping_address'],
        'payment_method' => $paymentMethod,
        'total_price' => $totalPrice,
        'payment_status' => 'completed',
    ]);
    
    // 6. Dispatch event
    OrderPlaced::dispatch($order);
    
    // 7. Clear cart
    $this->cartRepository->clearCart($cart);
    
    return $order;
}
```

### 3. Payment Gateway Factory

```php
// PaymentGatewayFactory::make()
public static function make(string $driver): PaymentGatewayInterface
{
    return match ($driver) {
        'stripe' => new StripePaymentGateway(),
        'paypal' => new PayPalPaymentGateway(), // Future
        'square' => new SquarePaymentGateway(),  // Future
        default => throw new InvalidArgumentException(
            "Driver [{$driver}] not supported."
        ),
    };
}
```

### 4. Stripe Payment Gateway Implementation

```php
// StripePaymentGateway::process()
public function process(float $amount, array $params): bool
{
    try {
        Stripe::setApiKey(config('payment.stripe.secret'));
        
        Charge::create([
            'amount' => (int) $amount,           // Amount in cents
            'currency' => 'usd',
            'source' => $params['token'],        // Stripe token
            'description' => $params['description'],
            'receipt_email' => $params['email'],
            'metadata' => [
                'order_id' => $params['order_id'] ?? null,
            ],
        ]);
        
        return true;
    } catch (ApiErrorException $e) {
        \Log::error('Stripe error: ' . $e->getMessage());
        return false;
    }
}
```

### 5. Event Dispatch

```php
// In OrderService after creating order
OrderPlaced::dispatch($order);

// Event class (app/Events/OrderPlaced.php)
class OrderPlaced
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Order $order) {}
}
```

### 6. Event Listener Registration

```php
// EventServiceProvider
protected $listen = [
    OrderPlaced::class => [
        SendOrderConfirmation::class,
    ],
];
```

### 7. Email Sending Listener

```php
// SendOrderConfirmation::handle()
public function handle(OrderPlaced $event): void
{
    Mail::send(new OrderConfirmationMail($event->order));
}
```

### 8. Mailable Class

```php
// OrderConfirmationMail
public function envelope(): Envelope
{
    return new Envelope(
        from: new Address(config('mail.from.address')),
        subject: "Order Confirmation - Order #{$this->order->id}",
    );
}

public function content(): Content
{
    return new Content(
        view: 'emails.order-confirmation',
        with: [
            'order' => $this->order,
            'customerName' => $this->order->user->name,
            'orderDate' => $this->order->created_at->format('M d, Y'),
            'totalAmount' => number_format($this->order->total_price, 2),
        ],
    );
}
```

## How to Add New Payment Gateways

### Step 1: Create Payment Gateway Class
```php
// app/Services/YourPaymentGateway.php
namespace App\Services;

class YourPaymentGateway implements PaymentGatewayInterface
{
    public function process(float $amount, array $params): bool
    {
        // Implementation here
    }
}
```

### Step 2: Update Factory
```php
// PaymentGatewayFactory::make()
return match ($driver) {
    'stripe' => new StripePaymentGateway(),
    'your_gateway' => new YourPaymentGateway(),  // Add this line
    default => throw new InvalidArgumentException(...),
};
```

### Step 3: Update Configuration
```php
// config/payment.php
'your_gateway' => [
    'key' => env('YOUR_GATEWAY_KEY'),
    'secret' => env('YOUR_GATEWAY_SECRET'),
],
```

### Step 4: Update Validation
```php
// CheckoutRequest::rules()
'payment_method' => ['required', 'string', 'in:stripe,paypal,your_gateway'],
```

## Environment Variables

```env
# Payment Gateway Configuration
PAYMENT_GATEWAY_DRIVER=stripe

# Stripe
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# PayPal (Future)
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox

# Mail Configuration
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="E-Commerce"
```

## Database Schema Changes

```sql
-- Added columns to orders table
ALTER TABLE orders ADD payment_id VARCHAR(255) NULLABLE;
ALTER TABLE orders ADD payment_status VARCHAR(255) DEFAULT 'pending';
```

## Testing Checklist

- [ ] Stripe SDK installed (`composer require stripe/stripe-php`)
- [ ] Environment variables configured (.env has STRIPE keys)
- [ ] Database migrated (`php artisan migrate`)
- [ ] Email view created (resources/views/emails/order-confirmation.blade.php)
- [ ] Event listeners registered (EventServiceProvider in bootstrap/providers.php)
- [ ] Queue worker running (`php artisan queue:listen`)
- [ ] Checkout request validated with payment fields
- [ ] Order model updated with payment fields
- [ ] Tested with Stripe test card: 4242 4242 4242 4242
- [ ] Confirmed confirmation email sent to user

## API Response Example

### Successful Checkout
```json
{
  "success": true,
  "message": "Checkout completed successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "total_price": 99.99,
    "shipping_address": "123 Main St, City, State 12345",
    "payment_method": "stripe",
    "payment_id": "ch_1234567890",
    "payment_status": "completed",
    "created_at": "2026-05-08T22:38:52.000000Z",
    "updated_at": "2026-05-08T22:38:52.000000Z"
  }
}
```

### Failed Payment
```json
{
  "status": "error",
  "message": "Validation failed.",
  "data": {
    "payment": ["Payment processing failed. Please try again."]
  }
}
```

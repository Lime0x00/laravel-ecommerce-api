# Payment Gateway Integration - Architecture & Flow Diagrams

## 1. Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         LARAVEL E-COMMERCE API                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                        HTTP CLIENT (Frontend)                        │  │
│  │                   POST /api/orders/checkout                         │  │
│  └─────────────────────────────┬──────────────────────────────────────┘  │
│                                │                                         │
│                                │ Request Validation                      │
│                                ▼                                         │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │              CheckoutRequest (Validation Layer)                      │  │
│  │  • shipping_address ✓                                               │  │
│  │  • payment_method (stripe|paypal|cash) ✓                           │  │
│  │  • payment_token (optional) ✓                                       │  │
│  │  • payment_email (optional) ✓                                       │  │
│  └─────────────────────────────┬──────────────────────────────────────┘  │
│                                │                                         │
│                                ▼                                         │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │             OrderController::checkout()                              │  │
│  │                                                                       │  │
│  │  Call OrderService::checkout($userId, $payload)                     │  │
│  └─────────────────────────────┬──────────────────────────────────────┘  │
│                                │                                         │
│                                ▼                                         │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                   OrderService::checkout()                           │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ 1. Validate Cart                                               │  │  │
│  │  │    - Check cart exists                                         │  │  │
│  │  │    - Check cart has items                                      │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ 2. Calculate Total Price                                       │  │  │
│  │  │    - Sum: unit_price × quantity for each item                  │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ 3. Get Payment Gateway from Factory                            │  │  │
│  │  │    PaymentGatewayFactory::make($paymentMethod)                │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  │  ┌────────────────────────────────────────────────────────────────┐  │  │
│  │  │ 4. Process Payment                                             │  │  │
│  │  │    $paymentGateway->process($amount, $params)                 │  │  │
│  │  │    Returns: true (success) or false (failed)                  │  │  │
│  │  └────────────────────────────────────────────────────────────────┘  │  │
│  │             │                                                        │  │
│  │             ├─ SUCCESS ──────────────────────────────┐              │  │
│  │             │                                        │              │  │
│  │             │              FAILED                    │              │  │
│  │             ▼                │                       ▼              │  │
│  │      Throw Exception         │                  Create Order       │  │
│  │             │                │                       │              │  │
│  │             │                │                  OrderRepository    │  │
│  │             │                │                  .createFromCart()  │  │
│  │             │                ▼                       │              │  │
│  │             │           Return 422                   ▼              │  │
│  │             │           Validation                Dispatch Event   │  │
│  │             │           Error                  OrderPlaced::       │  │
│  │             │                                  dispatch($order)    │  │
│  │             │                                       │              │  │
│  │             │                                  Clear Cart          │  │
│  │             │                                       │              │  │
│  │             │                                       ▼              │  │
│  │             │                               Return Order + 201    │  │
│  │             └───────────────────────────────┬─────────────────────┘  │
│  │                                             │                      │  │
│  └─────────────────────────────────────────────┼──────────────────────┘  │
│                                                │                         │
│                                ┌───────────────┴─────────────┐            │
│                                │                             │            │
│                  ERROR: Return 422                  SUCCESS: Dispatch    │
│                                │                    OrderPlaced Event    │
│                                ▼                             │            │
│                          ┌────────────┐            ┌────────▼─────────┐  │
│                          │   Client   │            │  EventService    │  │
│                          │ Gets Error │            │  Listener Setup  │  │
│                          └────────────┘            └────────┬─────────┘  │
│                                                              │            │
│                                      ┌───────────────────────┴──────┐    │
│                                      │                              │    │
│                    ┌─────────────────▼──────────────────┐  ┌──────▼─┐  │
│                    │  SendOrderConfirmation Listener    │  │ Others │  │
│                    │  (Observes OrderPlaced Event)      │  └────────┘  │
│                    │                                    │              │
│                    │  handle(OrderPlaced $event) {      │              │
│                    │    Mail::send(                     │              │
│                    │      new OrderConfirmationMail()   │              │
│                    │    )                               │              │
│                    │  }                                 │              │
│                    └─────────────────┬──────────────────┘              │
│                                      │                               │
│                                      ▼                               │
│                    ┌──────────────────────────────────┐               │
│                    │  Mailable: OrderConfirmationMail │               │
│                    │                                  │               │
│                    │  To: customer@example.com        │               │
│                    │  Subject: Order Confirmation     │               │
│                    │  Template: order-confirmation    │               │
│                    │           .blade.php             │               │
│                    └─────────────────┬────────────────┘               │
│                                      │                               │
│                    ┌─────────────────▼────────────────┐               │
│                    │  Queue System (ShouldQueue)      │               │
│                    │  - Redis                         │               │
│                    │  - Database                      │               │
│                    │  - Sync                          │               │
│                    └─────────────────┬────────────────┘               │
│                                      │                               │
│                    ┌─────────────────▼────────────────┐               │
│                    │  Queue Worker                    │               │
│                    │  php artisan queue:listen        │               │
│                    └─────────────────┬────────────────┘               │
│                                      │                               │
│                    ┌─────────────────▼────────────────┐               │
│                    │  Mail Driver                     │               │
│                    │  - SMTP (Production)            │               │
│                    │  - Log (Development)            │               │
│                    │  - Mailgun                      │               │
│                    └─────────────────┬────────────────┘               │
│                                      │                               │
│                    ┌─────────────────▼────────────────┐               │
│                    │  Customer Email Inbox           │               │
│                    │  ✉️ Order Confirmation          │               │
│                    │  Order #1234567890              │               │
│                    │  Total: $99.99                  │               │
│                    │  Status: Processing             │               │
│                    └──────────────────────────────────┘               │
│                                                                       │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Factory Pattern Flow

```
┌────────────────────────────────────────────────────────────┐
│  PaymentGatewayFactory::make(string $driver)               │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Input: $driver = "stripe"                               │
│                                                            │
│  ┌─────────────────────────────────────────────────┐     │
│  │ match ($driver) {                               │     │
│  │     'stripe' => new StripePaymentGateway(),    │     │
│  │     'paypal' => new PayPalPaymentGateway(),    │     │
│  │     'square' => new SquarePaymentGateway(),    │     │
│  │     default => throw InvalidArgumentException │     │
│  │ }                                               │     │
│  └─────────────────────────────────────────────────┘     │
│                                                            │
│  Output: implements PaymentGatewayInterface               │
│                                                            │
│  ┌────────┬────────┬────────┬──────────────────┐         │
│  ▼        ▼        ▼        ▼                  ▼         │
│ Stripe  PayPal   Square  Cryptocurrency  Bitcoin        │
│   │       │        │          │            │            │
│   └───────┴────────┴──────────┴────────────┘            │
│                    │                                     │
│                    ▼                                     │
│        PaymentGatewayInterface                          │
│        process(float, array): bool                       │
│                                                          │
└────────────────────────────────────────────────────────────┘
```

---

## 3. Payment Processing Flow

```
START
  │
  ├─ Receive Checkout Request
  │  {
  │    "shipping_address": "...",
  │    "payment_method": "stripe",
  │    "payment_token": "tok_...",
  │    "payment_email": "..."
  │  }
  │
  ├─ Validate Request
  │  ├─ Is user authenticated? ✓
  │  ├─ Is cart not empty? ✓
  │  ├─ Is payment_method valid? ✓
  │  └─ Return 422 if validation fails
  │
  ├─ Calculate Order Total
  │  Total = Σ(unit_price × quantity)
  │
  ├─ Get Payment Gateway
  │  PaymentGatewayFactory::make('stripe')
  │  Returns: StripePaymentGateway instance
  │
  ├─ Prepare Payment Parameters
  │  {
  │    'amount': 9999 (cents),
  │    'token': 'tok_visa',
  │    'email': 'customer@example.com',
  │    'description': 'Order payment for user 1'
  │  }
  │
  ├─ Process Payment with Stripe
  │  │
  │  ├─ StripePaymentGateway::process()
  │  │  {
  │  │    Stripe::setApiKey(...)
  │  │    Charge::create([
  │  │      'amount' => 9999,
  │  │      'currency' => 'usd',
  │  │      'source' => $token,
  │  │      'description' => '...',
  │  │      'receipt_email' => '...'
  │  │    ])
  │  │    return true
  │  │  }
  │  │
  │  ├─ Stripe API Response
  │  │  {
  │  │    "id": "ch_1234567890",
  │  │    "status": "succeeded",
  │  │    "amount": 9999,
  │  │    "currency": "usd"
  │  │  }
  │  │
  │  └─ Return: true (success) or false (failed)
  │
  ├─ DECISION POINT
  │  │
  │  ├─ If Payment FAILED
  │  │  │
  │  │  ├─ Log error to file
  │  │  ├─ Return 422 Validation Error
  │  │  └─ No order created
  │  │
  │  └─ If Payment SUCCEEDED
  │     │
  │     ├─ Create Order Record
  │     │  {
  │     │    'user_id': 1,
  │     │    'total_price': 99.99,
  │     │    'shipping_address': '...',
  │     │    'payment_method': 'stripe',
  │     │    'payment_id': 'ch_1234567890',
  │     │    'payment_status': 'completed'
  │     │  }
  │     │
  │     ├─ Dispatch OrderPlaced Event
  │     │  OrderPlaced::dispatch($order)
  │     │
  │     ├─ Event Routing
  │     │  EventServiceProvider routes to:
  │     │  └─ SendOrderConfirmation Listener
  │     │
  │     ├─ Listener Queues Email
  │     │  Mail::send(new OrderConfirmationMail($order))
  │     │
  │     ├─ Clear User's Cart
  │     │  CartRepository::clearCart($cart)
  │     │
  │     ├─ Return 201 Success Response
  │     │  {
  │     │    "success": true,
  │     │    "message": "Checkout completed successfully.",
  │     │    "data": { order object }
  │     │  }
  │     │
  │     └─ Email sent (async via queue worker)
  │
  └─ END
```

---

## 4. Event Listener Flow

```
┌──────────────────────────────────────────────────────────────┐
│                   EVENT SERVICE PROVIDER                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  protected $listen = [                                      │
│      OrderPlaced::class => [                               │
│          SendOrderConfirmation::class,                     │
│          // More listeners can be added here              │
│      ],                                                    │
│  ];                                                         │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│               EVENT DISPATCH FLOW                           │
│                                                              │
│  Application                                                │
│      │                                                       │
│      ├─ OrderPlaced::dispatch($order)                      │
│      │                                                       │
│      ├─ Event Bus Receives Event                           │
│      │                                                       │
│      ├─ Looks Up Listeners in $listen array                │
│      │                                                       │
│      ├─ For each listener:                                 │
│      │  │                                                   │
│      │  ├─ Instantiate Listener                            │
│      │  │                                                   │
│      │  ├─ Call handle() Method                            │
│      │  │  │                                               │
│      │  │  ├─ SendOrderConfirmation::handle()             │
│      │  │  │  │                                            │
│      │  │  │  ├─ Extract Order from Event                 │
│      │  │  │  │  $order = $event->order                   │
│      │  │  │  │                                            │
│      │  │  │  ├─ Create Mailable Instance                 │
│      │  │  │  │  new OrderConfirmationMail($order)        │
│      │  │  │  │                                            │
│      │  │  │  ├─ Queue via Mail Facade                    │
│      │  │  │  │  Mail::send($mailable)                    │
│      │  │  │  │                                            │
│      │  │  │  └─ Queue Manager Adds to Jobs Table        │
│      │  │  │                                              │
│      │  │  └─ Return                                      │
│      │  │                                                  │
│      │  └─ Move to Next Listener                          │
│      │                                                     │
│      └─ All Listeners Executed                            │
│                                                             │
│  Queue System                                              │
│      │                                                     │
│      ├─ Queue Worker: php artisan queue:listen           │
│      │                                                    │
│      ├─ Polls jobs table/Redis                           │
│      │                                                    │
│      ├─ Dequeues OrderConfirmationMail Job              │
│      │                                                    │
│      ├─ Processes Job                                    │
│      │  │                                                │
│      │  ├─ Load Mailable                                │
│      │  ├─ Build Email                                  │
│      │  ├─ Send via Mail Driver                         │
│      │  └─ Mark Job as Processed                        │
│      │                                                   │
│      └─ Continue Listening for Next Job                 │
│                                                           │
└──────────────────────────────────────────────────────────────┘
```

---

## 5. Database Schema Changes

```
ORDERS TABLE (Before)
┌──────────────────────────────────────────────────┐
│ id: BIGINT                                       │
│ user_id: BIGINT (FK)                            │
│ total_price: DECIMAL(10,2)                      │
│ status: VARCHAR(255)                            │
│ shipping_address: TEXT                          │
│ payment_method: VARCHAR(255)                    │
│ created_at: TIMESTAMP                           │
│ updated_at: TIMESTAMP                           │
└──────────────────────────────────────────────────┘

                      ⬇️ Migration

ORDERS TABLE (After)
┌──────────────────────────────────────────────────┐
│ id: BIGINT                                       │
│ user_id: BIGINT (FK)                            │
│ total_price: DECIMAL(10,2)                      │
│ status: VARCHAR(255)                            │
│ shipping_address: TEXT                          │
│ payment_method: VARCHAR(255)                    │
│ ┌─ payment_id: VARCHAR(255) ✨ NEW             │
│ └─ payment_status: VARCHAR(255) ✨ NEW         │
│ created_at: TIMESTAMP                           │
│ updated_at: TIMESTAMP                           │
└──────────────────────────────────────────────────┘
```

---

## 6. Email Processing Queue

```
Application Thread              Queue Thread           Mail Driver
─────────────────────           ────────────           ────────────

Mail::send($mailable)
    │
    ├─ Create Mailable
    │  OrderConfirmationMail
    │
    ├─ Serialize Job  ────────┐
    │  {                       │
    │    "class": "Mail",      │
    │    "data": {...}         ├──→ Jobs Table / Redis
    │  }                       │    Job ID: 12345
    │                          │    Status: pending
    └─ Return Immediately ────┘    Attempts: 0
       (Async Processing)
       │
       └─ Continue Handling Request
          (Don't wait for email)

Queue Listener                          Queue Worker
    (php artisan queue:listen)              (Background Process)
    
    Polls Jobs Table/Redis         Dequeues Job 12345
            │                              │
            ├─ Check for new jobs         ├─ Deserialize
            │                             │
            ├─ Find job: 12345            ├─ Instantiate Mailable
            │                             │
            └─ Pass to Worker     ──────→ ├─ Render Template
                                         │
                                         ├─ Call Mail Driver
                                         │  ├─ SMTP
                                         │  ├─ Sendmail
                                         │  ├─ Mailgun
                                         │  └─ etc.
                                         │
                                         ├─ Send Email
                                         │  └─ To: customer@example.com
                                         │
                                         ├─ Mark Success
                                         │  └─ Delete from Jobs
                                         │
                                         └─ Log Result
```

---

## 7. Configuration Hierarchy

```
ENVIRONMENT VARIABLES (.env)
         │
         ├─ PAYMENT_GATEWAY_DRIVER=stripe
         ├─ STRIPE_PUBLIC_KEY=pk_test_...
         ├─ STRIPE_SECRET_KEY=sk_test_...
         └─ PAYPAL_* keys...
         
                    ⬇️
         
CONFIGURATION FILES (config/)
         │
         ├─ payment.php
         │  ├─ default driver
         │  ├─ stripe config
         │  ├─ paypal config
         │  └─ other gateways
         │
         ├─ mail.php
         │  ├─ default mailer
         │  ├─ from address
         │  └─ mailer configs
         │
         └─ queue.php
            ├─ default connection
            ├─ redis config
            └─ database config

                    ⬇️
         
APPLICATION RUNTIME
         │
         ├─ PaymentGatewayFactory::make()
         │  uses config('payment.default')
         │
         ├─ StripePaymentGateway
         │  uses config('payment.stripe')
         │
         ├─ OrderConfirmationMail
         │  uses config('mail.from')
         │
         └─ Queue Worker
            uses config('queue.default')
```

---

## 8. Error Handling Flow

```
START: Process Payment
    │
    ├─ Try to create Stripe Charge
    │  │
    │  ├─ Success (Charge created)
    │  │  └─ Return true
    │  │
    │  └─ Exception caught
    │     │
    │     ├─ ApiErrorException
    │     │  ├─ Log error: "Stripe payment error: ..."
    │     │  ├─ Return false
    │     │  └─ No order created
    │     │
    │     └─ Other Exception
    │        ├─ Log general error
    │        ├─ Return false
    │        └─ No order created
    │
    ├─ Back in OrderService::checkout()
    │  │
    │  ├─ If false returned
    │  │  ├─ Throw ValidationException
    │  │  │  {
    │  │  │    "payment": [
    │  │  │      "Payment processing failed. Please try again."
    │  │  │    ]
    │  │  │  }
    │  │  ├─ Return 422 to client
    │  │  └─ No order created
    │  │
    │  └─ If true returned
    │     ├─ Continue with order creation
    │     └─ Dispatch event
    │
    └─ END
```

---

**Note**: All diagrams show the complete flow from request to email delivery. Reference these diagrams when:
- Understanding the system architecture
- Adding new payment gateways
- Debugging issues
- Explaining the system to team members
- Planning enhancements

<h1>Order Confirmation</h1>
<p>Hello {{ $order->user->name }},</p>
<p>Thank you for your order #{{ $order->id }}.</p>
<p>Total Amount: ${{ number_format($order->total_price, 2) }}</p>
<p>Status: {{ ucfirst($order->status) }}</p>
<p>We will notify you once your order has been shipped.</p>

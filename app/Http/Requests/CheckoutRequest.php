<?php

namespace App\Http\Requests;

class CheckoutRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,visa,paypal'],
        ];
    }
}

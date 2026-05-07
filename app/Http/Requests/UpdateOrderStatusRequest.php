<?php

namespace App\Http\Requests;

class UpdateOrderStatusRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled'],
        ];
    }
}

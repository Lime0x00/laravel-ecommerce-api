<?php

namespace App\Http\Requests;

class ProductUpdateRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', "unique:products,slug,{$productId}"],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0.01'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}

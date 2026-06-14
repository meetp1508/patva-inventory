<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage inventory') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id'         => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'           => ['required', 'integer', 'not_in:0'],
            'action_type'        => ['required', 'in:purchase,adjustment,return'],
            'remarks'            => ['nullable', 'string', 'max:255'],
        ];
    }
}

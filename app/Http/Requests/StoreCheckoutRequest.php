<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('billing access') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id'             => ['nullable', 'exists:customers,id'],
            'new_customer'            => ['nullable', 'array'],
            'new_customer.name'       => ['nullable', 'required_with:new_customer.phone', 'string', 'max:255'],
            'new_customer.phone'      => ['nullable', 'required_with:new_customer.name', 'string', 'max:30'],
            'new_customer.address'    => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            // Price and tax are resolved server-side from the product; any
            // client-supplied values here are ignored (kept only for the UI).
            'items.*.unit_price'      => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'payment_method'          => ['required', 'in:cash,upi,card'],
            'paid_amount'             => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

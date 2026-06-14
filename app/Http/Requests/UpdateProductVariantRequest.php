<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage products') ?? false;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant')->id;

        return [
            'variant_name'    => ['required', 'string', 'max:255'],
            'sku'             => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variantId)],
            'barcode'         => ['nullable', 'string', 'max:100', Rule::unique('product_variants', 'barcode')->ignore($variantId)],
            'additional_price'=> ['required', 'numeric'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
        ];
    }
}

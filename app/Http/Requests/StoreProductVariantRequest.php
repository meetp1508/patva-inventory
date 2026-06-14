<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage products') ?? false;
    }

    public function rules(): array
    {
        return [
            'variant_name'    => ['required', 'string', 'max:255'],
            'sku'             => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'barcode'         => ['nullable', 'string', 'max:100', 'unique:product_variants,barcode'],
            'additional_price'=> ['required', 'numeric'],
            'stock_quantity'  => ['required', 'integer', 'min:0'],
        ];
    }
}

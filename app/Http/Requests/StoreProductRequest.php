<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage products') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id'         => ['nullable', 'exists:categories,id'],
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode'             => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'description'         => ['nullable', 'string'],
            'purchase_price'      => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'type'                => ['required', 'in:simple,variant'],
            'images'              => ['nullable', 'array', 'max:8'],
            'images.*'            => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'           => ['boolean'],

            'variants'                  => ['nullable', 'array'],
            'variants.*.id'             => ['nullable', 'integer'],
            'variants.*.name'           => ['required', 'string', 'max:255'],
            'variants.*.sku'            => ['required', 'string', 'max:100', 'distinct'],
            'variants.*.additional_price' => ['nullable', 'numeric'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.value_ids'      => ['array'],
            'variants.*.value_ids.*'    => ['integer', 'exists:attribute_values,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}

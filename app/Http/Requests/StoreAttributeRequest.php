<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage products') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255', 'unique:attributes,name'],
            'values'         => ['required', 'array', 'min:1'],
            'values.*.value' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }
}

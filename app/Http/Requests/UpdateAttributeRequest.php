<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage products') ?? false;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute')->id;

        return [
            'name'       => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($attributeId)],
            'values'     => ['required', 'array', 'min:1'],
            'values.*.id'    => ['nullable', 'integer'],
            'values.*.value' => ['required', 'string', 'max:255'],
        ];
    }
}

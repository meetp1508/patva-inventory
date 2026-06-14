<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings access') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name'        => ['required', 'string', 'max:255'],
            'company_email'       => ['nullable', 'email', 'max:255'],
            'company_phone'       => ['nullable', 'string', 'max:30'],
            'company_address'     => ['nullable', 'string', 'max:1000'],
            'company_logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'currency_symbol'     => ['required', 'string', 'max:5'],
            'currency_code'       => ['required', 'string', 'max:5'],
            'default_tax_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix'      => ['required', 'string', 'max:20'],
            'invoice_footer'      => ['nullable', 'string', 'max:1000'],
            'whatsapp_driver'     => ['required', 'in:log,meta'],
            'whatsapp_phone_id'   => ['nullable', 'string', 'max:255'],
            'whatsapp_token'      => ['nullable', 'string', 'max:1000'],
        ];
    }
}

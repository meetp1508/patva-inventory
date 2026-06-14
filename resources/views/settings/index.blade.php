<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings" />
    </x-slot>

    <div x-data="{ tab: 'company' }" class="max-w-4xl mx-auto">
        <x-flash />

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-100 overflow-x-auto">
                @foreach (['company' => 'Company', 'invoice' => 'Invoice', 'tax' => 'Tax & Currency', 'whatsapp' => 'WhatsApp'] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('PUT')

                {{-- Company --}}
                <div x-show="tab === 'company'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings->get('company_name')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $settings->get('company_email')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $settings->get('company_phone')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="company_address" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('company_address', $settings->get('company_address')) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        @if ($settings->get('company_logo'))
                            <img src="{{ Storage::url($settings->get('company_logo')) }}" alt="Logo" class="h-16 mb-2 rounded">
                        @endif
                        <input type="file" name="company_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>

                {{-- Invoice --}}
                <div x-show="tab === 'invoice'" class="space-y-6" style="display:none;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number Prefix *</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->get('invoice_prefix')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <p class="mt-1 text-xs text-gray-500">Next invoice: {{ $settings->get('invoice_prefix') }}{{ str_pad($settings->get('invoice_next_number', '1'), 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Footer Note</label>
                        <textarea name="invoice_footer" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('invoice_footer', $settings->get('invoice_footer')) }}</textarea>
                    </div>
                </div>

                {{-- Tax & Currency --}}
                <div x-show="tab === 'tax'" class="space-y-6" style="display:none;">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency Symbol *</label>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->get('currency_symbol')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency Code *</label>
                            <input type="text" name="currency_code" value="{{ old('currency_code', $settings->get('currency_code')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Default Tax Rate (%) *</label>
                            <input type="number" step="0.01" name="default_tax_rate" value="{{ old('default_tax_rate', $settings->get('default_tax_rate')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div x-show="tab === 'whatsapp'" class="space-y-6" style="display:none;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Driver *</label>
                        <select name="whatsapp_driver" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="log" @selected($settings->get('whatsapp_driver') === 'log')>Log (testing only)</option>
                            <option value="meta" @selected($settings->get('whatsapp_driver') === 'meta')>Meta Cloud API</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_id" value="{{ old('whatsapp_phone_id', $settings->get('whatsapp_phone_id')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Access Token</label>
                        <input type="password" name="whatsapp_token" value="{{ old('whatsapp_token', $settings->get('whatsapp_token')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" autocomplete="off">
                    </div>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

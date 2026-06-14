<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Adjust Stock" :back="route('inventory.index')" />
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8" x-data="{ productId: '{{ $preselect }}' }">
        <x-flash />

        <form action="{{ route('inventory.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                <select name="product_id" x-model="productId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select product...</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }}) — stock {{ $product->stock_quantity }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Variant (optional)</label>
                <select name="product_variant_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— None —</option>
                    @foreach ($products as $product)
                        @foreach ($product->variants as $variant)
                            <option value="{{ $variant->id }}" x-show="productId == '{{ $product->id }}'">{{ $variant->variant_name }} (stock {{ $variant->stock_quantity }})</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Change *</label>
                    <input type="number" name="quantity" placeholder="e.g. 10 or -5" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <p class="text-xs text-gray-500 mt-1">Positive to add, negative to remove.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action Type *</label>
                    <select name="action_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="purchase">Purchase / Restock</option>
                        <option value="adjustment">Manual Adjustment</option>
                        <option value="return">Customer Return</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <textarea name="remarks" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex justify-end border-t border-gray-100 pt-6">
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-gray-900 font-medium mr-6">Cancel</a>
                <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold text-sm hover:bg-indigo-700">Apply Adjustment</button>
            </div>
        </form>
    </div>
</x-app-layout>

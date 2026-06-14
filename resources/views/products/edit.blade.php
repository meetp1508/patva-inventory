<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Edit Product — ' . $product->name" :back="route('products.index')">
            <x-slot name="action">
                <a href="{{ route('barcode.product', $product) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Print Barcode</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <x-flash />

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-100 pb-2 mb-4">Basic Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
                </div>

                @include('products.partials.image-uploader', ['product' => $product])

                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-100 pb-2 mb-4">Inventory & Pricing</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <div class="flex gap-2">
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        {{-- Associated with the regenerate form rendered after the product form (see below).
                             A nested <form> here is invalid HTML and silently breaks the surrounding product form. --}}
                        <button type="submit" form="regenerate-barcode-form" onclick="return confirm('Regenerate barcode?');" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm">↻</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">{{ setting('currency_symbol') }}</span></div>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" class="pl-7 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">{{ setting('currency_symbol') }}</span></div>
                        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" class="pl-7 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Low-Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="simple" @selected(old('type', $product->type) === 'simple')>Simple</option>
                        <option value="variant" @selected(old('type', $product->type) === 'variant')>Variant</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                @include('products.partials.variant-builder', ['product' => $product, 'attributeList' => $attributes])
            </div>

            <label class="flex items-center mb-6">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (visible in POS)</span>
            </label>

            <div class="flex items-center justify-end mt-8 border-t border-gray-100 pt-6">
                <div>
                    <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900 font-medium mr-6">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition">
                        Update Product
                    </button>
                </div>
            </div>
        </form>

        {{-- Kept outside the product form: HTML does not allow nested forms. --}}
        <form id="regenerate-barcode-form" action="{{ route('barcode.regenerate', $product) }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-app-layout>

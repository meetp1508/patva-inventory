<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$product->name" :back="route('products.index')">
            <x-slot name="action">
                <div class="flex gap-2">
                    <a href="{{ route('barcode.product', $product) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Print Barcode</a>
                    <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Edit</a>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
                    <div><dt class="text-gray-500">Barcode</dt><dd class="font-medium">{{ $product->barcode ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Category</dt><dd class="font-medium">{{ $product->category?->name ?? 'Uncategorized' }}</dd></div>
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium capitalize">{{ $product->type }}</dd></div>
                    <div><dt class="text-gray-500">Purchase Price</dt><dd class="font-medium">{{ money($product->purchase_price) }}</dd></div>
                    <div><dt class="text-gray-500">Selling Price</dt><dd class="font-medium">{{ money($product->selling_price) }}</dd></div>
                    <div><dt class="text-gray-500">Tax Rate</dt><dd class="font-medium">{{ $product->tax_rate }}%</dd></div>
                    <div><dt class="text-gray-500">Stock</dt><dd class="font-medium">{{ $product->stock_quantity }} (threshold {{ $product->low_stock_threshold }})</dd></div>
                </dl>

                @if ($product->description)
                    <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-700">{{ $product->description }}</div>
                @endif
            </div>

            @include('products.variants._manage', ['product' => $product])
        </div>

        <div class="space-y-6">
            @if ($product->images->isNotEmpty())
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4"
                     x-data="{ active: '{{ $product->images->first()->url }}' }">
                    <img :src="active" alt="" class="w-full aspect-square rounded-lg object-cover bg-gray-50">
                    @if ($product->images->count() > 1)
                        <div class="grid grid-cols-5 gap-2 mt-3">
                            @foreach ($product->images as $image)
                                <button type="button" @click="active = '{{ $image->url }}'"
                                        class="aspect-square rounded-lg overflow-hidden border-2 transition"
                                        :class="active === '{{ $image->url }}' ? 'border-indigo-500' : 'border-transparent'">
                                    <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($product->image_url)
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4">
                    <img src="{{ $product->image_url }}" alt="" class="w-full rounded-lg object-cover">
                </div>
            @endif

            @if ($product->barcode)
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-2">Barcode</p>
                    <img src="{{ app(\App\Services\BarcodeService::class)->pngDataUri($product->barcode) }}" alt="{{ $product->barcode }}" class="mx-auto">
                    <p class="text-sm font-mono mt-2">{{ $product->barcode }}</p>
                    <a href="{{ route('barcode.download', $product) }}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 inline-block">Download PNG</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

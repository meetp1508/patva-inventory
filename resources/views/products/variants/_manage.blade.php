<div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Variants</h3>
        <a href="{{ route('products.edit', $product) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Edit variants</a>
    </div>

    @if ($product->variants->isEmpty())
        <div class="py-6 text-center text-gray-500 text-sm">
            <p>No variants yet.</p>
            <p class="text-xs text-gray-400 mt-1">Open <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a> and pick attribute values (e.g. Color, Size) to generate variants automatically.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="text-left font-medium py-2 pr-3">Variant</th>
                        <th class="text-left font-medium py-2 px-3">SKU</th>
                        <th class="text-left font-medium py-2 px-3">Barcode</th>
                        <th class="text-right font-medium py-2 px-3">Price</th>
                        <th class="text-right font-medium py-2 pl-3">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($product->variants as $variant)
                        <tr>
                            <td class="py-2 pr-3 font-medium text-gray-800">{{ $variant->variant_name }}</td>
                            <td class="py-2 px-3 text-gray-600">{{ $variant->sku }}</td>
                            <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ $variant->barcode ?: '—' }}</td>
                            <td class="py-2 px-3 text-right">{{ money($product->selling_price + $variant->additional_price) }}</td>
                            <td class="py-2 pl-3 text-right">{{ $variant->stock_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

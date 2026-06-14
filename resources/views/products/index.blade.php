<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Products">
            <x-slot name="action">
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Product
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, SKU, or barcode..." class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select name="category" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Filter</button>
                @if (request()->hasAny(['q', 'category']))
                    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm">
                        <th class="py-4 px-6 font-medium">Product</th>
                        <th class="py-4 px-6 font-medium">Category</th>
                        <th class="py-4 px-6 font-medium">SKU / Barcode</th>
                        <th class="py-4 px-6 font-medium">Price</th>
                        <th class="py-4 px-6 font-medium">Stock</th>
                        <th class="py-4 px-6 font-medium">Status</th>
                        <th class="py-4 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500 capitalize">{{ $product->type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-gray-500">{{ $product->category?->name ?? 'Uncategorized' }}</td>
                            <td class="py-4 px-6">
                                <div class="text-sm text-gray-900">{{ $product->sku }}</div>
                                <div class="text-xs text-gray-500">{{ $product->barcode ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm font-medium text-gray-900">{{ money($product->selling_price) }}</div>
                                <div class="text-xs text-gray-500">Cost: {{ money($product->purchase_price) }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $color = $product->stock_quantity <= 0 ? 'red' : ($product->isLowStock() ? 'yellow' : 'green');
                                @endphp
                                <x-badge :color="$color">{{ $product->stock_quantity }}</x-badge>
                            </td>
                            <td class="py-4 px-6">
                                <x-badge :color="$product->is_active ? 'green' : 'gray'">{{ $product->is_active ? 'Active' : 'Inactive' }}</x-badge>
                            </td>
                            <td class="py-4 px-6 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('products.show', $product) }}" class="text-gray-600 hover:text-gray-900 text-sm">View</a>
                                <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-500">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</x-app-layout>

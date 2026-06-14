<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Inventory">
            <x-slot name="action">
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">
                    Adjust Stock
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-medium text-gray-900">Stock History</h3>
            </div>

            <form method="GET" class="p-4 border-b border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                <select name="action" class="rounded-lg border-gray-300">
                    <option value="">All Actions</option>
                    @foreach (['purchase', 'sale', 'adjustment', 'return'] as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300">
                <button class="px-3 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="py-3 px-4 text-left">When</th>
                            <th class="py-3 px-4 text-left">Product</th>
                            <th class="py-3 px-4 text-left">Action</th>
                            <th class="py-3 px-4 text-right">Qty</th>
                            <th class="py-3 px-4 text-right">Before → After</th>
                            <th class="py-3 px-4 text-left">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M, H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $log->product?->name }}</div>
                                    @if ($log->variant)
                                        <div class="text-xs text-gray-500">{{ $log->variant->variant_name }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $color = match ($log->action_type) {
                                            'purchase' => 'green',
                                            'sale' => 'blue',
                                            'return' => 'yellow',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <x-badge :color="$color">{{ $log->action_type }}</x-badge>
                                </td>
                                <td class="py-3 px-4 text-right font-medium {{ $log->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                                </td>
                                <td class="py-3 px-4 text-right text-gray-500">{{ $log->balance_before }} → {{ $log->balance_after }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ $log->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-gray-500">No movements yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-100"><h3 class="font-medium text-gray-900">Low Stock</h3></div>
            <div class="divide-y divide-gray-100">
                @forelse ($lowStock as $product)
                    <a href="{{ route('inventory.create', ['product_id' => $product->id]) }}" class="flex justify-between items-center p-4 hover:bg-gray-50">
                        <div>
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                        </div>
                        <x-badge :color="$product->stock_quantity > 0 ? 'yellow' : 'red'">{{ $product->stock_quantity }}</x-badge>
                    </a>
                @empty
                    <div class="p-6 text-center text-gray-500 text-sm">All well-stocked. 🎉</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

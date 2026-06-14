<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" />
    </x-slot>

    @php
        $cards = [
            ['label' => "Today's Sales", 'value' => money($stats['today_sales']), 'bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'This Month', 'value' => money($stats['month_sales']), 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['label' => 'Total Products', 'value' => number_format($stats['total_products']), 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label' => 'Low Stock Items', 'value' => number_format($stats['low_stock_count']), 'bg' => 'bg-red-50', 'text' => 'text-red-600', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach ($cards as $card)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full {{ $card['bg'] }} {{ $card['text'] }}">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="mb-1 text-sm font-medium text-gray-600">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sales — Last 14 Days</h3>
            <canvas id="salesChart" height="100"></canvas>
        </div>
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Products</h3>
            <canvas id="topProductsChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Invoices</h3>
            <div class="divide-y divide-gray-100">
                @forelse ($recentInvoices as $invoice)
                    <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->customer?->name ?? 'Walk-in' }} · {{ $invoice->created_at->format('d M, H:i') }}</p>
                        </div>
                        <span class="font-semibold text-gray-900">{{ money($invoice->total_amount) }}</span>
                    </a>
                @empty
                    <div class="text-center py-8 text-gray-500">No recent invoices.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Low Stock Alerts</h3>
            <div class="divide-y divide-gray-100">
                @forelse ($lowStock as $product)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                        </div>
                        <x-badge :color="$product->stock_quantity > 0 ? 'yellow' : 'red'">{{ $product->stock_quantity }} left</x-badge>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">All products well-stocked. 🎉</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const salesTrend = @json($salesTrend);
            new Chart(document.getElementById('salesChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(salesTrend).map(d => new Date(d).toLocaleDateString(undefined, {month:'short', day:'numeric'})),
                    datasets: [{
                        label: 'Sales',
                        data: Object.values(salesTrend),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            const topProducts = @json($topProducts);
            new Chart(document.getElementById('topProductsChart'), {
                type: 'doughnut',
                data: {
                    labels: topProducts.map(p => p.product_name),
                    datasets: [{
                        data: topProducts.map(p => p.total_qty),
                        backgroundColor: ['#4f46e5','#10b981','#f59e0b','#ef4444','#3b82f6'],
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        </script>
    @endpush
</x-app-layout>

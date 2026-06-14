<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Analytics & Reports">
            <x-slot name="action">
                <div class="flex gap-2">
                    <a href="{{ route('reports.export.sales', ['from' => $from, 'to' => $to, 'format' => 'xlsx']) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700">Export Sales (XLSX)</a>
                    <a href="{{ route('reports.export.sales', ['from' => $from, 'to' => $to, 'format' => 'csv']) }}" class="px-3 py-2 bg-gray-700 text-white rounded-lg text-xs hover:bg-gray-800">CSV</a>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <form method="GET" class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4 mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Apply</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Revenue</p>
            <p class="text-2xl font-bold mt-1">{{ money($profit['revenue']) }}</p>
        </div>
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Cost of Goods</p>
            <p class="text-2xl font-bold mt-1">{{ money($profit['cost']) }}</p>
        </div>
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Profit</p>
            <p class="text-2xl font-bold mt-1 text-green-600">{{ money($profit['profit']) }}</p>
        </div>
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Margin</p>
            <p class="text-2xl font-bold mt-1">{{ $profit['margin'] }}%</p>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Trend</h3>
        <canvas id="salesByDay" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-100"><h3 class="font-medium text-gray-900">Top Products</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr><th class="py-2 px-4 text-left">Product</th><th class="py-2 px-4 text-right">Qty</th><th class="py-2 px-4 text-right">Revenue</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($topProducts as $row)
                        <tr><td class="py-2 px-4">{{ $row->product_name }}</td><td class="py-2 px-4 text-right">{{ $row->total_qty }}</td><td class="py-2 px-4 text-right font-medium">{{ money($row->total_revenue) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-100"><h3 class="font-medium text-gray-900">Top Customers</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr><th class="py-2 px-4 text-left">Customer</th><th class="py-2 px-4 text-right">Orders</th><th class="py-2 px-4 text-right">Spent</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($topCustomers as $row)
                        <tr><td class="py-2 px-4">{{ $row->name }}<div class="text-xs text-gray-500">{{ $row->phone }}</div></td><td class="py-2 px-4 text-right">{{ $row->invoice_count }}</td><td class="py-2 px-4 text-right font-medium">{{ money($row->total_spent) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-medium text-gray-900">Inventory Valuation</h3>
            <a href="{{ route('reports.export.inventory', ['format' => 'xlsx']) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Export XLSX</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="py-2 px-4 text-left">Product</th>
                        <th class="py-2 px-4 text-right">Stock</th>
                        <th class="py-2 px-4 text-right">Cost</th>
                        <th class="py-2 px-4 text-right">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($valuation as $row)
                        <tr>
                            <td class="py-2 px-4">{{ $row->name }}<div class="text-xs text-gray-500">{{ $row->sku }}</div></td>
                            <td class="py-2 px-4 text-right">{{ $row->stock_quantity }}</td>
                            <td class="py-2 px-4 text-right">{{ money($row->stock_cost) }}</td>
                            <td class="py-2 px-4 text-right font-medium">{{ money($row->stock_value) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const data = @json($salesByDay);
            new Chart(document.getElementById('salesByDay'), {
                type: 'line',
                data: {
                    labels: data.map(d => d.day),
                    datasets: [{
                        label: 'Revenue',
                        data: data.map(d => d.total),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        </script>
    @endpush
</x-app-layout>

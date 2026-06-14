<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Invoices">
            <x-slot name="action">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs uppercase tracking-widest font-semibold hover:bg-indigo-700">New Sale</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Invoice #..." class="rounded-lg border-gray-300">
            <select name="customer_id" class="rounded-lg border-gray-300">
                <option value="">All Customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-gray-300">
                <option value="">All Statuses</option>
                @foreach (['paid', 'partial', 'unpaid'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
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
                        <th class="py-3 px-4 text-left">Number</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Customer</th>
                        <th class="py-3 px-4 text-right">Items</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-mono text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $invoice->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4">{{ $invoice->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4 text-right">{{ $invoice->items_count ?? $invoice->items()->count() }}</td>
                            <td class="py-3 px-4 text-right font-semibold">{{ money($invoice->total_amount) }}</td>
                            <td class="py-3 px-4">
                                @php
                                    $color = match ($invoice->status) {
                                        'paid' => 'green',
                                        'partial' => 'yellow',
                                        default => 'red',
                                    };
                                @endphp
                                <x-badge :color="$color">{{ $invoice->status }}</x-badge>
                            </td>
                            <td class="py-3 px-4 text-right space-x-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">View</a>
                                <a href="{{ route('pos.invoice.pdf', $invoice) }}" class="text-gray-600 hover:text-gray-900 font-medium text-xs">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-500">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">{{ $invoices->links() }}</div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$customer->name" :back="route('customers.index')">
            <x-slot name="action">
                <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Edit</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact</h3>
                <dl class="text-sm space-y-2">
                    <div><dt class="text-gray-500 text-xs uppercase tracking-wide">Phone</dt><dd class="font-medium">{{ $customer->phone }}</dd></div>
                    @if ($customer->address)
                        <div><dt class="text-gray-500 text-xs uppercase tracking-wide">Address</dt><dd class="whitespace-pre-line">{{ $customer->address }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Invoices</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $invoiceCount }}</p>
                </div>
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Lifetime Spend</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ money($totalSpent) }}</p>
                </div>
            </div>

            @if ((float) $customer->outstanding_balance > 0)
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                    <p class="text-xs text-red-700 uppercase tracking-wide">Outstanding Balance</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ money($customer->outstanding_balance) }}</p>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-100"><h3 class="font-medium text-gray-900">Purchase History</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="py-3 px-4 text-left">Invoice</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($customer->invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-mono"><a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:underline">{{ $invoice->invoice_number }}</a></td>
                                <td class="py-3 px-4 text-gray-500">{{ $invoice->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-right font-semibold">{{ money($invoice->total_amount) }}</td>
                                <td class="py-3 px-4"><x-badge :color="$invoice->status === 'paid' ? 'green' : ($invoice->status === 'partial' ? 'yellow' : 'red')">{{ $invoice->status }}</x-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-gray-500">No purchases yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

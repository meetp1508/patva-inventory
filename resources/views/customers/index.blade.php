<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customers">
            <x-slot name="action">
                <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs uppercase tracking-widest font-semibold hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Customer
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or phone..." class="flex-1 rounded-lg border-gray-300 text-sm">
            <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm">
                        <th class="py-4 px-6 font-medium">Name</th>
                        <th class="py-4 px-6 font-medium">Phone</th>
                        <th class="py-4 px-6 font-medium">Invoices</th>
                        <th class="py-4 px-6 font-medium">Balance</th>
                        <th class="py-4 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $customer->name }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $customer->phone }}</td>
                            <td class="py-4 px-6"><x-badge color="blue">{{ $customer->invoices_count }}</x-badge></td>
                            <td class="py-4 px-6">
                                <span class="font-medium {{ (float) $customer->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ money($customer->outstanding_balance) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('customers.show', $customer) }}" class="text-gray-600 hover:text-gray-900 text-sm">View</a>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">{{ $customers->links() }}</div>
    </div>
</x-app-layout>

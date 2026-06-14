<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Activity Log" />
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search description..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Action" class="w-40 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm">
                        <th class="py-3 px-6 font-medium">When</th>
                        <th class="py-3 px-6 font-medium">User</th>
                        <th class="py-3 px-6 font-medium">Action</th>
                        <th class="py-3 px-6 font-medium">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 text-sm">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-6 text-gray-500 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                            <td class="py-3 px-6">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="py-3 px-6"><x-badge color="indigo">{{ $log->action }}</x-badge></td>
                            <td class="py-3 px-6">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-gray-500">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</x-app-layout>

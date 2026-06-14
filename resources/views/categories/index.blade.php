<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Categories">
            <x-slot name="action">
                <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Category
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search categories..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm">
                        <th class="py-4 px-6 font-medium">Name</th>
                        <th class="py-4 px-6 font-medium">Slug</th>
                        <th class="py-4 px-6 font-medium">Products</th>
                        <th class="py-4 px-6 font-medium">Description</th>
                        <th class="py-4 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $category->slug }}</td>
                            <td class="py-4 px-6"><x-badge color="blue">{{ $category->products_count }}</x-badge></td>
                            <td class="py-4 px-6 text-gray-500">{{ Str::limit($category->description, 50) }}</td>
                            <td class="py-4 px-6 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">{{ $categories->links() }}</div>
    </div>
</x-app-layout>

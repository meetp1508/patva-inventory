<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Attributes">
            <x-slot name="action">
                <a href="{{ route('attributes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Attribute
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-100 text-sm text-gray-500">
            Define reusable options like <strong>Color</strong> or <strong>Size</strong>. Pick their values on a product to auto-generate variants.
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm">
                        <th class="py-4 px-6 font-medium">Attribute</th>
                        <th class="py-4 px-6 font-medium">Values</th>
                        <th class="py-4 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($attributes as $attribute)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $attribute->name }}</td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($attribute->values as $value)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $value->value }}</span>
                                    @empty
                                        <span class="text-gray-400 text-sm">No values</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('attributes.edit', $attribute) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</a>
                                <form action="{{ route('attributes.destroy', $attribute) }}" method="POST" class="inline" onsubmit="return confirm('Delete this attribute?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-gray-500">No attributes yet. Add one like “Color” or “Size”.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

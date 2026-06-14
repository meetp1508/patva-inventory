@props(['attribute' => null])

@php
    $existingValues = $attribute
        ? $attribute->values->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->values()
        : collect(old('values', [['id' => null, 'value' => '']]));
    // Normalise old() input (which may be a plain list) into {id, value} rows.
    $rows = collect($existingValues)->map(fn ($r) => [
        'id'    => is_array($r) ? ($r['id'] ?? null) : null,
        'value' => is_array($r) ? ($r['value'] ?? '') : $r,
    ])->values();
@endphp

<div x-data="attributeValues(@js($rows))">
    <div class="mb-6">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Attribute Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $attribute?->name) }}"
               placeholder="e.g. Color, Size, Material"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Values *</label>
        <p class="text-xs text-gray-400 mb-3">e.g. for Color: Red, Blue, Green. Add one per row.</p>

        <div class="space-y-2">
            <template x-for="(row, i) in rows" :key="row.key">
                <div class="flex items-center gap-2">
                    <input type="hidden" :name="`values[${i}][id]`" :value="row.id ?? ''">
                    <input type="text" :name="`values[${i}][value]`" x-model="row.value"
                           placeholder="Value"
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                            class="h-9 w-9 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500"
                            title="Remove">&times;</button>
                </div>
            </template>
        </div>

        <button type="button" @click="addRow()"
                class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Add value</button>

        @error('values')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @foreach ($errors->get('values.*.value') as $messages)
            <p class="mt-1 text-sm text-red-600">{{ $messages[0] }}</p>
        @endforeach
    </div>

    <div class="flex items-center justify-end border-t border-gray-100 pt-6">
        <a href="{{ route('attributes.index') }}" class="text-gray-600 hover:text-gray-900 font-medium mr-6">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition">
            {{ $attribute ? 'Update Attribute' : 'Save Attribute' }}
        </button>
    </div>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('attributeValues', (initial = []) => ({
                seq: 0,
                rows: [],
                init() {
                    const source = initial.length ? initial : [{ id: null, value: '' }];
                    this.rows = source.map((r) => ({ key: 'r' + this.seq++, id: r.id ?? null, value: r.value ?? '' }));
                },
                addRow() {
                    this.rows.push({ key: 'r' + this.seq++, id: null, value: '' });
                },
                removeRow(i) {
                    this.rows.splice(i, 1);
                    if (this.rows.length === 0) this.addRow();
                },
            }));
        });
    </script>
    @endpush
@endonce

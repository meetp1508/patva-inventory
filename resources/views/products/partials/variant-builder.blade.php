@props(['product' => null, 'attributeList' => null])

@php
    $existingVariants = $product
        ? $product->variants->map(fn ($v) => [
            'id'               => $v->id,
            'sku'              => $v->sku,
            'additional_price' => (float) $v->additional_price,
            'stock_quantity'   => (int) $v->stock_quantity,
            'value_ids'        => $v->values->pluck('id')->values(),
        ])->values()
        : collect();

    $attributeData = collect($attributeList)->map(fn ($a) => [
        'id'     => $a->id,
        'name'   => $a->name,
        'values' => $a->values->map(fn ($v) => ['id' => $v->id, 'value' => $v->value])->values(),
    ])->values();
@endphp

<div class="md:col-span-2 mt-2" x-data="variantBuilder(@js($attributeData), @js($existingVariants))">
    <input type="hidden" name="variant_builder" value="1">

    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-4">
        <h3 class="text-lg font-medium text-gray-900">Variants</h3>
        <a href="{{ route('attributes.index') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800">Manage attributes ↗</a>
    </div>

    @if ($attributeData->isEmpty())
        <p class="text-sm text-gray-500">
            No attributes defined yet. Create attributes like <strong>Color</strong> or <strong>Size</strong> on the
            <a href="{{ route('attributes.index') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">Attributes page</a>,
            then come back to build variants.
        </p>
    @else
        <p class="text-xs text-gray-400 mb-4">Tick the options this product comes in. Every combination is generated automatically — just set the stock for each.</p>

        {{-- Attribute value pickers --}}
        <div class="space-y-3 mb-5">
            <template x-for="attr in attributes" :key="attr.id">
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1.5" x-text="attr.name"></div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="val in attr.values" :key="val.id">
                            <button type="button" @click="toggle(attr.id, val.id)"
                                    :class="isSelected(attr.id, val.id)
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-indigo-400'"
                                    class="px-3 py-1 rounded-full border text-sm transition"
                                    x-text="val.value"></button>
                        </template>
                        <span x-show="attr.values.length === 0" class="text-xs text-gray-400">No values — add some on the Attributes page.</span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Generated variant rows --}}
        <div x-show="rows.length > 0" x-cloak class="border border-gray-100 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left font-medium px-3 py-2">Variant</th>
                        <th class="text-left font-medium px-3 py-2 w-40">SKU</th>
                        <th class="text-right font-medium px-3 py-2 w-28">+ Price</th>
                        <th class="text-right font-medium px-3 py-2 w-24">Stock</th>
                        <th class="px-3 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(row, i) in rows" :key="row.key">
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-800" x-text="row.name"></td>
                            <td class="px-3 py-2">
                                <input type="text" x-model="row.sku" :name="`variants[${i}][sku]`"
                                       class="w-full rounded-md border-gray-200 text-xs font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" x-model.number="row.additional_price" :name="`variants[${i}][additional_price]`"
                                       class="w-full rounded-md border-gray-200 text-sm text-right">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" x-model.number="row.stock_quantity" :name="`variants[${i}][stock_quantity]`"
                                       class="w-full rounded-md border-gray-200 text-sm text-right">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeRow(i)" class="text-gray-400 hover:text-red-600" title="Remove this combination">&times;</button>
                            </td>

                            {{-- Hidden fields submitted with the product form --}}
                            <template x-if="row.id"><input type="hidden" :name="`variants[${i}][id]`" :value="row.id"></template>
                            <input type="hidden" :name="`variants[${i}][name]`" :value="row.name">
                            <template x-for="vid in row.value_ids" :key="vid">
                                <input type="hidden" :name="`variants[${i}][value_ids][]`" :value="vid">
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <p x-show="rows.length > 0" x-cloak class="text-xs text-gray-400 mt-2">
            <span x-text="rows.length"></span> variant(s). Product stock will be the total of all variant stock.
        </p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('variantBuilder', (attributes = [], existing = []) => ({
                attributes,
                selected: {},      // attributeId -> [valueId,...]
                rows: [],
                excluded: [],      // signatures the user removed
                seq: 0,

                init() {
                    // Pre-select values used by existing variants and show those rows as-is
                    // (do NOT auto-expand to the full grid until the user changes a selection).
                    existing.forEach((v) => {
                        (v.value_ids || []).forEach((vid) => {
                            const attr = this.attributes.find(a => a.values.some(x => x.id === vid));
                            if (!attr) return;
                            (this.selected[attr.id] ??= []);
                            if (!this.selected[attr.id].includes(vid)) this.selected[attr.id].push(vid);
                        });
                    });

                    this.rows = existing.map((v) => ({
                        key: 'v' + this.seq++,
                        id: v.id,
                        value_ids: [...(v.value_ids || [])],
                        name: this.nameFor(v.value_ids || []),
                        sku: v.sku,
                        additional_price: v.additional_price ?? 0,
                        stock_quantity: v.stock_quantity ?? 0,
                    }));
                },

                isSelected(attrId, valueId) {
                    return (this.selected[attrId] || []).includes(valueId);
                },

                toggle(attrId, valueId) {
                    const arr = (this.selected[attrId] ??= []);
                    const idx = arr.indexOf(valueId);
                    if (idx === -1) arr.push(valueId); else arr.splice(idx, 1);
                    this.regenerate();
                },

                sig(ids) {
                    return [...ids].sort((a, b) => a - b).join('-');
                },

                labelFor(valueId) {
                    for (const a of this.attributes) {
                        const v = a.values.find(x => x.id === valueId);
                        if (v) return v.value;
                    }
                    return '';
                },

                nameFor(valueIds) {
                    // order labels by attribute order
                    const parts = [];
                    for (const a of this.attributes) {
                        const v = a.values.find(x => valueIds.includes(x.id));
                        if (v) parts.push(v.value);
                    }
                    return parts.join(' / ');
                },

                suggestSku(valueIds) {
                    const base = (document.querySelector('input[name="sku"]')?.value || 'VAR').trim().toUpperCase().replace(/\s+/g, '-');
                    const parts = [];
                    for (const a of this.attributes) {
                        const v = a.values.find(x => valueIds.includes(x.id));
                        if (v) parts.push(v.value.toUpperCase().replace(/[^A-Z0-9]+/g, ''));
                    }
                    return [base, ...parts].join('-');
                },

                regenerate() {
                    const prev = {};
                    for (const r of this.rows) prev[this.sig(r.value_ids)] = r;

                    const groups = this.attributes
                        .map(a => ({ vals: a.values.filter(v => (this.selected[a.id] || []).includes(v.id)) }))
                        .filter(g => g.vals.length > 0);

                    if (groups.length === 0) { this.rows = []; return; }

                    let combos = [[]];
                    for (const g of groups) {
                        const next = [];
                        for (const combo of combos) for (const v of g.vals) next.push([...combo, v.id]);
                        combos = next;
                    }

                    this.rows = combos
                        .filter(ids => !this.excluded.includes(this.sig(ids)))
                        .map((ids) => {
                            const s = this.sig(ids);
                            if (prev[s]) return { ...prev[s], value_ids: ids, name: this.nameFor(ids) };
                            return {
                                key: 'v' + this.seq++,
                                id: null,
                                value_ids: ids,
                                name: this.nameFor(ids),
                                sku: this.suggestSku(ids),
                                additional_price: 0,
                                stock_quantity: 0,
                            };
                        });
                },

                removeRow(i) {
                    this.excluded.push(this.sig(this.rows[i].value_ids));
                    this.rows.splice(i, 1);
                },
            }));
        });
    </script>
    @endpush
@endonce

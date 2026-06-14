@props(['product' => null])

@php
    $existing = $product
        ? $product->images->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])->values()
        : collect();
@endphp

<div class="md:col-span-2" x-data="productImages(@js($existing))">
    <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>

    <div @click="$refs.input.click()"
         @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop.prevent="onDrop($event)"
         :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 hover:border-indigo-300'"
         class="cursor-pointer border-2 border-dashed rounded-xl px-6 py-8 text-center transition-colors">
        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
        </svg>
        <p class="mt-2 text-sm text-gray-600"><span class="text-indigo-600 font-medium">Click to upload</span> or drag &amp; drop</p>
        <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — up to 2&nbsp;MB each, max 8 images</p>
        <input x-ref="input" type="file" name="images[]" accept="image/*" multiple class="hidden" @change="onSelect($event)">
    </div>

    <p class="text-xs text-gray-500 mt-2" x-show="visibleCount > 0" x-cloak>
        <span x-text="visibleCount"></span> image(s) selected — the first is used as the primary thumbnail.
    </p>

    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3 mt-3">
        <template x-for="(tile, i) in tiles" :key="tile.key">
            <div x-show="!tile.deleted" x-cloak
                 class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-50 aspect-square"
                 :class="firstVisibleIndex === i ? 'ring-2 ring-indigo-500' : ''">
                <img :src="tile.url" alt="" class="h-full w-full object-cover">
                <button type="button" @click="remove(i)"
                        title="Remove image"
                        class="absolute top-1 right-1 h-6 w-6 rounded-full bg-black/60 hover:bg-red-600 text-white text-xs leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                    &times;
                </button>
                <span x-show="firstVisibleIndex === i"
                      class="absolute bottom-0 inset-x-0 bg-indigo-600 text-white text-[10px] text-center py-0.5">Primary</span>
            </div>
        </template>
    </div>

    {{-- Hidden inputs telling the server which existing images to delete --}}
    <template x-for="id in deletedIds" :key="id">
        <input type="hidden" name="deleted_images[]" :value="id">
    </template>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productImages', (existing = []) => ({
                MAX: 8,
                dragging: false,
                tiles: existing.map((img) => ({
                    key: 'e' + img.id,
                    kind: 'existing',
                    id: img.id,
                    url: img.url,
                    file: null,
                    deleted: false,
                })),
                seq: 0,

                get visibleCount() {
                    return this.tiles.filter((t) => !t.deleted).length;
                },
                get firstVisibleIndex() {
                    return this.tiles.findIndex((t) => !t.deleted);
                },
                get deletedIds() {
                    return this.tiles.filter((t) => t.kind === 'existing' && t.deleted).map((t) => t.id);
                },

                onSelect(e) {
                    this.addFiles(e.target.files);
                },
                onDrop(e) {
                    this.dragging = false;
                    this.addFiles(e.dataTransfer.files);
                },
                addFiles(fileList) {
                    for (const file of fileList) {
                        if (!file.type.startsWith('image/')) continue;
                        if (this.visibleCount >= this.MAX) {
                            alert('You can upload up to ' + this.MAX + ' images.');
                            break;
                        }
                        this.tiles.push({
                            key: 'n' + this.seq++,
                            kind: 'new',
                            id: null,
                            url: URL.createObjectURL(file),
                            file,
                            deleted: false,
                        });
                    }
                    this.syncInput();
                },
                remove(i) {
                    const tile = this.tiles[i];
                    if (tile.kind === 'new') {
                        URL.revokeObjectURL(tile.url);
                        this.tiles.splice(i, 1);
                        this.syncInput();
                    } else {
                        tile.deleted = true; // queued for server-side deletion via hidden input
                    }
                },
                syncInput() {
                    const dt = new DataTransfer();
                    this.tiles
                        .filter((t) => t.kind === 'new' && !t.deleted)
                        .forEach((t) => dt.items.add(t.file));
                    this.$refs.input.files = dt.files;
                },
            }));
        });
    </script>
    @endpush
@endonce

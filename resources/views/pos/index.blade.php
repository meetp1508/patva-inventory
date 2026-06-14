<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Point of Sale" />
    </x-slot>

    <div x-data="pos()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Search + product results --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <label class="block text-xs font-medium text-gray-500 mb-2">Scan barcode or search</label>
                <input
                    type="text"
                    x-model="query"
                    @input.debounce.250ms="search()"
                    @keydown.enter.prevent="quickAddByBarcode()"
                    x-ref="searchInput"
                    autofocus
                    placeholder="Scan barcode or type name / SKU..."
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100">
                    <template x-for="p in results" :key="p.id">
                        <div class="p-3 hover:bg-gray-50 flex justify-between items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 truncate" x-text="p.name"></div>
                                <div class="text-xs text-gray-500" x-text="p.sku + ' · stock ' + p.stock"></div>
                            </div>
                            <div class="text-sm font-semibold whitespace-nowrap" x-text="formatMoney(p.price)"></div>
                            <template x-if="p.variants && p.variants.length === 0">
                                <button @click="addItem(p, null)" :disabled="p.stock <= 0" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed">Add</button>
                            </template>
                            <template x-if="p.variants && p.variants.length > 0">
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700">Variants ▾</button>
                                    <div x-show="open" x-transition class="absolute right-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                                        <template x-for="v in p.variants" :key="v.id">
                                            <button @click="addItem(p, v); open = false" :disabled="v.stock <= 0" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm flex justify-between disabled:opacity-40">
                                                <span x-text="v.name"></span>
                                                <span class="text-gray-500" x-text="formatMoney(v.price)"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <div x-show="results.length === 0" class="p-8 text-center text-gray-400 text-sm">Type or scan to find products.</div>
                </div>
            </div>
        </div>

        {{-- Cart + checkout --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:sticky lg:top-6 lg:max-h-[calc(100vh-10.5rem)]">
            <div class="p-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-medium text-gray-500">Customer</label>
                    <button type="button" @click="toggleNewCustomer()"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                        <span x-show="!showNewCustomer">+ Add new customer</span>
                        <span x-show="showNewCustomer" x-cloak>Choose existing</span>
                    </button>
                </div>

                {{-- Existing customer picker (searchable) --}}
                <div x-show="!showNewCustomer" class="relative" @click.outside="syncCustomerLabel()">
                    <input type="text"
                           x-model="customerSearch"
                           @focus="customerOpen = true"
                           @input="customerOpen = true"
                           @keydown.escape="syncCustomerLabel()"
                           placeholder="Walk-in — search name or phone..."
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm pr-8">
                    <button type="button" x-show="customerId" x-cloak @click="selectCustomer(null)"
                            class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600" title="Clear">&times;</button>

                    <div x-show="customerOpen" x-transition x-cloak
                         class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <button type="button" @click="selectCustomer(null)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm text-gray-500">Walk-in (no customer)</button>
                        <template x-for="c in filteredCustomers" :key="c.id">
                            <button type="button" @click="selectCustomer(c)"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm flex justify-between gap-2"
                                    :class="String(c.id) === String(customerId) ? 'bg-indigo-50' : ''">
                                <span class="truncate" x-text="c.name"></span>
                                <span class="text-gray-400 whitespace-nowrap" x-text="c.phone"></span>
                            </button>
                        </template>
                        <div x-show="filteredCustomers.length === 0" class="px-3 py-2 text-sm text-gray-400">No customers found.</div>
                    </div>
                </div>

                {{-- Inline new-customer capture --}}
                <div x-show="showNewCustomer" x-cloak class="space-y-2">
                    <input type="text" x-model.trim="newCustomer.name" placeholder="Customer name *"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <input type="tel" x-model.trim="newCustomer.phone" placeholder="Phone number *"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <textarea x-model.trim="newCustomer.address" rows="2" placeholder="Address (optional)"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    <p class="text-[11px] text-gray-400">Saved automatically when you complete the sale. Existing phone numbers are reused.</p>
                </div>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-gray-100 lg:min-h-[12rem]">
                <template x-for="(item, idx) in cart" :key="idx">
                    <div class="p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate" x-text="item.name"></div>
                                <div class="text-xs text-gray-500" x-text="formatMoney(item.unit_price) + ' × ' + item.quantity"></div>
                            </div>
                            <div class="text-sm font-semibold whitespace-nowrap" x-text="formatMoney(item.unit_price * item.quantity * (1 + item.tax_rate/100))"></div>
                            <button @click="removeItem(idx)" title="Remove item" aria-label="Remove item"
                                    class="shrink-0 h-7 w-7 flex items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <button @click="item.quantity = Math.max(1, item.quantity - 1)" class="px-2 py-0.5 bg-gray-100 rounded text-sm">−</button>
                            <input type="number" x-model.number="item.quantity" min="1" class="w-16 rounded-md border-gray-200 text-sm">
                            <button @click="item.quantity = item.quantity + 1" class="px-2 py-0.5 bg-gray-100 rounded text-sm">+</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="p-8 text-center text-gray-400 text-sm">Cart is empty.</div>
            </div>

            <div class="p-4 border-t border-gray-100 space-y-3 text-sm shrink-0">
                <div class="flex justify-between text-gray-600"><span>Subtotal</span><span x-text="formatMoney(subtotal)"></span></div>
                <div class="flex justify-between text-gray-600"><span>Tax</span><span x-text="formatMoney(taxTotal)"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Discount</span>
                    <input type="number" step="0.01" x-model.number="discount" min="0" class="w-24 rounded-md border-gray-200 text-sm text-right">
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-100 pt-2">
                    <span>Total</span><span x-text="formatMoney(total)"></span>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-2">
                    <template x-for="m in ['cash','upi','card']" :key="m">
                        <button @click="paymentMethod = m"
                                :class="paymentMethod === m ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="py-2 rounded-lg text-xs font-medium uppercase tracking-wide" x-text="m"></button>
                    </template>
                </div>

                <button @click="checkout()" :disabled="cart.length === 0 || submitting"
                        class="w-full py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Checkout · <span x-text="formatMoney(total)"></span></span>
                    <span x-show="submitting">Processing...</span>
                </button>
                <p x-show="errorMessage" x-text="errorMessage" class="text-red-600 text-xs"></p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function pos() {
                return {
                    query: '',
                    results: [],
                    cart: [],
                    customers: @json($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])),
                    customerId: '',
                    customerSearch: '',
                    customerOpen: false,
                    showNewCustomer: false,
                    newCustomer: { name: '', phone: '', address: '' },
                    discount: 0,
                    paymentMethod: 'cash',
                    submitting: false,
                    errorMessage: '',
                    currency: @json(setting('currency_symbol', '₹')),

                    init() {
                        this.search();
                    },

                    formatMoney(n) {
                        return this.currency + (Number(n) || 0).toFixed(2);
                    },

                    toggleNewCustomer() {
                        this.showNewCustomer = !this.showNewCustomer;
                        if (this.showNewCustomer) {
                            this.selectCustomer(null);
                        } else {
                            this.newCustomer = { name: '', phone: '', address: '' };
                        }
                        this.errorMessage = '';
                    },

                    get filteredCustomers() {
                        const q = this.customerSearch.trim().toLowerCase();
                        if (!q) return this.customers;
                        return this.customers.filter(c =>
                            c.name.toLowerCase().includes(q) || (c.phone || '').toLowerCase().includes(q)
                        );
                    },

                    selectCustomer(c) {
                        this.customerId = c ? String(c.id) : '';
                        this.customerSearch = c ? c.name : '';
                        this.customerOpen = false;
                    },

                    // On blur/escape, snap the text back to the chosen customer so a
                    // half-typed search never leaves a misleading label.
                    syncCustomerLabel() {
                        const sel = this.customers.find(c => String(c.id) === String(this.customerId));
                        this.customerSearch = sel ? sel.name : '';
                        this.customerOpen = false;
                    },

                    async search() {
                        const url = '{{ route('pos.search') }}?q=' + encodeURIComponent(this.query);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        this.results = await res.json();
                    },

                    async quickAddByBarcode() {
                        if (!this.query) return;
                        await this.search();
                        const match = this.results.find(p => p.barcode === this.query);
                        if (match) {
                            this.addItem(match, null);
                            this.query = '';
                            this.results = [];
                            this.$refs.searchInput.focus();
                            return;
                        }
                        for (const p of this.results) {
                            const v = (p.variants || []).find(v => v.barcode === this.query);
                            if (v) {
                                this.addItem(p, v);
                                this.query = '';
                                this.results = [];
                                this.$refs.searchInput.focus();
                                return;
                            }
                        }
                    },

                    addItem(product, variant) {
                        const key = variant ? product.id + '-' + variant.id : product.id + '-';
                        const existing = this.cart.find(i => i.key === key);
                        if (existing) {
                            existing.quantity += 1;
                            return;
                        }
                        this.cart.push({
                            key,
                            product_id: product.id,
                            variant_id: variant ? variant.id : null,
                            name: product.name + (variant ? ' — ' + variant.name : ''),
                            quantity: 1,
                            unit_price: variant ? variant.price : product.price,
                            tax_rate: product.tax_rate || 0,
                        });
                    },

                    removeItem(idx) {
                        this.cart.splice(idx, 1);
                    },

                    get subtotal() {
                        return this.cart.reduce((s, i) => s + i.unit_price * i.quantity, 0);
                    },

                    get taxTotal() {
                        return this.cart.reduce((s, i) => s + i.unit_price * i.quantity * (i.tax_rate / 100), 0);
                    },

                    get total() {
                        return Math.max(0, this.subtotal + this.taxTotal - (Number(this.discount) || 0));
                    },

                    async checkout() {
                        if (this.cart.length === 0) return;

                        let newCustomer = null;
                        if (this.showNewCustomer) {
                            if (!this.newCustomer.name || !this.newCustomer.phone) {
                                this.errorMessage = 'Enter the customer name and phone, or switch to "Choose existing".';
                                return;
                            }
                            newCustomer = { ...this.newCustomer };
                        }

                        this.submitting = true;
                        this.errorMessage = '';
                        const res = await fetch('{{ route('pos.checkout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                customer_id: this.customerId || null,
                                new_customer: newCustomer,
                                items: this.cart.map(i => ({
                                    product_id: i.product_id,
                                    variant_id: i.variant_id,
                                    quantity: i.quantity,
                                    unit_price: i.unit_price,
                                    tax_rate: i.tax_rate,
                                })),
                                discount_amount: Number(this.discount) || 0,
                                payment_method: this.paymentMethod,
                                paid_amount: this.total,
                            }),
                        });
                        const data = await res.json();
                        this.submitting = false;
                        if (data.success) {
                            window.location.href = data.invoice_url;
                        } else {
                            this.errorMessage = data.error || 'Checkout failed.';
                        }
                    },
                }
            }
        </script>
    @endpush
</x-app-layout>

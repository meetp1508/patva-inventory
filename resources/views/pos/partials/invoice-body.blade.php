{{-- Shared invoice body, used by on-screen receipt and PDF view. --}}
<div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-200">
    <div>
        @if (setting('company_logo'))
            <img src="{{ Storage::url(setting('company_logo')) }}" alt="" style="max-height: 60px;" class="mb-2">
        @endif
        <h2 class="text-xl font-bold text-gray-900">{{ setting('company_name') }}</h2>
        @if (setting('company_address'))<p class="text-xs text-gray-600 whitespace-pre-line">{{ setting('company_address') }}</p>@endif
        @if (setting('company_phone'))<p class="text-xs text-gray-600">Phone: {{ setting('company_phone') }}</p>@endif
        @if (setting('company_email'))<p class="text-xs text-gray-600">Email: {{ setting('company_email') }}</p>@endif
    </div>
    <div class="text-right">
        <h3 class="text-2xl font-semibold text-indigo-600 mb-1">INVOICE</h3>
        <p class="text-sm font-mono">{{ $invoice->invoice_number }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $invoice->created_at->format('d M Y, H:i') }}</p>
        <p class="text-xs text-gray-500">Status: <span class="font-semibold capitalize">{{ $invoice->status }}</span></p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6 mb-6 text-sm">
    <div>
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Billed To</p>
        @if ($invoice->customer)
            <p class="font-medium">{{ $invoice->customer->name }}</p>
            <p class="text-xs text-gray-600">{{ $invoice->customer->phone }}</p>
            @if ($invoice->customer->address)<p class="text-xs text-gray-600 whitespace-pre-line">{{ $invoice->customer->address }}</p>@endif
        @else
            <p class="text-gray-500">Walk-in customer</p>
        @endif
    </div>
    <div class="text-right">
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Cashier</p>
        <p>{{ $invoice->user?->name ?? '—' }}</p>
        @if ($invoice->payments->first())
            <p class="text-xs text-gray-500 mt-1">Paid via <span class="font-medium uppercase">{{ $invoice->payments->first()->payment_method }}</span></p>
        @endif
    </div>
</div>

<table class="w-full text-sm mb-6">
    <thead>
        <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
            <th class="py-2 text-left">Item</th>
            <th class="py-2 text-right">Qty</th>
            <th class="py-2 text-right">Price</th>
            <th class="py-2 text-right">Total</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach ($invoice->items as $item)
            <tr>
                <td class="py-2">{{ $item->product_name }}</td>
                <td class="py-2 text-right">{{ $item->quantity }}</td>
                <td class="py-2 text-right">{{ money($item->unit_price) }}</td>
                <td class="py-2 text-right">{{ money($item->subtotal) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="flex justify-end">
    <div class="w-full max-w-xs text-sm space-y-1">
        <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>{{ money($invoice->subtotal) }}</span></div>
        <div class="flex justify-between text-gray-600"><span>Tax</span><span>{{ money($invoice->tax_amount) }}</span></div>
        <div class="flex justify-between text-gray-600"><span>Discount</span><span>− {{ money($invoice->discount_amount) }}</span></div>
        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2"><span>Total</span><span>{{ money($invoice->total_amount) }}</span></div>
    </div>
</div>

@if (setting('invoice_footer'))
    <div class="mt-8 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
        {{ setting('invoice_footer') }}
    </div>
@endif

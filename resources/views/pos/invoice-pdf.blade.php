<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; box-sizing: border-box; }
        body { font-size: 12px; color: #1f2937; margin: 20px; }
        .header { display: table; width: 100%; padding-bottom: 12px; border-bottom: 2px solid #4f46e5; margin-bottom: 16px; }
        .header > div { display: table-cell; vertical-align: top; }
        .header .right { text-align: right; }
        .title { color: #4f46e5; font-size: 24px; font-weight: bold; }
        .meta { margin-bottom: 16px; display: table; width: 100%; }
        .meta > div { display: table-cell; width: 50%; vertical-align: top; }
        .meta .right { text-align: right; }
        .label { color: #6b7280; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background: #f3f4f6; color: #6b7280; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items .num { text-align: right; }
        .totals { width: 260px; margin-left: auto; }
        .totals .row { display: table; width: 100%; padding: 4px 0; }
        .totals .row > div { display: table-cell; }
        .totals .row .label { text-align: left; color: #4b5563; text-transform: none; letter-spacing: 0; font-size: 12px; }
        .totals .row .value { text-align: right; }
        .totals .grand { border-top: 1px solid #d1d5db; padding-top: 8px; margin-top: 8px; font-size: 16px; font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div style="font-size: 16px; font-weight: bold;">{{ setting('company_name') }}</div>
            @if (setting('company_address'))<div style="font-size: 11px; color: #4b5563;">{{ setting('company_address') }}</div>@endif
            @if (setting('company_phone'))<div style="font-size: 11px; color: #4b5563;">Phone: {{ setting('company_phone') }}</div>@endif
            @if (setting('company_email'))<div style="font-size: 11px; color: #4b5563;">Email: {{ setting('company_email') }}</div>@endif
        </div>
        <div class="right">
            <div class="title">INVOICE</div>
            <div style="font-family: monospace;">{{ $invoice->invoice_number }}</div>
            <div style="color: #6b7280; font-size: 11px;">{{ $invoice->created_at->format('d M Y, H:i') }}</div>
            <div style="color: #6b7280; font-size: 11px;">Status: <b style="text-transform: capitalize;">{{ $invoice->status }}</b></div>
        </div>
    </div>

    <div class="meta">
        <div>
            <div class="label">Billed To</div>
            @if ($invoice->customer)
                <div style="font-weight: bold;">{{ $invoice->customer->name }}</div>
                <div style="color: #4b5563;">{{ $invoice->customer->phone }}</div>
                @if ($invoice->customer->address)<div style="color: #4b5563;">{{ $invoice->customer->address }}</div>@endif
            @else
                <div style="color: #6b7280;">Walk-in customer</div>
            @endif
        </div>
        <div class="right">
            <div class="label">Cashier</div>
            <div>{{ $invoice->user?->name ?? '—' }}</div>
            @if ($invoice->payments->first())
                <div class="label" style="margin-top: 6px;">Payment Method</div>
                <div style="text-transform: uppercase;">{{ $invoice->payments->first()->payment_method }}</div>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr><th>Item</th><th class="num">Qty</th><th class="num">Price</th><th class="num">Total</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ money($item->unit_price) }}</td>
                    <td class="num">{{ money($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><div class="label">Subtotal</div><div class="value">{{ money($invoice->subtotal) }}</div></div>
        <div class="row"><div class="label">Tax</div><div class="value">{{ money($invoice->tax_amount) }}</div></div>
        <div class="row"><div class="label">Discount</div><div class="value">− {{ money($invoice->discount_amount) }}</div></div>
        <div class="row grand"><div class="label">Total</div><div class="value">{{ money($invoice->total_amount) }}</div></div>
    </div>

    @if (setting('invoice_footer'))
        <div class="footer">{{ setting('invoice_footer') }}</div>
    @endif
</body>
</html>

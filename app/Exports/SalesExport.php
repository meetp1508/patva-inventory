<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly string $from, private readonly string $to)
    {
    }

    public function query()
    {
        return Invoice::query()
            ->with('customer')
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->orderBy('created_at');
    }

    public function headings(): array
    {
        return ['Invoice #', 'Date', 'Customer', 'Subtotal', 'Tax', 'Discount', 'Total', 'Status'];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->created_at->format('Y-m-d H:i'),
            $invoice->customer?->name ?? 'Walk-in',
            (float) $invoice->subtotal,
            (float) $invoice->tax_amount,
            (float) $invoice->discount_amount,
            (float) $invoice->total_amount,
            $invoice->status,
        ];
    }
}

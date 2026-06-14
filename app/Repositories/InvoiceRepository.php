<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function search(array $filters = [], int $perPage = 20)
    {
        return Invoice::with(['customer', 'user'])
            ->when(!empty($filters['q']), fn ($q) => $q->where('invoice_number', 'like', "%{$filters['q']}%"))
            ->when(!empty($filters['customer_id']), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when(!empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}

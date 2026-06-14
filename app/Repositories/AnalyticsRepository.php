<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function salesByDay(string $from, string $to): array
    {
        return Invoice::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as orders'))
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($r) => ['day' => $r->day, 'total' => (float) $r->total, 'orders' => (int) $r->orders])
            ->all();
    }

    public function salesByMonth(int $year): array
    {
        $rows = Invoice::query()
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(fn ($m) => [
            'month' => $m,
            'total' => round((float) ($rows[$m] ?? 0), 2),
        ])->all();
    }

    public function topProducts(string $from, string $to, int $limit = 10)
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->select(
                'invoice_items.product_id',
                'invoice_items.product_name',
                DB::raw('SUM(invoice_items.quantity) as total_qty'),
                DB::raw('SUM(invoice_items.subtotal) as total_revenue'),
            )
            ->whereBetween('invoices.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('invoice_items.product_id', 'invoice_items.product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public function topCustomers(string $from, string $to, int $limit = 10)
    {
        return Customer::query()
            ->join('invoices', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('SUM(invoices.total_amount) as total_spent'),
            )
            ->whereBetween('invoices.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    public function profit(string $from, string $to): array
    {
        $rows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->select(
                DB::raw('SUM(invoice_items.subtotal) as revenue'),
                DB::raw('SUM(invoice_items.quantity * products.purchase_price) as cost'),
            )
            ->whereBetween('invoices.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->first();

        $revenue = (float) ($rows->revenue ?? 0);
        $cost = (float) ($rows->cost ?? 0);

        return [
            'revenue' => $revenue,
            'cost'    => $cost,
            'profit'  => $revenue - $cost,
            'margin'  => $revenue > 0 ? round((($revenue - $cost) / $revenue) * 100, 2) : 0,
        ];
    }

    public function inventoryValuation()
    {
        return Product::query()
            ->select(
                'id',
                'name',
                'sku',
                'stock_quantity',
                'purchase_price',
                'selling_price',
                DB::raw('(stock_quantity * purchase_price) as stock_cost'),
                DB::raw('(stock_quantity * selling_price) as stock_value'),
            )
            ->orderByDesc('stock_value')
            ->get();
    }
}

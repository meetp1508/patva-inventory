<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function stats(): array
    {
        $today = Carbon::today();

        return [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'low_stock_count' => Product::lowStock()->count(),
            'today_sales' => (float) Invoice::whereDate('created_at', $today)->sum('total_amount'),
            'month_sales' => (float) Invoice::whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->sum('total_amount'),
            'total_sales' => (float) Invoice::sum('total_amount'),
            'invoice_count' => Invoice::count(),
        ];
    }

    public function recentInvoices(int $limit = 5)
    {
        return Invoice::with('customer')->latest()->limit($limit)->get();
    }

    public function topProducts(int $limit = 5)
    {
        return InvoiceItem::query()
            // Only count items whose parent invoice is still live (not voided).
            ->whereHas('invoice')
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public function salesTrend(int $days = 14): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = Invoice::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total_amount) as total'))
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('total', 'day');

        // Fill gaps so the chart has a continuous series.
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $series[$date] = round((float) ($rows[$date] ?? 0), 2);
        }

        return $series;
    }

    public function lowStockProducts(int $limit = 5)
    {
        return Product::lowStock()->orderBy('stock_quantity')->limit($limit)->get();
    }
}

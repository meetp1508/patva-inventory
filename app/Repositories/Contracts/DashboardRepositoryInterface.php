<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    /** Headline KPI numbers for the dashboard cards. */
    public function stats(): array;

    /** Most recent invoices with customer eager-loaded. */
    public function recentInvoices(int $limit = 5);

    /** Best-selling products by quantity sold. */
    public function topProducts(int $limit = 5);

    /** Daily sales totals for the last N days, keyed by date. */
    public function salesTrend(int $days = 14): array;

    /** Products at or below their low-stock threshold. */
    public function lowStockProducts(int $limit = 5);
}

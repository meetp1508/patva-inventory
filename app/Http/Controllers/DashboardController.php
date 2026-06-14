<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard)
    {
    }

    public function index()
    {
        $stats = $this->dashboard->stats();
        $recentInvoices = $this->dashboard->recentInvoices();
        $topProducts = $this->dashboard->topProducts();
        $lowStock = $this->dashboard->lowStockProducts();
        $salesTrend = $this->dashboard->salesTrend(14);

        return view('dashboard', compact('stats', 'recentInvoices', 'topProducts', 'lowStock', 'salesTrend'));
    }
}

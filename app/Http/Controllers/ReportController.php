<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct(private readonly AnalyticsRepositoryInterface $analytics)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('analytics access'), 403);

        $from = $request->input('from', Carbon::today()->subDays(29)->toDateString());
        $to   = $request->input('to', Carbon::today()->toDateString());

        $salesByDay   = $this->analytics->salesByDay($from, $to);
        $topProducts  = $this->analytics->topProducts($from, $to);
        $topCustomers = $this->analytics->topCustomers($from, $to);
        $profit       = $this->analytics->profit($from, $to);
        $valuation    = $this->analytics->inventoryValuation();

        return view('reports.index', compact('from', 'to', 'salesByDay', 'topProducts', 'topCustomers', 'profit', 'valuation'));
    }
}

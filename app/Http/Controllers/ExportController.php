<?php

namespace App\Http\Controllers;

use App\Exports\InventoryExport;
use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function sales(Request $request)
    {
        abort_unless($request->user()->can('analytics access'), 403);

        $from = $request->input('from', Carbon::today()->subDays(29)->toDateString());
        $to   = $request->input('to', Carbon::today()->toDateString());
        $format = $request->input('format', 'xlsx');

        [$writer, $ext] = $this->formatToWriter($format);

        return Excel::download(new SalesExport($from, $to), "sales-{$from}-to-{$to}.{$ext}", $writer);
    }

    public function products(Request $request)
    {
        abort_unless($request->user()->can('manage products'), 403);

        [$writer, $ext] = $this->formatToWriter($request->input('format', 'xlsx'));

        return Excel::download(new ProductsExport(), "products.{$ext}", $writer);
    }

    public function inventory(Request $request)
    {
        abort_unless($request->user()->can('manage inventory'), 403);

        [$writer, $ext] = $this->formatToWriter($request->input('format', 'xlsx'));

        return Excel::download(new InventoryExport(), "inventory.{$ext}", $writer);
    }

    /** @return array{0:string,1:string} */
    private function formatToWriter(string $format): array
    {
        return match (strtolower($format)) {
            'csv' => [ExcelFormat::CSV, 'csv'],
            default => [ExcelFormat::XLSX, 'xlsx'],
        };
    }
}

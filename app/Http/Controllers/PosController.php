<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\SalesService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(private readonly SalesService $sales)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('billing access'), 403);

        $customers = Customer::orderBy('name')->get();

        return view('pos.index', compact('customers'));
    }

    /** AJAX product search by name / SKU / barcode (incl. variants). */
    public function search(Request $request)
    {
        $term = (string) $request->input('q', '');

        $products = Product::with('variants')
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$term}%")->orWhere('barcode', 'like', "%{$term}%"));
            })
            ->limit(15)
            ->get()
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'sku'       => $p->sku,
                'barcode'   => $p->barcode,
                'price'     => (float) $p->selling_price,
                'tax_rate'  => (float) $p->tax_rate,
                'stock'     => $p->stock_quantity,
                'type'      => $p->type,
                'variants'  => $p->variants->map(fn ($v) => [
                    'id'      => $v->id,
                    'name'    => $v->variant_name,
                    'sku'     => $v->sku,
                    'barcode' => $v->barcode,
                    'price'   => (float) $p->selling_price + (float) $v->additional_price,
                    'stock'   => $v->stock_quantity,
                ])->values(),
            ]);

        return response()->json($products);
    }

    public function checkout(StoreCheckoutRequest $request)
    {
        try {
            $invoice = $this->sales->checkout($request->validated());
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'invoice_id' => $invoice->id,
            'invoice_url' => route('pos.invoice', $invoice),
        ]);
    }

    /** On-screen receipt for a completed sale. */
    public function invoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('items.product', 'customer', 'user', 'payments');

        return view('pos.invoice', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('items.product', 'customer', 'user', 'payments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.invoice-pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}

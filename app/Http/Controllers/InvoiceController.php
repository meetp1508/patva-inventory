<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
        private readonly InventoryService $inventory,
    ) {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('billing access'), 403);

        $invoices = $this->repository->search($request->only(['q', 'customer_id', 'status', 'from', 'to']), 20);
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('items.product', 'customer', 'user', 'payments');

        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        DB::transaction(function () use ($invoice) {
            // Return each sold line back to stock (logged) before voiding,
            // so deleting an invoice never silently loses inventory.
            $invoice->load('items.product', 'items.variant');

            foreach ($invoice->items as $item) {
                if (! $item->product) {
                    continue;
                }

                $this->inventory->adjust(
                    $item->product,
                    (int) $item->quantity,
                    InventoryService::ACTION_RETURN,
                    "Invoice {$invoice->invoice_number} voided",
                    $item->variant,
                );
            }

            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted and stock restored.');
    }
}

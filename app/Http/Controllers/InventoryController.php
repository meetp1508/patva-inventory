<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly InventoryRepositoryInterface $repository,
    ) {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('manage inventory'), 403);

        $logs = $this->repository->history(
            $request->only(['product_id', 'action', 'from', 'to']),
            25,
        );
        $lowStock = $this->repository->lowStock(10);

        return view('inventory.index', compact('logs', 'lowStock'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('manage inventory'), 403);

        $products = Product::with('variants')->orderBy('name')->get();
        $preselect = $request->input('product_id');

        return view('inventory.adjust', compact('products', 'preselect'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $variant = $request->product_variant_id ? ProductVariant::findOrFail($request->product_variant_id) : null;

        try {
            $this->inventory->adjust(
                $product,
                (int) $request->quantity,
                $request->action_type,
                $request->remarks,
                $variant,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('inventory.index')->with('success', 'Stock adjusted.');
    }
}

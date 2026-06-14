<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __construct(private readonly BarcodeService $barcodes)
    {
    }

    /** Printable label sheet for a product (and optionally its variants). */
    public function product(Product $product, Request $request)
    {
        $this->authorize('view', $product);

        $copies = (int) $request->input('copies', 12);

        if (empty($product->barcode)) {
            $this->barcodes->assignToProduct($product);
            $product->refresh();
        }

        $labels = collect(range(1, max(1, $copies)))->map(fn () => [
            'name'    => $product->name,
            'code'    => $product->barcode,
            'price'   => money($product->selling_price),
            'png'     => $this->barcodes->pngDataUri($product->barcode),
        ])->all();

        return view('barcode.label', compact('labels'));
    }

    public function variant(Product $product, ProductVariant $variant, Request $request)
    {
        $this->authorize('view', $product);
        abort_unless($variant->product_id === $product->id, 404);

        if (empty($variant->barcode)) {
            $this->barcodes->assignToVariant($variant);
            $variant->refresh();
        }

        $copies = (int) $request->input('copies', 12);
        $price = (float) $product->selling_price + (float) $variant->additional_price;

        $labels = collect(range(1, max(1, $copies)))->map(fn () => [
            'name'  => $product->name . ' — ' . $variant->variant_name,
            'code'  => $variant->barcode,
            'price' => money($price),
            'png'   => $this->barcodes->pngDataUri($variant->barcode),
        ])->all();

        return view('barcode.label', compact('labels'));
    }

    public function download(Product $product)
    {
        $this->authorize('view', $product);

        if (empty($product->barcode)) {
            $this->barcodes->assignToProduct($product);
            $product->refresh();
        }

        return response($this->barcodes->png($product->barcode, 3, 80), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $product->sku . '-barcode.png"',
        ]);
    }

    public function regenerate(Product $product)
    {
        $this->authorize('update', $product);

        $this->barcodes->assignToProduct($product);

        return back()->with('success', 'Barcode regenerated.');
    }
}

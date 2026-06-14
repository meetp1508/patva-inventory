<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\VariantService;

class ProductVariantController extends Controller
{
    public function __construct(private readonly VariantService $variants)
    {
    }

    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $this->variants->create($product, $request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Variant added.');
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant)
    {
        $this->authorize('update', $product);
        abort_unless($variant->product_id === $product->id, 404);

        $this->variants->update($variant, $request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Variant updated.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $this->authorize('update', $product);
        abort_unless($variant->product_id === $product->id, 404);

        $this->variants->delete($variant);

        return redirect()->route('products.show', $product)->with('success', 'Variant deleted.');
    }
}

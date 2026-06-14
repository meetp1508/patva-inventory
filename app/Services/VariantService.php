<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class VariantService
{
    public function __construct(private readonly BarcodeService $barcodes)
    {
    }

    /**
     * Create a variant and ensure it has a unique barcode. The parent product's
     * total stock is re-derived from the sum of variant stock.
     *
     * @param array<string, mixed> $data
     */
    public function create(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data) {
            $variant = $product->variants()->create($data);

            if (empty($variant->barcode)) {
                $this->barcodes->assignToVariant($variant);
            }

            // A product that has variants is, by definition, a variant product.
            if ($product->type !== 'variant') {
                $product->forceFill(['type' => 'variant'])->save();
            }

            $this->syncProductStock($product);

            return $variant;
        });
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data) {
            $variant->update($data);
            $this->syncProductStock($variant->product);
            return $variant;
        });
    }

    /**
     * Reconcile a product's full variant set from the product form payload.
     * Rows with an id are updated, rows without are created (with an auto
     * barcode), and any existing variant absent from the payload is removed.
     * The product's type and stock are kept consistent.
     *
     * @param array<int, array{id?:int|null, name:string, sku:string, additional_price?:float, stock_quantity?:int, value_ids?:array<int>}> $variants
     */
    public function sync(Product $product, array $variants): void
    {
        DB::transaction(function () use ($product, $variants) {
            $keepIds = [];

            foreach ($variants as $row) {
                $attributes = [
                    'variant_name'     => $row['name'],
                    'sku'              => $row['sku'],
                    'additional_price' => $row['additional_price'] ?? 0,
                    'stock_quantity'   => $row['stock_quantity'] ?? 0,
                ];

                $variant = ! empty($row['id']) ? $product->variants()->find($row['id']) : null;

                if ($variant) {
                    $variant->update($attributes);
                } else {
                    $variant = $product->variants()->create($attributes);
                    $this->barcodes->assignToVariant($variant);
                }

                $variant->values()->sync($row['value_ids'] ?? []);
                $keepIds[] = $variant->id;
            }

            // Remove variants the user dropped.
            $product->variants()->whereNotIn('id', $keepIds)->get()->each->delete();

            $product->forceFill(['type' => count($keepIds) > 0 ? 'variant' : 'simple'])->save();
            $this->syncProductStock($product);
        });
    }

    public function delete(ProductVariant $variant): void
    {
        DB::transaction(function () use ($variant) {
            $product = $variant->product;
            $variant->delete();

            // Removing the last variant turns the product back into a simple one,
            // keeping its current stock instead of resetting it to zero.
            if ($product->variants()->count() === 0) {
                $product->forceFill(['type' => 'simple'])->save();
            } else {
                $this->syncProductStock($product);
            }
        });
    }

    /** Roll up variant stock into the parent product's stock_quantity. */
    public function syncProductStock(Product $product): void
    {
        if ($product->variants()->count() === 0) {
            return;
        }

        $total = (int) $product->variants()->sum('stock_quantity');
        $product->forceFill(['stock_quantity' => $total])->save();
    }
}

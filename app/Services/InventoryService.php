<?php

namespace App\Services;

use App\Events\StockUpdated;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single source of truth for all stock changes. Always writes an
 * InventoryLog (before/after balance) inside a transaction so the
 * audit trail stays consistent with the on-hand quantity.
 */
class InventoryService
{
    public const ACTION_PURCHASE = 'purchase';
    public const ACTION_SALE = 'sale';
    public const ACTION_ADJUSTMENT = 'adjustment';
    public const ACTION_RETURN = 'return';

    /**
     * Apply a stock delta. Positive increases stock, negative decreases it.
     * Pass a variant to track per-variant stock; the parent product's total
     * is recalculated from the variants.
     */
    public function adjust(
        Product $product,
        int $delta,
        string $action,
        ?string $remarks = null,
        ?ProductVariant $variant = null,
    ): InventoryLog {
        return DB::transaction(function () use ($product, $delta, $action, $remarks, $variant) {
            if ($variant) {
                $before = (int) $variant->stock_quantity;
                $after = $before + $delta;

                if ($after < 0) {
                    throw new RuntimeException("Insufficient stock for variant {$variant->variant_name}.");
                }

                $variant->forceFill(['stock_quantity' => $after])->save();

                // Roll the parent product's total up to the sum of variants.
                $product->forceFill([
                    'stock_quantity' => (int) $product->variants()->sum('stock_quantity'),
                ])->save();
            } else {
                $before = (int) $product->stock_quantity;
                $after = $before + $delta;

                if ($after < 0) {
                    throw new RuntimeException("Insufficient stock for {$product->name}.");
                }

                $product->forceFill(['stock_quantity' => $after])->save();
            }

            $log = InventoryLog::create([
                'product_id'         => $product->id,
                'product_variant_id' => $variant?->id,
                'user_id'            => Auth::id(),
                'action_type'        => $action,
                'quantity'           => $delta,
                'balance_before'     => $before,
                'balance_after'      => $after,
                'remarks'            => $remarks,
            ]);

            event(new StockUpdated($product, $delta, $action));

            return $log;
        });
    }
}

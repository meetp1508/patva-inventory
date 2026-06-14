<?php

namespace App\Services;

use App\Models\BarcodeLog;
use App\Models\Product;
use App\Models\ProductVariant;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

/**
 * Generates and renders unique, scanner-friendly Code-128 barcodes for
 * products and variants, logging every generation to barcode_logs.
 */
class BarcodeService
{
    /** Generate a globally-unique numeric barcode value. */
    public function generateUniqueCode(): string
    {
        do {
            // 12-digit numeric code: time-based prefix + random suffix.
            $code = substr((string) now()->timestamp, -7) . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (
            Product::where('barcode', $code)->exists()
            || ProductVariant::where('barcode', $code)->exists()
        );

        return $code;
    }

    /** Assign a freshly generated barcode to a product (and log it). */
    public function assignToProduct(Product $product): string
    {
        $code = $this->generateUniqueCode();
        $product->forceFill(['barcode' => $code])->save();

        BarcodeLog::create([
            'product_id' => $product->id,
            'barcode_generated' => $code,
        ]);

        return $code;
    }

    /** Assign a freshly generated barcode to a variant (and log it). */
    public function assignToVariant(ProductVariant $variant): string
    {
        $code = $this->generateUniqueCode();
        $variant->forceFill(['barcode' => $code])->save();

        BarcodeLog::create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'barcode_generated' => $code,
        ]);

        return $code;
    }

    public function png(string $code, int $widthFactor = 2, int $height = 50): string
    {
        return (new BarcodeGeneratorPNG())->getBarcode($code, BarcodeGeneratorPNG::TYPE_CODE_128, $widthFactor, $height);
    }

    public function pngDataUri(string $code, int $widthFactor = 2, int $height = 50): string
    {
        return 'data:image/png;base64,' . base64_encode($this->png($code, $widthFactor, $height));
    }

    public function svg(string $code, int $widthFactor = 2, int $height = 50): string
    {
        return (new BarcodeGeneratorSVG())->getBarcode($code, BarcodeGeneratorSVG::TYPE_CODE_128, $widthFactor, $height);
    }
}

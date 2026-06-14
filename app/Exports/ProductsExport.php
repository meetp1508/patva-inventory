<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Product::query()->with('category')->orderBy('name');
    }

    public function headings(): array
    {
        return ['Name', 'SKU', 'Barcode', 'Category', 'Purchase Price', 'Selling Price', 'Tax %', 'Stock', 'Low-Stock Threshold', 'Active'];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->barcode,
            $product->category?->name,
            (float) $product->purchase_price,
            (float) $product->selling_price,
            (float) $product->tax_rate,
            $product->stock_quantity,
            $product->low_stock_threshold,
            $product->is_active ? 'Yes' : 'No',
        ];
    }
}

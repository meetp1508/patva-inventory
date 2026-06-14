<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Product::query()->orderBy('name');
    }

    public function headings(): array
    {
        return ['Name', 'SKU', 'Stock', 'Purchase Price', 'Selling Price', 'Stock Cost', 'Stock Value'];
    }

    public function map($product): array
    {
        $cost = (float) $product->stock_quantity * (float) $product->purchase_price;
        $value = (float) $product->stock_quantity * (float) $product->selling_price;

        return [
            $product->name,
            $product->sku,
            $product->stock_quantity,
            (float) $product->purchase_price,
            (float) $product->selling_price,
            round($cost, 2),
            round($value, 2),
        ];
    }
}

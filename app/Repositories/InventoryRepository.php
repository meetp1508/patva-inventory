<?php

namespace App\Repositories;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function history(array $filters = [], int $perPage = 25)
    {
        return InventoryLog::with(['product', 'variant', 'user'])
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(!empty($filters['action']), fn ($q) => $q->where('action_type', $filters['action']))
            ->when(!empty($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function lowStock(int $perPage = 25)
    {
        return Product::with('category')->lowStock()->orderBy('stock_quantity')->paginate($perPage);
    }
}

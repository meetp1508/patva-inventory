<?php

namespace App\Repositories\Contracts;

interface InventoryRepositoryInterface
{
    /** Paginated inventory log history, optionally scoped to a product or action type. */
    public function history(array $filters = [], int $perPage = 25);

    /** Products at or below their low-stock threshold. */
    public function lowStock(int $perPage = 25);
}

<?php

namespace App\Repositories\Contracts;

interface InvoiceRepositoryInterface
{
    /** Paginated invoice list with optional filters. */
    public function search(array $filters = [], int $perPage = 20);
}

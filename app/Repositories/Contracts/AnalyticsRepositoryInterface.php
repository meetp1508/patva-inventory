<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    public function salesByDay(string $from, string $to): array;

    public function salesByMonth(int $year): array;

    public function topProducts(string $from, string $to, int $limit = 10);

    public function topCustomers(string $from, string $to, int $limit = 10);

    public function profit(string $from, string $to): array;

    public function inventoryValuation();
}

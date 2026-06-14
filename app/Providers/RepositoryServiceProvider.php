<?php

namespace App\Providers;

use App\Repositories\AnalyticsRepository;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\DashboardRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bind repository contracts to their Eloquent implementations.
     */
    public array $bindings = [
        DashboardRepositoryInterface::class => DashboardRepository::class,
        InventoryRepositoryInterface::class => InventoryRepository::class,
        InvoiceRepositoryInterface::class   => InvoiceRepository::class,
        AnalyticsRepositoryInterface::class => AnalyticsRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}

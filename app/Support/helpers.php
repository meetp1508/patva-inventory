<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Read an application setting (cached). Pass no key to get the service.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount using the configured currency symbol.
     */
    function money(int|float|string|null $amount): string
    {
        $symbol = (string) setting('currency_symbol', '₹');

        return $symbol . number_format((float) $amount, 2);
    }
}

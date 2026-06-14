<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(SettingService::class);

        // Only seed keys that are not already persisted, then flush cache.
        $service->set(SettingService::DEFAULTS);
    }
}

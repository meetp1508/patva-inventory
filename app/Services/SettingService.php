<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Central read/write access to application settings, backed by a forever cache
 * that is flushed whenever a setting changes. Resolve via the container as a
 * singleton, or use the global setting() helper.
 */
class SettingService
{
    private const CACHE_KEY = 'app.settings';

    /**
     * Sensible defaults used when a setting has not been persisted yet.
     */
    public const DEFAULTS = [
        'company_name'       => 'My Company',
        'company_email'      => '',
        'company_phone'      => '',
        'company_address'    => '',
        'company_logo'       => '',
        'currency_symbol'    => '₹',
        'currency_code'      => 'INR',
        'default_tax_rate'   => '0',
        'invoice_prefix'     => 'INV-',
        'invoice_next_number'=> '1',
        'invoice_footer'     => 'Thank you for your business!',
        'whatsapp_driver'    => 'log',
        'whatsapp_phone_id'  => '',
        'whatsapp_token'     => '',
    ];

    /**
     * All settings as a flat key => value array (cached forever).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        if ($value !== null && $value !== '') {
            return $value;
        }

        return $default ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Persist one or many settings, then flush the cache.
     *
     * @param array<string, mixed> $values
     */
    public function set(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? (string) (int) $value : $value, 'group' => $group],
            );
        }

        $this->flush();
    }

    /**
     * Reserve and return the next sequential invoice number, e.g. "INV-000042".
     * Increments the stored counter so each invoice is unique.
     */
    public function nextInvoiceNumber(): string
    {
        $prefix = (string) $this->get('invoice_prefix', 'INV-');

        // Reserve the counter atomically: lock the row so two concurrent
        // checkouts can't read the same number and collide on the unique
        // invoice_number index.
        $next = DB::transaction(function () {
            $row = Setting::where('key', 'invoice_next_number')->lockForUpdate()->first();
            $current = (int) ($row->value ?? self::DEFAULTS['invoice_next_number']);

            Setting::updateOrCreate(
                ['key' => 'invoice_next_number'],
                ['value' => (string) ($current + 1), 'group' => 'invoice'],
            );

            return $current;
        });

        $this->flush();

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

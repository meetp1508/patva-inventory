<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() > 0) {
            return; // already populated
        }

        Category::factory(8)->create();
        $products = Product::factory(40)->create();
        $customers = Customer::factory(15)->create();
        $user = User::query()->first();

        // Synthesize 60 days of sales so the dashboard and reports look real.
        for ($d = 60; $d >= 0; $d--) {
            $invoiceCount = random_int(0, 5);
            for ($i = 0; $i < $invoiceCount; $i++) {
                $when = Carbon::today()->subDays($d)->setTime(random_int(9, 20), random_int(0, 59));
                $itemCount = random_int(1, 4);
                $lineItems = [];
                $subtotal = 0.0;
                $taxTotal = 0.0;

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products->random();
                    $qty = random_int(1, 3);
                    $lineSub = (float) $product->selling_price * $qty;
                    $lineTax = round($lineSub * ((float) $product->tax_rate / 100), 2);
                    $subtotal += $lineSub;
                    $taxTotal += $lineTax;

                    $lineItems[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'quantity'     => $qty,
                        'unit_price'   => $product->selling_price,
                        'subtotal'     => $lineSub + $lineTax,
                    ];
                }

                $discount = random_int(0, 1) ? random_int(0, 30) : 0;
                $total = max(0, $subtotal + $taxTotal - $discount);

                $invoice = Invoice::create([
                    'invoice_number'  => 'INV-' . str_pad((string) (Invoice::count() + 1), 6, '0', STR_PAD_LEFT),
                    'customer_id'     => random_int(0, 1) ? $customers->random()->id : null,
                    'user_id'         => $user->id,
                    'subtotal'        => $subtotal,
                    'tax_amount'      => $taxTotal,
                    'discount_amount' => $discount,
                    'total_amount'    => $total,
                    'status'          => 'paid',
                    'created_at'      => $when,
                    'updated_at'      => $when,
                ]);

                foreach ($lineItems as $item) {
                    InvoiceItem::create(['invoice_id' => $invoice->id] + $item);
                }

                Payment::create([
                    'invoice_id'     => $invoice->id,
                    'customer_id'    => $invoice->customer_id,
                    'payment_method' => collect(['cash', 'upi', 'card'])->random(),
                    'amount'         => $total,
                    'created_at'     => $when,
                    'updated_at'     => $when,
                ]);
            }
        }

        // Keep invoice_next_number consistent with the seeded data.
        $next = Invoice::count() + 1;
        \App\Models\Setting::updateOrCreate(['key' => 'invoice_next_number'], ['value' => (string) $next, 'group' => 'invoice']);
        app(\App\Services\SettingService::class)->flush();
    }
}

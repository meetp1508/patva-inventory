<?php

namespace Tests\Feature;

use App\Events\InvoiceCreated;
use App\Events\PaymentCompleted;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_pos_checkout_creates_invoice_and_decrements_stock(): void
    {
        Event::fake([InvoiceCreated::class, PaymentCompleted::class]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'selling_price' => 100,
            'tax_rate' => 10,
            'stock_quantity' => 20,
        ]);
        $customer = Customer::factory()->create();

        $payload = [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 100, 'tax_rate' => 10],
            ],
            'discount_amount' => 0,
            'payment_method' => 'cash',
            'paid_amount' => 330,
        ];

        $this->actingAs($admin)
            ->postJson(route('pos.checkout'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $invoice = Invoice::firstOrFail();
        $this->assertEquals(300.0, (float) $invoice->subtotal);
        $this->assertEquals(30.0, (float) $invoice->tax_amount);
        $this->assertEquals(330.0, (float) $invoice->total_amount);
        $this->assertEquals('paid', $invoice->status);

        $this->assertEquals(17, $product->fresh()->stock_quantity);

        Event::assertDispatched(InvoiceCreated::class);
        Event::assertDispatched(PaymentCompleted::class);
    }

    public function test_cashier_cannot_access_products(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $this->actingAs($cashier)
            ->get('/products')
            ->assertForbidden();
    }
}

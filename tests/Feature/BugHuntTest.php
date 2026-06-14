<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Each test asserts the *correct* expected behaviour. These were written to
 * document bugs and now serve as regression tests for the fixes.
 */
class BugHuntTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    private function product(array $attrs = []): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(array_merge([
            'category_id'    => $category->id,
            'selling_price'  => 100,
            'tax_rate'       => 0,
            'stock_quantity' => 50,
        ], $attrs));
    }

    /**
     * BUG #1 — Price tampering. The checkout trusts the client-supplied
     * unit_price, so a buyer can pay any price they like. The server should
     * price each line from the product's own selling_price.
     */
    public function test_checkout_does_not_trust_client_supplied_unit_price(): void
    {
        $product = $this->product(['selling_price' => 100]);

        $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    // Real price is 100; attacker sends 1.
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1],
                ],
                'payment_method' => 'cash',
            ])->assertOk();

        $invoice = Invoice::firstOrFail();

        // Expected: 2 × 100 = 200. Bug: records 2 × 1 = 2.
        $this->assertEquals(200.0, (float) $invoice->total_amount,
            'unit_price came from the untrusted client instead of the product.');
    }

    /**
     * BUG #2 — Invoice subtotal is inconsistent with its line items.
     * invoice.subtotal is stored pre-tax, but each invoice_item.subtotal is
     * stored tax-INCLUSIVE, so the line items never reconcile to the header.
     */
    public function test_invoice_header_subtotal_reconciles_with_line_items(): void
    {
        $product = $this->product(['selling_price' => 100, 'tax_rate' => 10]);

        $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 10],
                ],
                'payment_method' => 'cash',
            ])->assertOk();

        $invoice = Invoice::with('items')->firstOrFail();

        // Header subtotal (200) should equal the sum of line subtotals.
        // Bug: each line subtotal bakes in tax (220), so they disagree.
        $this->assertEquals(
            (float) $invoice->subtotal,
            (float) $invoice->items->sum('subtotal'),
            'Line item subtotals do not reconcile with the invoice subtotal.'
        );
    }

    /**
     * BUG #3 — Deleting an invoice does not return goods to stock.
     * The sale decremented stock; voiding the invoice should restore it.
     */
    public function test_deleting_invoice_restores_stock(): void
    {
        $product = $this->product(['stock_quantity' => 50]);

        $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100],
                ],
                'payment_method' => 'cash',
            ])->assertOk();

        $this->assertEquals(45, $product->fresh()->stock_quantity);

        $invoice = Invoice::firstOrFail();
        $this->actingAs($this->admin())->delete(route('invoices.destroy', $invoice));

        // Expected: stock back to 50. Bug: stays at 45 (goods lost forever).
        $this->assertEquals(50, $product->fresh()->stock_quantity,
            'Voiding an invoice must restore the sold stock.');
    }

    /**
     * BUG #4 — Deleted (soft-deleted) invoices still inflate analytics.
     * Invoice totals exclude trashed invoices, but topProducts() reads from
     * invoice_items, which are not filtered by the parent invoice's state.
     */
    public function test_top_products_excludes_deleted_invoices(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 100],
                ],
                'payment_method' => 'cash',
            ])->assertOk();

        Invoice::firstOrFail()->delete();

        $top = app(DashboardRepositoryInterface::class)->topProducts();

        // Expected: nothing sold remains after the void. Bug: item still counts.
        $this->assertCount(0, $top,
            'Soft-deleted invoice still contributes to top-selling products.');
    }

    /**
     * BUG #5 — A variant belonging to a *different* product is accepted.
     * Checkout never verifies variant_id belongs to product_id, so stock is
     * taken from an unrelated product's variant.
     */
    public function test_checkout_rejects_variant_from_another_product(): void
    {
        $productA = $this->product();
        $productB = $this->product();

        $variantOfB = ProductVariant::create([
            'product_id'       => $productB->id,
            'variant_name'     => 'B-Red',
            'sku'              => 'B-RED-1',
            'additional_price' => 0,
            'stock_quantity'   => 10,
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    // product A but a variant that belongs to product B.
                    ['product_id' => $productA->id, 'variant_id' => $variantOfB->id, 'quantity' => 1, 'unit_price' => 100],
                ],
                'payment_method' => 'cash',
            ]);

        // Expected: rejected (422). Bug: accepted, mis-attributing stock.
        $response->assertStatus(422);
    }

    /**
     * CONTROL — legitimate over-sell is correctly blocked. This one should
     * PASS, confirming the stock guard itself works.
     */
    public function test_checkout_blocks_overselling(): void
    {
        $product = $this->product(['stock_quantity' => 3]);

        $this->actingAs($this->admin())
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100],
                ],
                'payment_method' => 'cash',
            ])->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertEquals(3, $product->fresh()->stock_quantity);
        $this->assertEquals(0, Invoice::count());
    }
}

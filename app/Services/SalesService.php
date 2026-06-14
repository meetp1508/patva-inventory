<?php

namespace App\Services;

use App\Events\InvoiceCreated;
use App\Events\PaymentCompleted;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Handles POS checkout end-to-end: stock decrement (via InventoryService),
 * tax/discount calculation, invoice + items + payment creation, sequential
 * invoice numbering from settings, and domain event dispatch.
 */
class SalesService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly SettingService $settings,
    ) {
    }

    /**
     * Process a checkout payload and return the created invoice.
     *
     * @param array{
     *   customer_id?: int|null,
     *   items: array<int, array{product_id:int, variant_id?:int|null, quantity:int, unit_price:float, tax_rate?:float}>,
     *   discount_amount?: float,
     *   payment_method: string,
     *   paid_amount?: float
     * } $payload
     */
    public function checkout(array $payload): Invoice
    {
        return DB::transaction(function () use ($payload) {
            $customerId = $this->resolveCustomerId($payload);

            $subtotal = 0.0;
            $taxTotal = 0.0;
            $lines = [];

            foreach ($payload['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $variant = !empty($item['variant_id']) ? ProductVariant::lockForUpdate()->findOrFail($item['variant_id']) : null;

                // A variant must belong to the line's product, otherwise we'd
                // bill one product while drawing stock from another.
                if ($variant && (int) $variant->product_id !== (int) $product->id) {
                    throw new RuntimeException("Variant {$variant->variant_name} does not belong to {$product->name}.");
                }

                $quantity = (int) $item['quantity'];

                // Pricing is authoritative on the server — never trust the client.
                // Variant price = product selling price + the variant's surcharge.
                $unitPrice = (float) $product->selling_price + (float) ($variant->additional_price ?? 0);
                $taxRate = (float) ($product->tax_rate ?? 0);

                $lineSubtotal = $unitPrice * $quantity;
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                // Decrement stock through the inventory service so a log is written.
                $this->inventory->adjust(
                    $product,
                    -$quantity,
                    InventoryService::ACTION_SALE,
                    'POS sale',
                    $variant,
                );

                $lines[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name'       => $product->name . ($variant ? ' — ' . $variant->variant_name : ''),
                    'quantity'           => $quantity,
                    'unit_price'         => $unitPrice,
                    // Pre-tax line total so item subtotals reconcile with the
                    // invoice header subtotal; tax is itemised separately.
                    'subtotal'           => $lineSubtotal,
                ];
            }

            $discount = (float) ($payload['discount_amount'] ?? 0);
            $total = max(0, $subtotal + $taxTotal - $discount);
            $paid = (float) ($payload['paid_amount'] ?? $total);

            $invoice = Invoice::create([
                'invoice_number'  => $this->settings->nextInvoiceNumber(),
                'customer_id'     => $customerId,
                'user_id'         => Auth::id(),
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxTotal,
                'discount_amount' => $discount,
                'total_amount'    => $total,
                'status'          => $paid >= $total ? 'paid' : 'partial',
            ]);

            foreach ($lines as $line) {
                $line['invoice_id'] = $invoice->id;
                InvoiceItem::create($line);
            }

            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'customer_id'    => $customerId,
                'payment_method' => $payload['payment_method'],
                'amount'         => min($paid, $total),
            ]);

            // Update customer outstanding balance if the bill wasn't fully paid.
            if ($invoice->customer_id && $paid < $total) {
                Customer::where('id', $invoice->customer_id)->increment('outstanding_balance', $total - $paid);
            }

            event(new InvoiceCreated($invoice));
            event(new PaymentCompleted($payment));

            return $invoice;
        });
    }

    /**
     * Resolve the customer for this sale. An explicit customer_id wins; otherwise
     * the inline "new customer" details (name + phone) create one — reusing an
     * existing record when the phone already exists so POS never errors on a
     * returning walk-in.
     *
     * @param array<string, mixed> $payload
     */
    private function resolveCustomerId(array $payload): ?int
    {
        if (! empty($payload['customer_id'])) {
            return (int) $payload['customer_id'];
        }

        $new = $payload['new_customer'] ?? null;

        if (! is_array($new) || empty($new['name']) || empty($new['phone'])) {
            return null;
        }

        $phone = trim((string) $new['phone']);

        $customer = Customer::withTrashed()->where('phone', $phone)->first();

        if ($customer) {
            if ($customer->trashed()) {
                $customer->restore();
            }

            return $customer->id;
        }

        return Customer::create([
            'name'    => trim((string) $new['name']),
            'phone'   => $phone,
            'address' => isset($new['address']) ? trim((string) $new['address']) : null,
        ])->id;
    }
}

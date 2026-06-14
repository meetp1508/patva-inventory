<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\WhatsApp\WhatsAppService;

class SendPaymentWhatsApp
{
    public function __construct(private readonly WhatsAppService $whatsapp)
    {
    }

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment->loadMissing('invoice.customer');
        $customer = $payment->invoice?->customer;

        if (! $customer || ! $customer->phone) {
            return;
        }

        $body = sprintf(
            "Hi %s, we have received your %s payment of %s for invoice %s. Thank you!",
            $customer->name,
            strtoupper($payment->payment_method),
            money($payment->amount),
            $payment->invoice->invoice_number,
        );

        $this->whatsapp->queueText($customer->phone, $body, $payment->invoice);
    }
}

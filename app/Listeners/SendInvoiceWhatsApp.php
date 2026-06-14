<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Services\WhatsApp\WhatsAppService;

class SendInvoiceWhatsApp
{
    public function __construct(private readonly WhatsAppService $whatsapp)
    {
    }

    public function handle(InvoiceCreated $event): void
    {
        // Only auto-send if a customer with a phone is attached.
        if ($event->invoice->customer && $event->invoice->customer->phone) {
            $this->whatsapp->queueInvoice($event->invoice);
        }
    }
}

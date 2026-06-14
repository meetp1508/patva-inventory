<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Services\ActivityLogger;

class LogInvoiceActivity
{
    public function __construct(private readonly ActivityLogger $logger)
    {
    }

    public function handle(InvoiceCreated $event): void
    {
        $this->logger->log(
            'sale',
            "Invoice {$event->invoice->invoice_number} created for " . money($event->invoice->total_amount),
            $event->invoice,
            ['total' => $event->invoice->total_amount],
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\WhatsApp\WhatsAppService;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsapp)
    {
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if (! $invoice->customer || ! $invoice->customer->phone) {
            return back()->with('error', 'No customer phone on this invoice.');
        }

        $this->whatsapp->queueInvoice($invoice);

        return back()->with('success', 'WhatsApp message queued.');
    }
}

<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Invoice;
use App\Models\WhatsappLog;

/**
 * Public entry point for WhatsApp. Queues a job that picks the configured
 * driver, sends the message, and updates the log row.
 */
class WhatsAppService
{
    /**
     * Resolve the active driver based on the `whatsapp_driver` setting
     * (falls back to the config default).
     */
    public function driver(): WhatsAppDriver
    {
        $name = (string) (setting('whatsapp_driver') ?: config('whatsapp.default', 'log'));

        return match ($name) {
            'meta' => app(MetaCloudDriver::class),
            default => app(LogDriver::class),
        };
    }

    public function queueText(string $to, string $body, ?Invoice $invoice = null): WhatsappLog
    {
        $log = WhatsappLog::create([
            'invoice_id'  => $invoice?->id,
            'customer_id' => $invoice?->customer_id,
            'to_number'   => $to,
            'type'        => 'text',
            'payload'     => ['body' => $body],
            'status'      => 'pending',
        ]);

        SendWhatsAppMessage::dispatch($log->id);

        return $log;
    }

    public function queueInvoice(Invoice $invoice): ?WhatsappLog
    {
        if (! $invoice->customer || ! $invoice->customer->phone) {
            return null;
        }

        $body = sprintf(
            "Hi %s, your invoice %s from %s is ready. Total: %s. Thank you!",
            $invoice->customer->name,
            $invoice->invoice_number,
            setting('company_name'),
            money($invoice->total_amount),
        );

        return $this->queueText($invoice->customer->phone, $body, $invoice);
    }
}

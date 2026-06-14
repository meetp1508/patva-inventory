<?php

namespace App\Jobs;

use App\Models\WhatsappLog;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly int $logId)
    {
    }

    public function handle(WhatsAppService $service): void
    {
        $log = WhatsappLog::find($this->logId);
        if (! $log) {
            return;
        }

        $log->increment('attempts');

        try {
            $body = $log->payload['body'] ?? '';
            $providerId = $service->driver()->sendText($log->to_number, $body);

            $log->update([
                'status' => 'sent',
                'provider_message_id' => $providerId,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => $log->attempts >= $this->tries ? 'failed' : 'pending',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

<?php

use App\Jobs\SendWhatsAppMessage;
use App\Models\WhatsappLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('whatsapp:retry-failed', function () {
    WhatsappLog::query()
        ->where('status', 'pending')
        ->where('attempts', '<', 3)
        ->orderBy('id')
        ->chunkById(50, function ($logs) {
            foreach ($logs as $log) {
                SendWhatsAppMessage::dispatch($log->id);
            }
        });
    $this->info('Re-queued pending WhatsApp messages.');
})->purpose('Re-queue any WhatsApp messages still pending');

// Retry stuck WhatsApp messages every 10 minutes.
Schedule::command('whatsapp:retry-failed')->everyTenMinutes();

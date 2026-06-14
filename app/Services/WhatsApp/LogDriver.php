<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Dev / fallback driver that just logs the outgoing message. Useful in
 * development and as a safety net when Meta credentials are not configured.
 */
class LogDriver implements WhatsAppDriver
{
    public function sendText(string $to, string $body): string
    {
        $id = 'log-' . Str::uuid()->toString();
        Log::info('[WhatsApp:LogDriver] sending message', ['to' => $to, 'body' => $body, 'id' => $id]);

        return $id;
    }
}

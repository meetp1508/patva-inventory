<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends WhatsApp messages via the Meta WhatsApp Cloud API (Graph API).
 * Credentials are pulled from settings first (DB) and fall back to config/env.
 */
class MetaCloudDriver implements WhatsAppDriver
{
    public function sendText(string $to, string $body): string
    {
        $phoneId = (string) (setting('whatsapp_phone_id') ?: config('whatsapp.drivers.meta.phone_number_id'));
        $token   = (string) (setting('whatsapp_token') ?: config('whatsapp.drivers.meta.access_token'));
        $version = (string) config('whatsapp.drivers.meta.api_version', 'v19.0');
        $base    = (string) config('whatsapp.drivers.meta.endpoint', 'https://graph.facebook.com');

        if (! $phoneId || ! $token) {
            throw new RuntimeException('Meta WhatsApp credentials are not configured.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$base}/{$version}/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D+/', '', $to),
                'type' => 'text',
                'text' => ['body' => $body],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Meta API error: ' . $response->body());
        }

        $messages = $response->json('messages', []);

        return $messages[0]['id'] ?? 'unknown';
    }
}

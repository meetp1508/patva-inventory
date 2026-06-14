<?php

namespace App\Services\WhatsApp;

interface WhatsAppDriver
{
    /**
     * Send a plain text message to a WhatsApp number.
     * Returns the provider message id on success, throws on failure.
     */
    public function sendText(string $to, string $body): string;
}

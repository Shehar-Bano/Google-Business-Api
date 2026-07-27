<?php

namespace App\Services\WhatsApp;

interface WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp message to the specified recipient.
     *
     * @param string $phoneNumber Recipient phone number.
     * @param string $message Message content.
     * @return array Array containing keys: 'success' (bool), 'message_id' (string|null), 'error' (string|null).
     */
    public function sendMessage(string $phoneNumber, string $message): array;
}

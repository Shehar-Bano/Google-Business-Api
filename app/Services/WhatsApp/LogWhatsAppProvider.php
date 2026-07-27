<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

class LogWhatsAppProvider implements WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp message (Logged/Simulated by default).
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        Log::info("WhatsApp Message Dispatched via LogWhatsAppProvider", [
            'to' => $phoneNumber,
            'message' => $message
        ]);

        // Generate a mock message ID
        $mockMessageId = 'wa_msg_' . bin2hex(random_bytes(8));

        return [
            'success' => true,
            'message_id' => $mockMessageId,
            'error' => null
        ];
    }
}

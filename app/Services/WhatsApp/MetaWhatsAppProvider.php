<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppProvider implements WhatsAppProviderInterface
{
    protected ?string $accessToken;
    protected ?string $phoneNumberId;
    protected string $baseUrl;
    protected string $version;

    public function __construct()
    {
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->baseUrl = config('meta.base_url', 'https://graph.facebook.com');
        $this->version = config('meta.graph_version', 'v20.0');
    }

    /**
     * Send a WhatsApp message to the specified recipient via Meta Cloud API.
     *
     * @param string $phoneNumber Recipient phone number.
     * @param string $message Message content.
     * @return array Array containing keys: 'success' (bool), 'message_id' (string|null), 'error' (string|null).
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            Log::error("Meta WhatsApp credentials are missing in the configuration.");
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Meta WhatsApp credentials are not configured.'
            ];
        }

        // Clean phone number: remove any non-numeric characters (like + or spaces)
        // Meta API expects the country code and number as digits only, e.g., 15556627024
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (empty($cleanPhone)) {
            Log::error("Meta WhatsApp failed: Recipient phone number is empty or invalid.", [
                'original_phone' => $phoneNumber
            ]);
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Invalid phone number format.'
            ];
        }

        $url = rtrim($this->baseUrl, '/') . '/' . $this->version . '/' . $this->phoneNumberId . '/messages';

        try {
            Log::info("Dispatching Meta WhatsApp Message Request", [
                'to' => $cleanPhone,
                'phone_number_id' => $this->phoneNumberId,
                'url' => $url,
            ]);

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message
                ]
            ];

            $response = Http::withToken($this->accessToken)
                ->asJson()
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                Log::info("Meta WhatsApp Message sent successfully", [
                    'message_id' => $messageId,
                    'to' => $cleanPhone
                ]);

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'error' => null
                ];
            }

            $errorResponse = $response->json();
            $errorMessage = $errorResponse['error']['message'] ?? $response->body();

            Log::error("Meta WhatsApp API error response received", [
                'status' => $response->status(),
                'error' => $errorResponse
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Meta API Error: ' . $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error("Exception in MetaWhatsAppProvider: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}

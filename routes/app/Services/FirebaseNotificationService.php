<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseNotificationService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function sendToToken(string $token, string $title, string $body, array $data = [], bool $validateOnly = false): array
    {
        $projectId = $this->projectId();
        $accessToken = $this->accessToken();

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'validate_only' => $validateOnly,
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $this->stringData($data),
                ],
            ]);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException($response->body(), $response->status(), $exception);
        }

        return $response->json();
    }

    public function accessToken(): string
    {
        $serviceAccount = $this->serviceAccount();
        $now = time();

        $jwtHeader = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $jwtClaim = $this->base64UrlEncode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsignedJwt = "{$jwtHeader}.{$jwtClaim}";
        $signed = openssl_sign($unsignedJwt, $signature, $serviceAccount['private_key'], OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Unable to sign Firebase service-account JWT.');
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => "{$unsignedJwt}.{$this->base64UrlEncode($signature)}",
        ]);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException($response->body(), $response->status(), $exception);
        }

        return (string) $response->json('access_token');
    }

    private function serviceAccount(): array
    {
        $path = (string) config('services.firebase.credentials');

        if ($path === '') {
            throw new RuntimeException('FIREBASE_CREDENTIALS is not configured.');
        }

        $fullPath = str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)
            ? $path
            : base_path($path);

        if (! is_file($fullPath)) {
            throw new RuntimeException("Firebase credentials file was not found at {$fullPath}.");
        }

        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            throw new RuntimeException('Unable to read Firebase credentials file.');
        }

        $serviceAccount = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        foreach (['client_email', 'private_key'] as $key) {
            if (empty($serviceAccount[$key])) {
                throw new RuntimeException("Firebase credentials file is missing {$key}.");
            }
        }

        return $serviceAccount;
    }

    private function projectId(): string
    {
        $projectId = (string) config('services.firebase.project_id');

        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        return $projectId;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function stringData(array $data): array
    {
        return collect($data)
            ->mapWithKeys(fn (mixed $value, string $key) => [$key => is_scalar($value) ? (string) $value : json_encode($value)])
            ->all();
    }
}

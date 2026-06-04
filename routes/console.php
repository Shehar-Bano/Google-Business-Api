<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\FirebaseNotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('firebase:test-credentials', function (FirebaseNotificationService $firebase) {
    $token = $firebase->accessToken();

    $this->info('Firebase credentials are valid.');
    $this->line('Access token received: '.substr($token, 0, 12).'...');
})->purpose('Verify Firebase service-account credentials');

Artisan::command('firebase:test-notification {token : Device FCM token} {--title=SquashPro Test} {--body=Firebase notification test from Laravel.} {--validate-only : Validate with FCM without delivering}', function (FirebaseNotificationService $firebase) {
    try {
        $response = $firebase->sendToToken(
            (string) $this->argument('token'),
            (string) $this->option('title'),
            (string) $this->option('body'),
            [
                'type' => 'test',
                'source' => 'laravel-artisan',
            ],
            (bool) $this->option('validate-only')
        );

        $this->info($this->option('validate-only') ? 'Firebase notification payload is valid.' : 'Firebase notification sent.');
        $this->line(json_encode($response, JSON_PRETTY_PRINT));
    } catch (\RuntimeException $exception) {
        $message = $exception->getMessage();

        if (str_contains($message, 'UNREGISTERED')) {
            $this->error('This FCM token is unregistered.');
            $this->warn('Use a fresh device token from the current build of the app. Tokens often change after reinstall, logout, or app data reset.');
            return self::FAILURE;
        }

        $this->error($message);
        return self::FAILURE;
    }
})->purpose('Send a Firebase test notification to a device token');

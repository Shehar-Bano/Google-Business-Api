<?php

namespace App\Providers;

use App\Services\Otp\Contracts\OtpSenderInterface;
use App\Services\Otp\Senders\DummyOtpSender;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpSenderInterface::class, DummyOtpSender::class);
        $this->app->bind(
            \App\Services\WhatsApp\WhatsAppProviderInterface::class,
            function ($app) {
                if (config('services.whatsapp.provider') === 'meta') {
                    return $app->make(\App\Services\WhatsApp\MetaWhatsAppProvider::class);
                }
                return $app->make(\App\Services\WhatsApp\LogWhatsAppProvider::class);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Listen for Login
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\AdminAuditLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'login',
                    'description' => "User {$event->user->name} logged in.",
                    'ip_address' => request()->ip(),
                ]);
            }
        );

        // Listen for Logout
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function ($event) {
                if ($event->user) {
                    \App\Models\AdminAuditLog::create([
                        'user_id' => $event->user->id,
                        'action' => 'logout',
                        'description' => "User {$event->user->name} logged out.",
                        'ip_address' => request()->ip(),
                    ]);
                }
            }
        );
    }
}

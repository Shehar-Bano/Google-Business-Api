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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}

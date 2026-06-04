<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('help-support', [ContentController::class, 'helpSupport']);
    Route::get('privacy-policy', [ContentController::class, 'privacyPolicy']);

    Route::prefix('auth')->group(function () {
        Route::post('register/player', [AuthController::class, 'registerPlayer']);
        Route::post('register/club', [AuthController::class, 'registerClub']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('api.token')->group(function () {
        Route::prefix('player')->middleware('api.role:player')->group(function () {
            Route::post('complete-profile', [AuthController::class, 'completePlayerProfile']);
        });

        Route::prefix('account')->group(function () {
            Route::get('dashboard', [AccountController::class, 'dashboard']);
            Route::get('profile', [AccountController::class, 'profile']);
            Route::post('details/update', [AccountController::class, 'updateDetails']);
            Route::post('logo/update', [AccountController::class, 'updateLogo']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('delete', [AuthController::class, 'deleteAccount']);
            Route::get('notifications', [NotificationController::class, 'index']);
            Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::patch('notifications/{notification_id}/read', [NotificationController::class, 'markAsRead']);
        });
    });

    // TODO: Add future API modules here.
});

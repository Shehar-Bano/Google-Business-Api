<?php

use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AccountFcmTokenController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Models\Poster;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('help-support', [ContentController::class, 'helpSupport']);
    Route::get('privacy-policy', [ContentController::class, 'privacyPolicy']);
    // Route::get('orders', [OrderController::class, 'index']);
    // Route::get('orders/{order_id}', [OrderController::class, 'show']);
    Route::get('offerings/search', [App\Http\Controllers\Api\OfferingController::class, 'search']);

    Route::prefix('auth')->group(function () {

        Route::post('send-otp', [OtpController::class, 'sendOtp']);
        Route::post('verify-otp', [OtpController::class, 'verifyOtp']);
        Route::post('resend-otp', [OtpController::class, 'resendOtp']);

        Route::post('register/user', [AuthController::class, 'registerPlayer']);
        // Route::post('register/club', [AuthController::class, 'registerClub']);
        // Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        // Route::post('resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

    });

    Route::middleware('api.token')->group(function () {
        Route::apiResource('businesses', App\Http\Controllers\Api\BusinessController::class);

        // Route::post('orders', [OrderController::class, 'store']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('logout', [AuthController::class, 'logout']);
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
            Route::post('fcm-token', [AccountFcmTokenController::class, 'store']);

        });
    });

    Route::get('/user/businesses/{userId}', [BusinessController::class, 'show']);

    // Authentication & Social Connections Routes
    Route::get('social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);

    Route::get('/social/facebook/redirect-url', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookRedirectUrl']);
    // Config Credentials Routes
    Route::get('config/google', [App\Http\Controllers\Api\ConfigController::class, 'googleConfig']);
    Route::get('config/meta', [App\Http\Controllers\Api\ConfigController::class, 'metaConfig']);

    // Google Ads Route
    Route::post('google/keyword-ideas', [App\Http\Controllers\Api\GoogleAdsController::class, 'getKeywordIdeas']);

    Route::middleware('api.token')->group(function () {
        Route::post('auth/google/login', [App\Http\Controllers\Api\AuthController::class, 'googleLogin']);
        Route::get('social/facebook/connect', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookConnect']);
        // Route::post('social/facebook/connect-token', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookConnectToken']);
        Route::get('social/accounts', [App\Http\Controllers\Api\SocialConnectionController::class, 'status']);
        Route::delete('social/facebook/disconnect', [App\Http\Controllers\Api\SocialConnectionController::class, 'disconnectFacebook']);
        Route::delete('social/instagram/disconnect', [App\Http\Controllers\Api\SocialConnectionController::class, 'disconnectInstagram']);

        // Authenticated Business Write Routes
        Route::post('businesses', [App\Http\Controllers\Api\BusinessController::class, 'store']);
        Route::put('businesses/{business}', [App\Http\Controllers\Api\BusinessController::class, 'update']);
        Route::delete('businesses/{business}', [App\Http\Controllers\Api\BusinessController::class, 'destroy']);
        Route::post('businesses/{id}/offerings', [App\Http\Controllers\Api\OfferingController::class, 'saveBusinessOfferings']);

        // Preferences Routes
        Route::get('businesses/{businessId}/preferences', [App\Http\Controllers\Api\PreferenceController::class, 'show']);

        Route::delete('businesses/{businessId}/preferences', [App\Http\Controllers\Api\PreferenceController::class, 'destroy']);

        // Estimated Scores Route
        Route::get('businesses/{businessId}/estimated-scores', [App\Http\Controllers\Api\BusinessController::class, 'getEstimatedScores']);

        // Top Selling Items Routes
        Route::post('top-selling-items/{id}', [App\Http\Controllers\Api\TopSellingItemController::class, 'update']);
        Route::delete('top-selling-items/{id}', [App\Http\Controllers\Api\TopSellingItemController::class, 'destroy']);

        // AI Poster Generation Routes
        Route::post('business/generate-poster', [App\Http\Controllers\Api\PosterController::class, 'generateWithTemplate']);
        Route::post('business/generate-poster-direct', [App\Http\Controllers\Api\PosterController::class, 'generateDirect']);
        Route::get('business/generated-posters/{id}', [App\Http\Controllers\Api\PosterController::class, 'generationStatus']);
        Route::post('business/generated-posters/{id}/approve', [App\Http\Controllers\Api\PosterController::class, 'approve']);
        Route::post('business/generated-posters/{id}/reject', [App\Http\Controllers\Api\PosterController::class, 'reject']);

        // WhatsApp Review Request Routes
        Route::post('whatsapp/review-request', [App\Http\Controllers\Api\ReviewRequestController::class, 'sendWhatsAppRequest']);

        // Google Business Profile Management API Routes
        Route::get('google-business/accounts', [App\Http\Controllers\Api\GoogleBusinessController::class, 'getAccounts']);
        Route::get('google-business/accounts/{accountId}/locations', [App\Http\Controllers\Api\GoogleBusinessController::class, 'getLocations']);
        Route::get('google-business/locations/{locationId}/details', [App\Http\Controllers\Api\GoogleBusinessController::class, 'getLocationDetails']);

    });

    // AI Suggestion Routes
    Route::post('ai/suggestions', [App\Http\Controllers\Api\AiSuggestionController::class, 'getSuggestions']);

    // Subscriptions Plans API
    Route::get('subscription-plans', [App\Http\Controllers\Api\SubscriptionPlanController::class, 'index']);

    // Terms & Conditions API
    Route::get('terms-conditions', [App\Http\Controllers\Api\V1\TermsConditionController::class, 'index']);

    // Google Places API Details
    Route::get('google/place-details', [App\Http\Controllers\Api\V1\GooglePlaceController::class, 'getPlaceDetails']);

    Route::post('businesses/{businessId}/preferences', [App\Http\Controllers\Api\PreferenceController::class, 'storeOrUpdate']);
    // TODO: Add future API modules here.
});

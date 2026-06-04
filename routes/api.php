<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingReviewController;
use App\Http\Controllers\Api\V1\ClubController;
use App\Http\Controllers\Api\V1\PlayerContentController;
use App\Http\Controllers\Api\V1\PlayerDashboardController;
use App\Http\Controllers\Api\V1\PlayerBookingController;
use App\Http\Controllers\Api\V1\PlayerClubController;
use App\Http\Controllers\Api\V1\PlayerNotificationController;
use App\Http\Controllers\Api\V1\PlayerProfileController;
use App\Http\Controllers\Api\V1\PlayerTournamentController;
use App\Http\Controllers\Api\V1\PublicCityController;
use Illuminate\Support\Facades\Route;


// API Version 1
Route::prefix('v1')->group(function () {

    Route::get('/cities', [PublicCityController::class, 'index']);
    Route::get('/help-support', [PlayerContentController::class, 'helpSupport']);
    Route::get('/privacy-policy', [PlayerContentController::class, 'privacyPolicy']);

    // Public Auth Routes (No Authentication Required)
    Route::prefix('auth')->group(function () {
        // Registration
        Route::post('register/player', [AuthController::class, 'registerPlayer']);
        Route::post('register/club', [AuthController::class, 'registerClub']);

        // OTP Verification
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);

        // Login
        Route::post('login', [AuthController::class, 'login']);

        // Password Reset
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Protected Routes (Authentication Required)
    Route::middleware('api.token')->group(function () {
        // Player Routes
        Route::prefix('player')->middleware('api.role:player')->group(function () {
            Route::post('complete-profile', [AuthController::class, 'completePlayerProfile']);
            Route::get('dashboard', [PlayerDashboardController::class, 'index']);
            Route::get('clubs', [PlayerClubController::class, 'index']);
            Route::get('clubs/{club_id}', [PlayerClubController::class, 'show']);
            Route::get('clubs/{club_id}/courts', [PlayerClubController::class, 'courts']);
            Route::get('courts/{court_id}/time-slots', [PlayerClubController::class, 'timeSlots']);
            Route::get('bookings', [PlayerBookingController::class, 'index']);
            Route::post('bookings', [PlayerBookingController::class, 'store']);
            Route::get('booking/{booking_id}', [PlayerBookingController::class, 'show']);
            Route::post('booking/cancel', [PlayerBookingController::class, 'cancel']);
            Route::post('bookings/{booking_id}/review', [BookingReviewController::class, 'store']);
            Route::get('profile', [PlayerProfileController::class, 'show']);
            Route::post('details/update', [PlayerProfileController::class, 'updatePlayerDetails']);
            Route::post('logo/update', [PlayerProfileController::class, 'updatePlayerLogo']);
            Route::get('tournaments', [PlayerTournamentController::class, 'index']);
            Route::get('tournament/{tournament_id}', [PlayerTournamentController::class, 'show']);
            Route::get('payment-methods', [PlayerTournamentController::class, 'paymentMethods']);
            Route::post('tournament/register', [PlayerTournamentController::class, 'register']);
        });

        // Club Routes
        Route::prefix('club')->middleware('api.role:club')->group(function () {
            Route::get('dashboard', [ClubController::class, 'dashboard']);
            Route::get('courts', [ClubController::class, 'courts']);
            Route::post('courts', [ClubController::class, 'storeCourt']);
            Route::get('courts/{court_id}', [ClubController::class, 'courtDetail']);
            Route::post('courts/{court_id}/edit', [ClubController::class, 'editCourt']);
            Route::post('courts/{court_id}/set-maintenance', [ClubController::class, 'setCourtMaintenance']);
            Route::get('bookings', [ClubController::class, 'bookings']);
            Route::get('bookings/{booking_id}', [ClubController::class, 'bookingDetail']);
            Route::post('bookings/{booking_id}/status', [ClubController::class, 'updateBookingStatus']);
            Route::get('tournaments', [ClubController::class, 'tournaments']);
            Route::post('tournaments', [ClubController::class, 'storeTournament']);
            Route::get('tournaments/{tournament_id}', [ClubController::class, 'tournamentDetail']);
            Route::get('tournaments/{tournament_id}/enrolled-users', [ClubController::class, 'tournamentEnrolledUsers']);
            Route::post('tournaments/{tournament_id}/update', [ClubController::class, 'updateTournament']);
            Route::get('profile', [ClubController::class, 'profile']);
            Route::post('details/update', [ClubController::class, 'updateClubDetails']);
            Route::post('logo/update', [ClubController::class, 'updateClubLogo']);
        });

        // Common Authenticated Routes (Both Player & Club)
        Route::prefix('account')->group(function () {
            Route::post('delete', [AuthController::class, 'deleteAccount']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::get('notifications', [PlayerNotificationController::class, 'index']);
            Route::patch('notifications/read-all', [PlayerNotificationController::class, 'markAllAsRead']);
            Route::patch('notifications/{notification_id}/read', [PlayerNotificationController::class, 'markAsRead']);
        });
    });
});

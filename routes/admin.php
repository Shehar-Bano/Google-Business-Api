<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModulePlaceholderController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\PrivacyPolicyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SupportOptionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:super_admin|admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // TODO: Add future feature routes here.
        Route::get('modules/{module}', [ModulePlaceholderController::class, 'index'])->name('modules.show');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        Route::get('notifications', [ModulePlaceholderController::class, 'index'])->defaults('module', 'notifications')->name('notifications.index');
        Route::resource('support-options', SupportOptionController::class)->except(['show']);
        Route::get('privacy-policy', [PrivacyPolicyController::class, 'edit'])->name('privacy-policy.edit');
        Route::put('privacy-policy', [PrivacyPolicyController::class, 'update'])->name('privacy-policy.update');
        Route::resource('terms-conditions', App\Http\Controllers\Admin\TermsConditionController::class);

        Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
        Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
        Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

        // Audit Logs Route
        Route::get('audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

        // Subscriptions Management
        Route::resource('subscription-plans', App\Http\Controllers\Admin\SubscriptionPlanController::class);

        // User Management
        Route::get('user-management', [UserManagementController::class, 'index'])->name('user-management.index');
        Route::get('user-management/{user}', [UserManagementController::class, 'show'])->name('user-management.show');
        Route::patch('user-management/{user}/status', [UserManagementController::class, 'updateStatus'])->name('user-management.update-status');

        // Business Management
        Route::get('business-management/{business}/preferences', [App\Http\Controllers\Admin\BusinessManagementController::class, 'showPreferences'])->name('business-management.preferences');
        Route::get('business-management/{business}/keyword-ideas', [App\Http\Controllers\Admin\BusinessManagementController::class, 'showKeywordIdeas'])->name('business-management.keyword-ideas');
        Route::resource('business-management', App\Http\Controllers\Admin\BusinessManagementController::class)->parameters(['business-management' => 'business']);

        // Estimated Scores Management
        Route::get('estimated-scores', [App\Http\Controllers\Admin\EstimatedScoreController::class, 'index'])->name('estimated-scores.index');

        // Google Business Connections
        Route::get('google-business-connections', [App\Http\Controllers\Admin\GoogleBusinessConnectionController::class, 'index'])->name('google-business-connections.index');
        Route::get('whatsapp-review-requests', [App\Http\Controllers\Admin\WhatsAppReviewRequestController::class, 'index'])->name('whatsapp-review-requests.index');

        // Order Management
        // Route::get('order-management', [OrderManagementController::class, 'index'])->name('order-management.index');
        // Route::get('order-management/{order}', [OrderManagementController::class, 'show'])->name('order-management.show');
        // Route::patch('order-management/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('order-management.update-status');

        // Poster Management
        Route::resource('posters', App\Http\Controllers\Admin\PosterController::class);

        // AI Generated Posters Management
        Route::resource('ai-generated-posters', App\Http\Controllers\Admin\AiGeneratedPosterController::class)->only(['index', 'show']);
        Route::post('ai-generated-posters/{ai_generated_poster}/approve', [App\Http\Controllers\Admin\AiGeneratedPosterController::class, 'approve'])->name('ai-generated-posters.approve');
        Route::post('ai-generated-posters/{ai_generated_poster}/reject', [App\Http\Controllers\Admin\AiGeneratedPosterController::class, 'reject'])->name('ai-generated-posters.reject');

        Route::resource('videos', App\Http\Controllers\Admin\VideoController::class);
    });

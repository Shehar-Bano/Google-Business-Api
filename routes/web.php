<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LinkRedirectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->hasAnyRole(['super_admin', 'user'])) {
        return redirect()->route('admin.dashboard.index');
    }

    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('r/{id}', [LinkRedirectionController::class, 'redirect'])->name('link.redirect');

// Social OAuth Callbacks (supports all domain prefixes: /public/api/..., /api/..., /v1/...)
Route::get('social/instagram/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'instagramCallback']);
Route::get('api/social/instagram/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'instagramCallback']);
Route::get('public/api/social/instagram/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'instagramCallback']);
Route::get('public/api/v1/social/instagram/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'instagramCallback']);
Route::get('v1/social/instagram/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'instagramCallback']);

Route::get('social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);
Route::get('api/social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);
Route::get('public/api/social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);
Route::get('public/api/v1/social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);
Route::get('v1/social/facebook/callback', [App\Http\Controllers\Api\SocialConnectionController::class, 'facebookCallback']);

Route::get('/run-queue', function () {
    try {
        // Increase execution time limit since AI image generation can take a bit of time
        set_time_limit(300);
        
        \Illuminate\Support\Facades\Artisan::call('queue:work', [
            '--queue' => 'poster-generation,default',
            '--stop-when-empty' => true,
            '--tries' => 1
        ]);
        
        return response()->json([
            'success' => true,
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

Route::get('/view-logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return 'Log file does not exist at ' . $logFile;
    }
    
    $lines = file($logFile);
    $lastLines = array_slice($lines, -150); // Read last 150 lines
    
    return response(implode("", $lastLines), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/check-posters', function () {
    try {
        $posters = \App\Models\Poster::all();
        $result = [];
        
        foreach ($posters as $poster) {
            $fullPath = public_path($poster->image);
            $exists = file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            $result[] = [
                'id' => $poster->id,
                'title' => $poster->title,
                'image_path' => $poster->image,
                'full_path' => $fullPath,
                'exists' => $exists,
                'size_bytes' => $size,
                'size_kb' => round($size / 1024, 2),
                'status' => $poster->status
            ];
        }
        
        return response()->json([
            'success' => true,
            'posters' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

Route::get('/clear-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
    
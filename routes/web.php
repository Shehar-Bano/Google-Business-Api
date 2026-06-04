<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->hasAnyRole(['super_admin', 'admin'])) {
        return redirect()->route('admin.dashboard.index');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

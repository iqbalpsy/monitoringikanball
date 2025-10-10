<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Include test routes
require __DIR__ . '/test.php';

// Login page
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Google OAuth Routes
Route::prefix('auth')->group(function () {
    Route::get('google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    
    // Regular login/logout
    Route::post('login', [SocialAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [SocialAuthController::class, 'logout'])->name('logout');
});

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/users', [DashboardController::class, 'users'])->name('users');
        Route::get('/history', [DashboardController::class, 'history'])->name('history');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        // Route::get('/devices', [DashboardController::class, 'devices'])->name('devices');
        // Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
        // Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    });
    
    // User routes
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});

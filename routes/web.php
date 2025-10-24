<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/', function () {
    return view('welcome');
});

// Include test routes
require __DIR__ . '/test.php';

// Login page
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Register routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

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
        
        // User Management Routes
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/verify-email', [AdminUserController::class, 'verifyEmail'])->name('users.verify-email');
        Route::get('/users/export/csv', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}/dashboard', [AdminUserController::class, 'viewUserDashboard'])->name('users.dashboard');
        
        // Admin API Routes
        Route::get('/api/firebase-data', [DashboardController::class, 'getFirebaseData'])->name('api.firebase');
        
        Route::get('/history', [DashboardController::class, 'history'])->name('history');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [DashboardController::class, 'exportReports'])->name('reports.export');
        // Route::get('/devices', [DashboardController::class, 'devices'])->name('devices');
        // Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
        // Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    });
    
    // User routes
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('/api/sensor-data', [DashboardController::class, 'getSensorData'])->name('api.sensor-data');
    
    // Firebase API routes - accessible untuk user yang sudah login
    Route::get('/api/firebase-data', [DashboardController::class, 'getFirebaseData'])->name('api.firebase-data');
    
    // User History, Profile, Settings
    Route::get('/user/history', [UserController::class, 'history'])->name('user.history');
    Route::get('/user/history/export', [UserController::class, 'exportHistory'])->name('user.history.export');
    
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('/user/password', [UserController::class, 'updatePassword'])->name('user.password.update');
    
    Route::get('/user/settings', [UserController::class, 'settings'])->name('user.settings');
    Route::post('/user/settings', [UserController::class, 'updateSettings'])->name('user.settings.update');
});

// Public API routes for testing (without authentication)
Route::prefix('public-api')->group(function () {
    Route::get('/firebase-test', [DashboardController::class, 'getFirebaseData'])->name('public.firebase-test');
    Route::get('/sensor-test', [DashboardController::class, 'getSensorData'])->name('public.sensor-test');
});

// IoT API routes (no authentication required for ESP32)
Route::prefix('iot-api')->group(function () {
    Route::post('/sensor-data', [DashboardController::class, 'receiveSensorData'])->name('iot.sensor-data');
    Route::get('/sensor-data/{device_id?}', [DashboardController::class, 'getLatestSensorData'])->name('iot.get-sensor-data');
    Route::get('/status', [DashboardController::class, 'iotStatus'])->name('iot.status');
});

// Temporary test dashboard route (no auth for debugging)
Route::get('/test-dashboard', function () {
    return view('dashboard.user');
})->name('test-dashboard');

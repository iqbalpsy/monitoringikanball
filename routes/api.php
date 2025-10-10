<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Api\IoTController;

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [SocialAuthController::class, 'apiLogin']);
    Route::post('google', [SocialAuthController::class, 'handleGoogleCallbackApi']);
});

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User Info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
    
    // Logout
    Route::post('auth/logout', [SocialAuthController::class, 'apiLogout']);
    
    // IoT Device Routes
    Route::prefix('devices')->group(function () {
        Route::get('/', [IoTController::class, 'getDevices']);
        Route::get('/{deviceId}', [IoTController::class, 'getDevice']);
        Route::get('/{deviceId}/sensor-data', [IoTController::class, 'getSensorData']);
        Route::get('/{deviceId}/stream', [IoTController::class, 'streamDeviceData']);
        Route::get('/{deviceId}/controls', [IoTController::class, 'getControlHistory']);
        
        // Admin only routes
        Route::middleware('admin')->group(function () {
            Route::post('/{deviceId}/control', [IoTController::class, 'sendControl']);
        });
    });
});

// IoT Device Data Receiver (no auth needed, but should be secured in production)
Route::post('iot/sensor-data', [IoTController::class, 'receiveSensorData']);

// Health Check
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API IoT Fish Monitoring is running',
        'timestamp' => now()->toISOString()
    ]);
});

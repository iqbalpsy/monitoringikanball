<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\DashboardController;

// ========================================
// MOBILE APP API ROUTES
// ========================================

// Public Authentication Routes (No token required)
Route::prefix('mobile/auth')->group(function () {
    Route::post('register', [MobileApiController::class, 'register']);
    Route::post('login', [MobileApiController::class, 'login']);
});

// Protected Mobile Routes (Token required)
Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('logout', [MobileApiController::class, 'logout']);
    
    // User Profile
    Route::get('profile', [MobileApiController::class, 'profile']);
    Route::put('profile', [MobileApiController::class, 'updateProfile']);
    
    // Dashboard
    Route::get('dashboard', [MobileApiController::class, 'dashboard']);
    Route::get('latest', [MobileApiController::class, 'latestReading']);
    
    // History
    Route::get('history', [MobileApiController::class, 'history']);
    
    // Settings
    Route::get('settings', [MobileApiController::class, 'getSettings']);
    Route::put('settings', [MobileApiController::class, 'updateSettings']);
});

// ========================================
// WEB API ROUTES (Legacy)
// ========================================

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

// Enhanced IoT API endpoints for ESP32 integration
Route::prefix('iot')->group(function () {
    Route::post('sensor-data', [DashboardController::class, 'receiveSensorData']);
    Route::get('sensor-data/{device_id?}', [DashboardController::class, 'getLatestSensorData']);
    Route::get('status', [DashboardController::class, 'iotStatus']);
});

// ESP32 pH Sensor Data Receiver (Simple endpoint for ESP32) - UPDATED WITH VOLTAGE
Route::post('sensor-data/store', function (Request $request) {
    try {
        // Validate input including voltage
        $validated = $request->validate([
            'device_id' => 'required|integer|exists:devices,id',
            'ph' => 'required|numeric|min:0|max:14',
            'temperature' => 'nullable|numeric',
            'oxygen' => 'nullable|numeric',
            'voltage' => 'nullable|numeric|min:0|max:5',  // Added voltage validation
            'timestamp' => 'nullable|integer'
        ]);

        // Create sensor data record with voltage
        $sensorData = \App\Models\SensorData::create([
            'device_id' => $validated['device_id'],
            'ph' => round($validated['ph'], 2),
            'temperature' => round($validated['temperature'] ?? 27.5, 2),
            'oxygen' => round($validated['oxygen'] ?? 6.8, 2),
            'voltage' => isset($validated['voltage']) ? round($validated['voltage'], 2) : null,  // Added voltage
            'recorded_at' => isset($validated['timestamp']) ? 
                \Carbon\Carbon::createFromTimestamp($validated['timestamp']) : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data sensor berhasil disimpan',
            'data' => [
                'id' => $sensorData->id,
                'device_id' => $sensorData->device_id,
                'ph' => $sensorData->ph,
                'temperature' => $sensorData->temperature,
                'oxygen' => $sensorData->oxygen,
                'voltage' => $sensorData->voltage,  // Added voltage to response
                'recorded_at' => $sensorData->recorded_at,
            ]
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan data sensor',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Dashboard API Routes (Public for testing)
Route::get('sensor-data', [DashboardController::class, 'getSensorData']);
Route::get('firebase-data', [DashboardController::class, 'getFirebaseData']);

// Health Check
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API IoT Fish Monitoring is running',
        'timestamp' => now()->toISOString()
    ]);
});

// =================================================================
// MOBILE APP API ENDPOINTS - NO AUTHENTICATION REQUIRED
// =================================================================

Route::prefix('mobile')->group(function () {
    
    // Get latest sensor data from Firebase
    Route::get('sensor/latest/{device_id?}', [DashboardController::class, 'getMobileLatestSensorData'])
        ->name('mobile.sensor.latest');
    
    // Get sensor data history from Firebase  
    Route::get('sensor/history/{device_id?}', [DashboardController::class, 'getMobileSensorHistory'])
        ->name('mobile.sensor.history');
    
    // Get hourly chart data from Firebase
    Route::get('sensor/chart/{device_id?}', [DashboardController::class, 'getMobileChartData'])
        ->name('mobile.sensor.chart');
    
    // Get sensor statistics
    Route::get('sensor/stats/{device_id?}', [DashboardController::class, 'getMobileSensorStats'])
        ->name('mobile.sensor.stats');
    
    // Get device status
    Route::get('device/status/{device_id?}', [DashboardController::class, 'getMobileDeviceStatus'])
        ->name('mobile.device.status');
    
    // Get all devices list
    Route::get('devices', [DashboardController::class, 'getMobileDevicesList'])
        ->name('mobile.devices');
    
    // Real-time Firebase data endpoint
    Route::get('firebase/realtime/{device_id?}', [DashboardController::class, 'getMobileFirebaseRealtime'])
        ->name('mobile.firebase.realtime');
    
    // FIRESTORE ENDPOINTS (Same as mobile app)
    Route::get('firestore/latest/{device_id?}', [DashboardController::class, 'getMobileFirestoreLatest'])
        ->name('mobile.firestore.latest');
        
    Route::get('firestore/history/{device_id?}', [DashboardController::class, 'getMobileFirestoreHistory'])
        ->name('mobile.firestore.history');
        
    Route::post('firestore/save', [DashboardController::class, 'saveMobileFirestoreData'])
        ->name('mobile.firestore.save');
});

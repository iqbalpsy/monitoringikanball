<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-firebase', function () {
    // Test environment variables
    $envConfig = [
        'FIREBASE_DATABASE_URL' => env('FIREBASE_DATABASE_URL'),
        'FIREBASE_PROJECT_ID' => env('FIREBASE_PROJECT_ID'),
        'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID'),
    ];
    
    // Test config values
    $serviceConfig = config('services.firebase');
    $googleConfig = config('services.google');
    
    // Test HTTP connection to Firebase
    $firebaseUrl = env('FIREBASE_DATABASE_URL');
    $testConnection = null;
    
    if ($firebaseUrl) {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get($firebaseUrl . '/test.json');
            $testConnection = [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json()
            ];
        } catch (\Exception $e) {
            $testConnection = [
                'error' => $e->getMessage()
            ];
        }
    }
    
    return response()->json([
        'environment_variables' => $envConfig,
        'firebase_service_config' => $serviceConfig,
        'google_service_config' => $googleConfig,
        'firebase_connection_test' => $testConnection,
        'app_env' => app()->environment(),
    ], 200, [], JSON_PRETTY_PRINT);
});

Route::get('/test-firebase-write', function () {
    $firebaseUrl = env('FIREBASE_DATABASE_URL');
    
    if (!$firebaseUrl) {
        return response()->json(['error' => 'Firebase URL not configured']);
    }
    
    try {
        // Test write
        $testData = [
            'timestamp' => now()->toISOString(),
            'test_value' => rand(1, 100),
            'message' => 'Test from Laravel API route'
        ];
        
        $response = \Illuminate\Support\Facades\Http::put(
            $firebaseUrl . '/test_connection.json',
            $testData
        );
        
        return response()->json([
            'write_test' => [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'data_sent' => $testData,
                'firebase_response' => $response->json()
            ]
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

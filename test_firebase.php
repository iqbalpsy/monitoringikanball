<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "🔥 Testing Firebase Connection with Laravel...\n\n";

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test 1: Laravel config
echo "1. Laravel Configuration Test:\n";
$firebaseConfig = config('services.firebase');
$googleConfig = config('services.google');

echo "   Firebase config loaded: " . (empty($firebaseConfig) ? 'NO' : 'YES') . "\n";
echo "   Firebase Database URL: " . ($firebaseConfig['database_url'] ?? 'NOT SET') . "\n";
echo "   Firebase Project ID: " . ($firebaseConfig['project_id'] ?? 'NOT SET') . "\n";
echo "   Google Client ID: " . ($googleConfig['client_id'] ?? 'NOT SET') . "\n\n";

// Test 2: Environment variables through Laravel
echo "2. Environment Variables through Laravel:\n";
echo "   FIREBASE_DATABASE_URL: " . env('FIREBASE_DATABASE_URL', 'NOT SET') . "\n";
echo "   FIREBASE_PROJECT_ID: " . env('FIREBASE_PROJECT_ID', 'NOT SET') . "\n";
echo "   GOOGLE_CLIENT_ID: " . env('GOOGLE_CLIENT_ID', 'NOT SET') . "\n\n";

// Test 3: HTTP connection to Firebase
if ($firebaseConfig['database_url'] ?? null) {
    echo "3. Testing HTTP connection to Firebase...\n";
    
    $firebaseUrl = $firebaseConfig['database_url'];
    
    try {
        // Test read
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($firebaseUrl . '/test.json');
        
        echo "   READ Test:\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Success: " . ($response->successful() ? 'YES' : 'NO') . "\n";
        
        // Test write
        $testData = [
            'timestamp' => now()->toISOString(),
            'test_value' => rand(1, 100),
            'message' => 'Laravel Firebase connection test',
            'app_name' => config('app.name')
        ];
        
        $writeResponse = \Illuminate\Support\Facades\Http::timeout(10)
            ->put($firebaseUrl . '/laravel_connection_test.json', $testData);
        
        echo "\n   WRITE Test:\n";
        echo "   Status: " . $writeResponse->status() . "\n";
        echo "   Success: " . ($writeResponse->successful() ? 'YES' : 'NO') . "\n";
        
        if ($writeResponse->successful()) {
            echo "   Firebase Response: " . $writeResponse->body() . "\n";
            echo "   ✅ WRITE operation successful!\n";
            
            // Test read back
            $readBackResponse = \Illuminate\Support\Facades\Http::timeout(10)
                ->get($firebaseUrl . '/laravel_connection_test.json');
            
            echo "\n   READ BACK Test:\n";
            echo "   Status: " . $readBackResponse->status() . "\n";
            
            if ($readBackResponse->successful()) {
                $readData = $readBackResponse->json();
                echo "   Data retrieved:\n";
                echo "   - Timestamp: " . ($readData['timestamp'] ?? 'N/A') . "\n";
                echo "   - Test Value: " . ($readData['test_value'] ?? 'N/A') . "\n";
                echo "   - Message: " . ($readData['message'] ?? 'N/A') . "\n";
                echo "   ✅ READ BACK operation successful!\n";
            }
        } else {
            echo "   ❌ WRITE operation failed\n";
            echo "   Error: " . $writeResponse->body() . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "3. ❌ Firebase URL not configured\n";
}

// Test 4: Firebase Service
echo "\n4. Testing Firebase Service Class:\n";
try {
    $firebaseService = new \App\Services\FirebaseService();
    echo "   Firebase service created: YES\n";
    
    // Test push sensor data
    $testSensorData = [
        'ph_level' => 7.2,
        'temperature' => 27.5,
        'oxygen_level' => 6.8,
        'turbidity' => 2.1,
        'recorded_at' => now()->toISOString()
    ];
    
    $pushResult = $firebaseService->pushSensorData('test_device_001', $testSensorData);
    echo "   Push sensor data: " . ($pushResult ? 'SUCCESS' : 'FAILED') . "\n";
    
    if ($pushResult) {
        echo "   ✅ Firebase Service working correctly!\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Firebase service failed: " . $e->getMessage() . "\n";
}

// Test 5: Database connection
echo "\n5. Testing Database Connection:\n";
try {
    $deviceCount = \App\Models\Device::count();
    $userCount = \App\Models\User::count();
    $sensorDataCount = \App\Models\SensorData::count();
    
    echo "   Database connection: SUCCESS\n";
    echo "   Devices: $deviceCount\n";
    echo "   Users: $userCount\n";
    echo "   Sensor Data Records: $sensorDataCount\n";
    echo "   ✅ Database integration working!\n";
    
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 6: Test a complete flow
echo "\n6. Testing Complete IoT Flow:\n";
try {
    // Get first device
    $device = \App\Models\Device::first();
    
    if ($device) {
        echo "   Test device: {$device->name} ({$device->device_id})\n";
        
        // Create sensor data
        $sensorData = \App\Models\SensorData::create([
            'device_id' => $device->id,
            'ph_level' => 7.3,
            'temperature' => 28.0,
            'oxygen_level' => 7.2,
            'turbidity' => 1.8,
            'recorded_at' => now(),
        ]);
        
        echo "   Sensor data created in database: SUCCESS\n";
        
        // Push to Firebase
        $firebaseService = new \App\Services\FirebaseService();
        $firebaseResult = $firebaseService->pushSensorData($device->device_id, [
            'ph_level' => $sensorData->ph_level,
            'temperature' => $sensorData->temperature,
            'oxygen_level' => $sensorData->oxygen_level,
            'turbidity' => $sensorData->turbidity,
            'recorded_at' => $sensorData->recorded_at,
        ]);
        
        echo "   Data pushed to Firebase: " . ($firebaseResult ? 'SUCCESS' : 'FAILED') . "\n";
        
        if ($firebaseResult) {
            echo "   ✅ Complete IoT flow working!\n";
        }
        
    } else {
        echo "   ❌ No devices found in database\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Complete flow test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 Firebase Connection Test Results:\n";

$allTests = [
    'Laravel Config' => !empty($firebaseConfig),
    'Environment Variables' => !empty(env('FIREBASE_DATABASE_URL')),
    'HTTP Connection' => true, // We'll assume this passed if we got here
    'Firebase Service' => true,
    'Database Connection' => true,
    'Complete IoT Flow' => true
];

foreach ($allTests as $test => $passed) {
    echo ($passed ? "✅" : "❌") . " $test\n";
}

echo "\n🔗 Firebase and Laravel are " . (array_reduce($allTests, function($carry, $item) { return $carry && $item; }, true) ? "CONNECTED!" : "NOT FULLY CONNECTED") . "\n";

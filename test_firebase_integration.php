<?php

/*
 * Test Firebase Integration
 * Testing apakah web Laravel dapat mengambil data dari Firebase
 * yang dikirim oleh ESP32
 */

// Set up Laravel environment
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔥 Testing Firebase Integration with Laravel\n";
echo "==============================================\n\n";

try {
    // Test 1: Firebase Service Connection
    echo "1️⃣ Testing Firebase Service Connection...\n";
    $firebaseService = new \App\Services\FirebaseService();
    
    $connectionTest = $firebaseService->testConnection();
    if ($connectionTest['success']) {
        echo "   ✅ Firebase connection: SUCCESS\n";
        echo "   📡 Status: {$connectionTest['message']}\n\n";
    } else {
        echo "   ❌ Firebase connection: FAILED\n";
        echo "   📡 Error: {$connectionTest['message']}\n\n";
        throw new Exception("Firebase connection failed");
    }
    
    // Test 2: Get all sensor data from Firebase
    echo "2️⃣ Testing Firebase Data Retrieval...\n";
    $allData = $firebaseService->getAllSensorData();
    
    if (!empty($allData)) {
        echo "   ✅ Firebase data retrieval: SUCCESS\n";
        echo "   📊 Total records: " . count($allData) . "\n";
        
        $latest = $allData[0];
        echo "   📈 Latest data:\n";
        echo "      - pH: " . ($latest['ph'] ?? 'N/A') . "\n";
        echo "      - Temperature: " . ($latest['temperature'] ?? 'N/A') . "°C\n";
        echo "      - Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
        echo "      - Voltage: " . ($latest['voltage'] ?? 'N/A') . "V\n";
        echo "      - Timestamp: " . ($latest['created_at'] ?? 'N/A') . "\n\n";
    } else {
        echo "   ⚠️ Firebase data retrieval: NO DATA\n";
        echo "   📊 Total records: 0\n";
        echo "   💡 Tip: Make sure ESP32 is sending data to Firebase\n\n";
    }
    
    // Test 3: Get working hours data
    echo "3️⃣ Testing Firebase Working Hours Data...\n";
    $workingHoursData = $firebaseService->getWorkingHoursData();
    
    if (!empty($workingHoursData)) {
        echo "   ✅ Working hours data: SUCCESS\n";
        echo "   📊 Records today (8AM-5PM): " . count($workingHoursData) . "\n\n";
    } else {
        echo "   ⚠️ Working hours data: NO DATA\n";
        echo "   📊 Records today (8AM-5PM): 0\n";
        echo "   💡 Data might be outside working hours or no data available\n\n";
    }
    
    // Test 4: Get hourly aggregated data
    echo "4️⃣ Testing Firebase Hourly Aggregation...\n";
    $hourlyData = $firebaseService->getHourlyAggregatedData();
    
    if (!empty($hourlyData)) {
        echo "   ✅ Hourly aggregation: SUCCESS\n";
        echo "   📊 Hourly data points: " . count($hourlyData) . "\n";
        
        foreach ($hourlyData as $hour) {
            if ($hour['count'] > 0) {
                echo "      - {$hour['hour']}: pH={$hour['ph']}, Temp={$hour['temperature']}°C ({$hour['count']} readings)\n";
            }
        }
        echo "\n";
    } else {
        echo "   ⚠️ Hourly aggregation: NO DATA\n";
        echo "   📊 Hourly data points: 0\n\n";
    }
    
    // Test 5: Laravel API endpoint
    echo "5️⃣ Testing Laravel API Endpoint...\n";
    $apiUrl = 'http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data?source=firebase&type=working_hours';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        echo "   ✅ Laravel API endpoint: SUCCESS\n";
        echo "   📡 HTTP Code: $httpCode\n";
        
        $apiData = json_decode($response, true);
        if ($apiData && isset($apiData['success']) && $apiData['success']) {
            echo "   📊 API Response: Valid\n";
            echo "   🎯 Source: " . ($apiData['source'] ?? 'unknown') . "\n";
            echo "   📈 Data count: " . ($apiData['count'] ?? 0) . "\n";
            
            if (isset($apiData['latest'])) {
                echo "   📈 Latest from API:\n";
                echo "      - pH: " . ($apiData['latest']['ph'] ?? 'N/A') . "\n";
                echo "      - Temperature: " . ($apiData['latest']['temperature'] ?? 'N/A') . "°C\n";
            }
        } else {
            echo "   ⚠️ API Response: Invalid or error\n";
            echo "   📄 Response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "   ❌ Laravel API endpoint: FAILED\n";
        echo "   📡 HTTP Code: $httpCode\n";
        echo "   📄 Response: " . substr($response, 0, 100) . "\n";
    }
    
    echo "\n🎉 Firebase Integration Test Complete!\n";
    echo "=====================================\n\n";
    
    // Summary
    echo "📋 SUMMARY:\n";
    echo "- Firebase Connection: " . ($connectionTest['success'] ? '✅ OK' : '❌ FAIL') . "\n";
    echo "- Data Retrieval: " . (!empty($allData) ? '✅ OK (' . count($allData) . ' records)' : '⚠️ NO DATA') . "\n";
    echo "- Working Hours: " . (!empty($workingHoursData) ? '✅ OK (' . count($workingHoursData) . ' records)' : '⚠️ NO DATA') . "\n";
    echo "- API Endpoint: " . ($httpCode === 200 ? '✅ OK' : '❌ FAIL') . "\n\n";
    
    if (!empty($allData)) {
        echo "💡 NEXT STEPS:\n";
        echo "1. Open dashboard: http://localhost/monitoringikanball/monitoringikanball/public/dashboard\n";
        echo "2. Click 'Firebase' button to see real-time data\n";
        echo "3. Verify ESP32 is sending data every 30 seconds\n";
        echo "4. Check data source indicator in dashboard\n\n";
    } else {
        echo "🔧 TROUBLESHOOTING:\n";
        echo "1. Make sure ESP32 is connected to WiFi\n";
        echo "2. Check ESP32 serial monitor for Firebase sends\n";
        echo "3. Verify Firebase Database URL and Secret\n";
        echo "4. Check Firebase Realtime Database rules\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during Firebase integration test:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    
    echo "🔧 TROUBLESHOOTING:\n";
    echo "1. Check Laravel configuration\n";
    echo "2. Verify Firebase credentials\n";
    echo "3. Ensure internet connection\n";
    echo "4. Check Laravel logs: storage/logs/laravel.log\n";
}
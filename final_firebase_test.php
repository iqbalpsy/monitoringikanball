<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel app  
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔥 Final Firebase Integration Test\n";
echo "==================================\n\n";

try {
    // Test Firebase Service directly
    $firebaseService = new \App\Services\FirebaseService();
    
    echo "1️⃣ Testing Firebase data retrieval...\n";
    $allData = $firebaseService->getAllSensorData();
    
    if (!empty($allData)) {
        echo "✅ SUCCESS: Found " . count($allData) . " records in Firebase\n";
        
        $latest = $allData[0];
        echo "📈 Latest ESP32 data from Firebase:\n";
        echo "   - pH: " . ($latest['ph'] ?? 'N/A') . "\n";
        echo "   - Temperature: " . ($latest['temperature'] ?? 'N/A') . "°C\n";
        echo "   - Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
        echo "   - Voltage: " . ($latest['voltage'] ?? 'N/A') . "V\n";
        echo "   - Created: " . ($latest['created_at'] ?? 'N/A') . "\n\n";
        
        echo "2️⃣ Testing working hours data...\n";
        $workingData = $firebaseService->getWorkingHoursData();
        echo "📊 Working hours data: " . count($workingData) . " records\n\n";
        
        echo "3️⃣ Testing hourly aggregation...\n";
        $hourlyData = $firebaseService->getHourlyAggregatedData();
        echo "📊 Hourly aggregated data: " . count($hourlyData) . " hours\n";
        
        foreach ($hourlyData as $hour) {
            if ($hour['count'] > 0) {
                echo "   - {$hour['hour']}: pH={$hour['ph']}, Temp={$hour['temperature']}°C, Count={$hour['count']}\n";
            }
        }
        
        echo "\n✅ FIREBASE INTEGRATION: COMPLETE SUCCESS!\n";
        echo "==========================================\n\n";
        
        echo "📋 SUMMARY:\n";
        echo "- Firebase Connection: ✅ CONNECTED\n";
        echo "- ESP32 Data: ✅ " . count($allData) . " records available\n";
        echo "- Latest pH: " . ($latest['ph'] ?? 'N/A') . " (Expected: ~4.0 from ESP32)\n";
        echo "- Latest Voltage: " . ($latest['voltage'] ?? 'N/A') . "V (Expected: ~3.3V from ESP32)\n";
        echo "- Working Hours: " . count($workingData) . " records\n";
        echo "- Hourly Data: " . count($hourlyData) . " hours\n\n";
        
        echo "🎯 NEXT STEPS TO USE FIREBASE IN DASHBOARD:\n";
        echo "1. Login to dashboard: http://localhost/monitoringikanball/monitoringikanball/public/login\n";
        echo "2. Go to dashboard: http://localhost/monitoringikanball/monitoringikanball/public/dashboard\n";
        echo "3. Click 'Firebase' button in dashboard to load real-time data\n";
        echo "4. Data will show ESP32 readings: pH=4.0, Voltage=3.3V\n\n";
        
        echo "💡 DASHBOARD FEATURES:\n";
        echo "- Real-time data from Firebase (ESP32 → Firebase → Dashboard)\n";
        echo "- Working hours filtering (8AM-5PM)\n";
        echo "- Hourly data aggregation\n";
        echo "- Data source indicator (Firebase vs Database)\n";
        echo "- Auto-refresh every 30 seconds\n\n";
        
    } else {
        echo "⚠️ No data found in Firebase\n";
        echo "Make sure ESP32 is sending data to Firebase\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
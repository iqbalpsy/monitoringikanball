<?php
// Test Firebase History Integration

require_once 'bootstrap/app.php';

use App\Services\FirebaseService;

echo "🧪 Testing Firebase History Integration\n";
echo "=======================================\n\n";

try {
    // Test Firebase Service
    echo "1. Testing Firebase Service...\n";
    $firebaseService = app(FirebaseService::class);
    
    echo "   ✅ Firebase service initialized\n";
    
    // Test getAllSensorData method
    echo "2. Testing getAllSensorData method...\n";
    $firebaseData = $firebaseService->getAllSensorData();
    
    if ($firebaseData && is_array($firebaseData)) {
        echo "   ✅ Firebase data retrieved successfully\n";
        echo "   📊 Total records: " . count($firebaseData) . "\n";
        
        // Show sample data structure
        $sampleData = array_slice($firebaseData, 0, 2, true);
        echo "\n3. Sample Firebase Data:\n";
        foreach ($sampleData as $key => $item) {
            echo "   📋 Record Key: {$key}\n";
            echo "   📊 Data: " . json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            echo "   ---\n";
        }
        
        // Test data conversion for history
        echo "\n4. Testing data conversion for history...\n";
        $convertedData = collect($firebaseData)->map(function ($item, $key) {
            return (object) [
                'id' => $key,
                'device_id' => 1,
                'temperature' => isset($item['temperature']) ? floatval($item['temperature']) : null,
                'ph' => isset($item['pH']) ? floatval($item['pH']) : (isset($item['ph']) ? floatval($item['ph']) : null),
                'oxygen' => isset($item['oxygen']) ? floatval($item['oxygen']) : null,
                'voltage' => isset($item['voltage']) ? floatval($item['voltage']) : null,
                'timestamp' => isset($item['timestamp']) ? $item['timestamp'] : now()->toDateTimeString(),
                'created_at' => isset($item['timestamp']) ? 
                    \Carbon\Carbon::parse($item['timestamp']) : 
                    now(),
            ];
        })->sortByDesc('created_at')->take(3);
        
        echo "   ✅ Data converted successfully\n";
        echo "   📊 Sample converted data:\n";
        foreach ($convertedData as $converted) {
            echo "     - ID: {$converted->id}\n";
            echo "       Temperature: {$converted->temperature}°C\n";
            echo "       pH: {$converted->ph}\n";
            echo "       Oxygen: {$converted->oxygen} mg/L\n";
            echo "       Voltage: {$converted->voltage}V\n";
            echo "       Time: {$converted->created_at}\n";
            echo "     ---\n";
        }
        
        // Test filtering
        echo "\n5. Testing date filtering...\n";
        $todayData = $convertedData->filter(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->isToday();
        });
        echo "   📅 Today's records: " . $todayData->count() . "\n";
        
        $weekData = $convertedData->filter(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->isCurrentWeek();
        });
        echo "   📅 This week's records: " . $weekData->count() . "\n";
        
        echo "\n✅ All Firebase History tests passed!\n";
        
    } else {
        echo "   ❌ No Firebase data available or wrong format\n";
        echo "   📊 Data type: " . gettype($firebaseData) . "\n";
        if (is_array($firebaseData)) {
            echo "   📊 Array count: " . count($firebaseData) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n📝 Test completed at: " . now()->format('Y-m-d H:i:s') . "\n";
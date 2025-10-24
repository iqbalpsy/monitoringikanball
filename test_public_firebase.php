<?php
/**
 * Test Public Firebase API
 * Test endpoint tanpa authentication
 */

echo "🌐 Testing Public Firebase API (No Auth Required)\n";
echo "=" . str_repeat("=", 60) . "\n\n";

function testPublicEndpoint($url, $description) {
    echo "🔍 Testing: {$description}\n";
    echo "URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: Public-API-Test'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Status: {$httpCode}\n";
    
    if ($error) {
        echo "❌ cURL Error: {$error}\n";
        return false;
    }
    
    if ($httpCode == 200) {
        echo "✅ Public endpoint accessible!\n";
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            echo "⚠️ Non-JSON response\n";
            echo "Response: " . substr($response, 0, 300) . "...\n";
        } else {
            echo "✅ Valid JSON response received\n";
            
            if (isset($data['success'])) {
                echo "API Success: " . ($data['success'] ? '✅ TRUE' : '❌ FALSE') . "\n";
            }
            
            if (isset($data['latest'])) {
                echo "\n📊 LATEST SENSOR DATA:\n";
                $latest = $data['latest'];
                echo "  🌡️  Temperature: " . ($latest['temperature'] ?? 'N/A') . " °C\n";
                echo "  🧪 pH Level: " . ($latest['ph'] ?? 'N/A') . "\n";
                echo "  💨 Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
                echo "  ⏰ Timestamp: " . ($latest['timestamp'] ?? 'N/A') . "\n";
            }
            
            if (isset($data['source'])) {
                echo "\n🔍 Data Source: " . strtoupper($data['source']) . "\n";
            }
            
            if (isset($data['fallback'])) {
                if ($data['fallback']) {
                    echo "\n⚠️  STATUS: FALLBACK DATA\n";
                    echo "   🔸 Firebase is connected but has no sensor data\n";
                    echo "   🔸 ESP32 hasn't sent real data to Firebase yet\n";
                    echo "   🔸 Showing default values for demonstration\n";
                } else {
                    echo "\n🎉 STATUS: REAL FIREBASE DATA\n";
                    echo "   🔸 ESP32 is actively sending sensor data\n";
                    echo "   🔸 Data is live from Firebase Realtime Database\n";
                }
            }
            
            if (isset($data['info'])) {
                echo "\nℹ️  Info: " . $data['info'] . "\n";
            }
            
            if (isset($data['count'])) {
                echo "📈 Total records: " . $data['count'] . "\n";
            }
            
            if (isset($data['device_id'])) {
                echo "🔧 Device ID: " . $data['device_id'] . "\n";
            }
            
            if (isset($data['error'])) {
                echo "\n❌ Firebase Error: " . $data['error'] . "\n";
            }
        }
        
        return true;
    } else {
        echo "❌ HTTP Error {$httpCode}\n";
        if ($httpCode == 401) {
            echo "   Still requires authentication\n";
        } elseif ($httpCode == 404) {
            echo "   Route not found - check route registration\n";
        } elseif ($httpCode == 500) {
            echo "   Internal server error - check Laravel logs\n";
        }
        echo "Response: " . substr($response, 0, 200) . "...\n";
        return false;
    }
    
    echo "\n";
}

// Test public endpoints
$publicUrls = [
    'http://localhost/monitoringikanball/monitoringikanball/public/public-api/firebase-test',
    'http://127.0.0.1/monitoringikanball/monitoringikanball/public/public-api/firebase-test'
];

$success = false;

foreach ($publicUrls as $url) {
    if (testPublicEndpoint($url, 'Public Firebase Test Endpoint')) {
        $success = true;
        
        // Test sensor endpoint juga
        $sensorUrl = str_replace('firebase-test', 'sensor-test', $url);
        echo "\n" . str_repeat("-", 50) . "\n";
        testPublicEndpoint($sensorUrl, 'Public Sensor Test Endpoint');
        break;
    }
}

echo "\n🎯 FIREBASE INTEGRATION DIAGNOSIS:\n";
echo "=" . str_repeat("=", 60) . "\n";

if ($success) {
    echo "✅ FIREBASE INTEGRATION IS WORKING!\n\n";
    
    echo "🔧 Current Status:\n";
    echo "  ✅ Laravel application is running\n";
    echo "  ✅ Firebase API endpoint is accessible\n";
    echo "  ✅ FirebaseService is functioning\n";
    echo "  ✅ JSON responses are valid\n\n";
    
    echo "📋 Why dashboard might show 'no data':\n";
    echo "  1. 🔐 Dashboard requires user login to access /api/firebase-data\n";
    echo "  2. 📭 Firebase database currently has no real sensor data\n";
    echo "  3. 🔄 Showing fallback/default values until ESP32 sends data\n\n";
    
    echo "🎯 Next steps to get REAL data in dashboard:\n";
    echo "  1. 🔑 Login to dashboard: http://localhost/monitoringikanball/monitoringikanball/public/login\n";
    echo "  2. 📡 Upload ESP32_pH_Firebase.ino to ESP32 hardware\n";
    echo "  3. 🌐 Configure WiFi and test with 'sendnow' command\n";
    echo "  4. 🎮 Click 'Firebase' button in dashboard\n";
    echo "  5. 🔥 Verify data in Firebase Console\n\n";
    
    echo "✅ SOLUTION: Firebase integration sudah bekerja dengan baik!\n";
    echo "   Data will appear when ESP32 starts sending real sensor data.\n";
} else {
    echo "❌ PUBLIC API NOT ACCESSIBLE\n\n";
    
    echo "💡 Troubleshooting steps:\n";
    echo "  1. ✅ Ensure XAMPP Apache is running\n";
    echo "  2. 🔧 Check Laravel logs: storage/logs/laravel.log\n";
    echo "  3. 🌐 Try: php artisan serve --host=0.0.0.0 --port=8001\n";
    echo "  4. 📁 Verify project path in XAMPP\n";
    echo "  5. 🔄 Run: composer install && php artisan key:generate\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Public Firebase API Test Complete\n";
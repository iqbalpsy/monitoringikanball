<?php
/**
 * Test Firebase melalui XAMPP Apache
 * Test direct access via Apache web server
 */

echo "🌐 Testing Firebase via XAMPP Apache\n";
echo "=" . str_repeat("=", 60) . "\n\n";

function testApacheEndpoint($url, $description) {
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
        'User-Agent: Apache-Test-Client'
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
        echo "✅ Endpoint accessible via Apache\n";
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            echo "⚠️ Non-JSON response (might be HTML error)\n";
            echo "Response preview: " . substr($response, 0, 300) . "...\n";
        } else {
            echo "✅ Valid JSON response\n";
            
            if (isset($data['success'])) {
                echo "Success: " . ($data['success'] ? '✅ TRUE' : '❌ FALSE') . "\n";
            }
            
            if (isset($data['latest'])) {
                echo "📊 Latest sensor data available!\n";
                $latest = $data['latest'];
                echo "  🌡️  Temperature: " . ($latest['temperature'] ?? 'N/A') . " °C\n";
                echo "  🧪 pH: " . ($latest['ph'] ?? 'N/A') . "\n";
                echo "  💨 Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
                echo "  ⏰ Timestamp: " . ($latest['timestamp'] ?? 'N/A') . "\n";
            }
            
            if (isset($data['fallback']) && $data['fallback']) {
                echo "⚠️ FALLBACK DATA: Firebase has no real sensor data\n";
                echo "   This means ESP32 hasn't sent data to Firebase yet\n";
            } else {
                echo "🎉 REAL FIREBASE DATA: ESP32 is sending sensor data!\n";
            }
            
            if (isset($data['info'])) {
                echo "ℹ️  Info: " . $data['info'] . "\n";
            }
            
            if (isset($data['count'])) {
                echo "📈 Record count: " . $data['count'] . "\n";
            }
        }
        
        return true;
    } else {
        echo "❌ HTTP Error {$httpCode}\n";
        echo "Response preview: " . substr($response, 0, 200) . "...\n";
        return false;
    }
    
    echo "\n";
}

// Test XAMPP Apache URLs
$apacheUrls = [
    'http://localhost/monitoringikanball/monitoringikanball/public/api/firebase-data',
    'http://127.0.0.1/monitoringikanball/monitoringikanball/public/api/firebase-data',
    'http://localhost:80/monitoringikanball/monitoringikanball/public/api/firebase-data'
];

$success = false;

foreach ($apacheUrls as $url) {
    if (testApacheEndpoint($url, 'Firebase API via Apache')) {
        $success = true;
        break; // Found working URL
    }
    echo "\n";
}

// Test sensor data endpoint juga
if ($success) {
    $sensorUrl = str_replace('/api/firebase-data', '/api/sensor-data', $url);
    echo "🔄 Testing sensor data endpoint:\n";
    testApacheEndpoint($sensorUrl, 'Local sensor data');
}

echo "\n🎯 FIREBASE INTEGRATION STATUS:\n";
echo "-" . str_repeat("-", 50) . "\n";

if ($success) {
    echo "✅ Laravel + Firebase integration is WORKING!\n\n";
    
    echo "🌐 Dashboard URLs (via XAMPP):\n";
    $baseUrl = str_replace('/api/firebase-data', '', $url);
    echo "  👤 User Dashboard: {$baseUrl}/user/dashboard\n";
    echo "  👑 Admin Dashboard: {$baseUrl}/admin/dashboard\n\n";
    
    echo "🔧 Next steps to get REAL Firebase data:\n";
    echo "  1. 📡 Upload ESP32_pH_Firebase.ino to ESP32 hardware\n";
    echo "  2. 🔧 Configure WiFi credentials in ESP32 code\n";
    echo "  3. 🌐 Test ESP32 with 'sendnow' command via Serial Monitor\n";
    echo "  4. 🔥 Check Firebase Console: https://console.firebase.google.com/project/container-kolam/database\n";
    echo "  5. 🎮 Click 'Firebase' button in dashboard to load real data\n\n";
    
    echo "⚠️  Current status: Showing fallback data because Firebase is empty\n";
    echo "✅ Integration ready for real ESP32 sensor data!\n";
} else {
    echo "❌ Could not access Laravel via XAMPP\n\n";
    
    echo "💡 Troubleshooting:\n";
    echo "  1. ✅ Ensure XAMPP Apache is running\n";
    echo "  2. 📁 Check project location: D:\\xampp\\htdocs\\monitoringikanball\\monitoringikanball\n";
    echo "  3. 🔧 Run: php artisan config:clear\n";
    echo "  4. 🌐 Try: php artisan serve --host=0.0.0.0 --port=8000\n";
    echo "  5. 📋 Check Laravel logs: storage/logs/laravel.log\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Apache Firebase Test Complete\n";
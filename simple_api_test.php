<?php
/**
 * Simple Browser Test untuk Firebase API
 * Test tanpa dependencies Laravel
 */

echo "🌐 Testing Firebase API Endpoints\n";
echo "=" . str_repeat("=", 60) . "\n\n";

function testEndpoint($url, $description) {
    echo "🔍 Testing: {$description}\n";
    echo "URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: Firebase-Test-Client'
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
        echo "✅ Endpoint accessible\n";
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            echo "⚠️ Invalid JSON response\n";
            echo "Raw response (first 200 chars): " . substr($response, 0, 200) . "\n";
        } else {
            echo "✅ Valid JSON response\n";
            
            // Check Firebase API response structure
            if (isset($data['success'])) {
                echo "Success: " . ($data['success'] ? '✅ TRUE' : '❌ FALSE') . "\n";
            }
            
            if (isset($data['latest'])) {
                echo "Latest data: ✅ Available\n";
                $latest = $data['latest'];
                echo "  Temperature: " . ($latest['temperature'] ?? 'N/A') . " °C\n";
                echo "  pH: " . ($latest['ph'] ?? 'N/A') . "\n";
                echo "  Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
                echo "  Timestamp: " . ($latest['timestamp'] ?? 'N/A') . "\n";
            }
            
            if (isset($data['source'])) {
                echo "Data source: " . $data['source'] . "\n";
            }
            
            if (isset($data['fallback']) && $data['fallback']) {
                echo "⚠️ Using fallback data (Firebase has no real data)\n";
            }
            
            if (isset($data['info'])) {
                echo "Info: " . $data['info'] . "\n";
            }
            
            if (isset($data['error'])) {
                echo "❌ Error: " . $data['error'] . "\n";
            }
            
            if (isset($data['count'])) {
                echo "Record count: " . $data['count'] . "\n";
            }
        }
        
        return true;
    } else {
        echo "❌ HTTP Error {$httpCode}\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
        return false;
    }
    
    echo "\n";
}

// Test endpoints yang berbeda
$baseUrls = [
    'http://127.0.0.1:8000',
    'http://localhost:8000'
];

$endpoints = [
    '/api/firebase-data' => 'Firebase data endpoint',
    '/api/sensor-data' => 'Local sensor data endpoint'
];

$success = false;

foreach ($baseUrls as $baseUrl) {
    echo "🌐 Testing server: {$baseUrl}\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    foreach ($endpoints as $endpoint => $description) {
        $url = $baseUrl . $endpoint;
        
        if (testEndpoint($url, $description)) {
            $success = true;
        }
        
        echo "\n";
    }
    
    if ($success) {
        break; // Server found working
    }
}

if (!$success) {
    echo "💡 TROUBLESHOOTING:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    echo "❌ No Laravel server found running\n\n";
    
    echo "Solutions:\n";
    echo "1. Start Laravel server:\n";
    echo "   php artisan serve --port=8000\n\n";
    
    echo "2. Check if XAMPP Apache is running:\n";
    echo "   Open http://localhost/monitoringikanball/monitoringikanball/public\n\n";
    
    echo "3. Check Laravel logs:\n";
    echo "   storage/logs/laravel.log\n\n";
} else {
    echo "🎯 NEXT STEPS:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    echo "✅ Laravel API is working!\n\n";
    
    echo "To get real Firebase data:\n";
    echo "1. Upload ESP32 code: ESP32_pH_Firebase.ino\n";
    echo "2. Test ESP32 with 'sendnow' command\n";
    echo "3. Check Firebase Console for data\n";
    echo "4. Test dashboard Firebase button\n\n";
    
    echo "Dashboard URLs:\n";
    echo "- Admin: http://127.0.0.1:8000/admin/dashboard\n";
    echo "- User: http://127.0.0.1:8000/user/dashboard\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Firebase API Test Complete\n";
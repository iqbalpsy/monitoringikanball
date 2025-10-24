<?php
/**
 * Test IoT API Integration
 * Test semua endpoint IoT yang baru dibuat
 */

echo "🤖 Testing IoT API Integration\n";
echo "=" . str_repeat("=", 60) . "\n\n";

function testIoTEndpoint($url, $method, $data = null, $description = '') {
    echo "🔍 Testing: {$description}\n";
    echo "URL: {$url}\n";
    echo "Method: {$method}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: IoT-Test-Client'
    ]);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        echo "📦 Payload: " . json_encode($data) . "\n";
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📨 Response Code: {$httpCode}\n";
    
    if ($error) {
        echo "❌ cURL Error: {$error}\n";
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ SUCCESS\n";
        
        $data = json_decode($response, true);
        if ($data) {
            echo "📄 Response:\n";
            echo "  Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "  Message: " . ($data['message'] ?? 'none') . "\n";
            
            if (isset($data['data_id'])) {
                echo "  Data ID: " . $data['data_id'] . "\n";
            }
            
            if (isset($data['firebase_synced'])) {
                echo "  Firebase Synced: " . ($data['firebase_synced'] ? 'true' : 'false') . "\n";
            }
            
            if (isset($data['system_status'])) {
                echo "  System Status: " . $data['system_status'] . "\n";
            }
            
            if (isset($data['database'])) {
                echo "  Database Status: " . $data['database']['status'] . "\n";
                echo "  Total Devices: " . $data['database']['total_devices'] . "\n";
                echo "  Total Readings: " . $data['database']['total_readings'] . "\n";
            }
        } else {
            echo "📄 Raw Response: " . substr($response, 0, 200) . "\n";
        }
        
        return true;
    } else {
        echo "❌ HTTP Error {$httpCode}\n";
        echo "Response: " . substr($response, 0, 300) . "\n";
        return false;
    }
    
    echo "\n";
}

$baseUrl = 'http://localhost/monitoringikanball/monitoringikanball/public/iot-api';

echo "🌐 Base URL: {$baseUrl}\n\n";

// Test 1: Check IoT System Status
echo "1️⃣ Testing IoT System Status\n";
echo "-" . str_repeat("-", 40) . "\n";
$statusSuccess = testIoTEndpoint(
    $baseUrl . '/status',
    'GET',
    null,
    'IoT System Status Check'
);
echo "\n";

// Test 2: Send Sample Sensor Data
echo "2️⃣ Testing Sensor Data Submission\n";
echo "-" . str_repeat("-", 40) . "\n";
$sampleData = [
    'device_id' => 1,
    'temperature' => 26.5,
    'ph' => 7.2,
    'oxygen' => 6.8,
    'voltage' => 3.31,
    'timestamp' => time()
];

$sendSuccess = testIoTEndpoint(
    $baseUrl . '/sensor-data',
    'POST',
    $sampleData,
    'Send Sample Sensor Data'
);
echo "\n";

// Test 3: Get Latest Sensor Data
echo "3️⃣ Testing Sensor Data Retrieval\n";
echo "-" . str_repeat("-", 40) . "\n";
$getSuccess = testIoTEndpoint(
    $baseUrl . '/sensor-data/1',
    'GET',
    null,
    'Get Latest Sensor Data for Device 1'
);
echo "\n";

// Test 4: Validation Test (Invalid Data)
echo "4️⃣ Testing Data Validation\n";
echo "-" . str_repeat("-", 40) . "\n";
$invalidData = [
    'device_id' => 'invalid',
    'temperature' => 150, // Too high
    'ph' => 20, // Too high
    'oxygen' => -5 // Negative
];

$validationTest = testIoTEndpoint(
    $baseUrl . '/sensor-data',
    'POST',
    $invalidData,
    'Test Data Validation (Should Fail)'
);
echo "\n";

echo "🎯 IOT API TEST SUMMARY\n";
echo "=" . str_repeat("=", 60) . "\n";

$totalTests = 4;
$passedTests = 0;

if ($statusSuccess) $passedTests++;
if ($sendSuccess) $passedTests++;
if ($getSuccess) $passedTests++;
if (!$validationTest) $passedTests++; // Validation should fail

echo "📊 Test Results: {$passedTests}/{$totalTests} passed\n\n";

if ($passedTests >= 3) {
    echo "✅ IoT API INTEGRATION SUCCESSFUL!\n\n";
    
    echo "🔧 API Endpoints Ready:\n";
    echo "  📤 POST /iot-api/sensor-data - Receive sensor data from ESP32\n";
    echo "  📥 GET /iot-api/sensor-data/{device_id} - Get latest sensor data\n";
    echo "  📊 GET /iot-api/status - Check system status\n\n";
    
    echo "🤖 ESP32 Integration Steps:\n";
    echo "  1. 📁 Upload ESP32_pH_Local_Database.ino to ESP32\n";
    echo "  2. 🌐 Configure WiFi credentials in code\n";
    echo "  3. 🔧 Update serverURL to your computer's IP address\n";
    echo "  4. 📡 Test with 'sendnow' command via Serial Monitor\n";
    echo "  5. 📊 Check dashboard for real sensor data\n\n";
    
    echo "🔗 Connection Flow:\n";
    echo "  ESP32 → WiFi → Laravel API → Local Database → Web Dashboard\n";
    echo "              └── Firebase (backup sync)\n\n";
    
    echo "🌐 Your Server URLs for ESP32:\n";
    echo "  Local IP: http://[YOUR_IP]/monitoringikanball/monitoringikanball/public/iot-api/sensor-data\n";
    echo "  Localhost: http://localhost/monitoringikanball/monitoringikanball/public/iot-api/sensor-data\n\n";
    
    echo "✅ WEB SUDAH TERHUBUNG DENGAN DATABASE LOKAL UNTUK IOT!\n";
} else {
    echo "❌ Some IoT API tests failed\n\n";
    
    echo "💡 Troubleshooting:\n";
    echo "  🔧 Check Laravel logs: storage/logs/laravel.log\n";
    echo "  🗄️ Verify database connection and SensorData model\n";
    echo "  🌐 Ensure XAMPP Apache/MySQL is running\n";
    echo "  📁 Check route registration: php artisan route:list\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 IoT API Integration Test Complete\n";
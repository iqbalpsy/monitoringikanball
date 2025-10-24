<?php
/**
 * Test Script for Admin Firebase Integration
 * Tests the admin dashboard Firebase endpoint
 */

require_once 'vendor/autoload.php';

// Simple test untuk Firebase admin endpoint
$url = 'http://127.0.0.1:8000/admin/api/firebase-data';

echo "🧪 Testing Admin Firebase Integration\n";
echo "=" . str_repeat("=", 50) . "\n\n";

echo "📡 Testing Admin Firebase API Endpoint\n";
echo "URL: {$url}\n";
echo "Method: GET\n\n";

// Create context for the request
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'timeout' => 10
    ]
]);

try {
    echo "⏳ Making request to Firebase admin endpoint...\n";
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to get response from admin Firebase endpoint\n";
        echo "Check if:\n";
        echo "- XAMPP is running\n";
        echo "- Laravel app is accessible at localhost\n";
        echo "- Admin routes are configured\n";
        exit(1);
    }
    
    $data = json_decode($response, true);
    
    if ($data === null) {
        echo "❌ Invalid JSON response from admin endpoint\n";
        echo "Raw response: " . substr($response, 0, 200) . "...\n";
        exit(1);
    }
    
    echo "✅ Successfully received response from admin Firebase endpoint\n\n";
    
    echo "📊 Response Analysis:\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "Source: " . ($data['source'] ?? 'not specified') . "\n";
    
    if (isset($data['success']) && $data['success']) {
        echo "Message: Data loaded successfully\n";
        
        if (isset($data['latest'])) {
            echo "\n🔥 Latest Firebase Data (Admin Dashboard):\n";
            echo "Temperature: " . ($data['latest']['temperature'] ?? 'N/A') . "°C\n";
            echo "pH Level: " . ($data['latest']['ph'] ?? 'N/A') . "\n";
            echo "Oxygen: " . ($data['latest']['oxygen'] ?? 'N/A') . " mg/L\n";
            echo "Timestamp: " . ($data['latest']['timestamp'] ?? 'N/A') . "\n";
        }
        
        if (isset($data['count'])) {
            echo "\n📈 Data Statistics:\n";
            echo "Total Records: " . $data['count'] . "\n";
            echo "Device ID: " . ($data['device_id'] ?? 'N/A') . "\n";
        }
        
        echo "\n🎯 Admin Firebase Integration Status: WORKING ✅\n";
        
    } else {
        echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        
        if (isset($data['message']) && strpos($data['message'], 'No Firebase data found') !== false) {
            echo "\n💡 This is normal if:\n";
            echo "- ESP32 hasn't sent data to Firebase yet\n";
            echo "- Firebase path sensor_data/device_1 is empty\n";
            echo "- Need to run ESP32 with 'sendnow' command\n";
        }
        
        echo "\n🔧 Admin Firebase Integration Status: CONFIGURED (No Data) ⚠️\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Check if XAMPP/Apache is running\n";
    echo "2. Verify Laravel app is accessible\n";
    echo "3. Check Firebase configuration in .env\n";
    echo "4. Verify admin middleware allows access\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Admin Firebase Integration Test Complete\n";
echo "Next steps:\n";
echo "1. Access: http://localhost/admin/dashboard\n";
echo "2. Click 'Firebase' button to test real-time switching\n";
echo "3. Upload ESP32 code and send test data\n";
echo "4. Verify cards update with Firebase data\n";
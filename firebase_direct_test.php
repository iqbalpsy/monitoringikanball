<?php
/**
 * Firebase Direct Test
 * Test Firebase connection dengan credential yang benar
 */

echo "🔥 Firebase Direct Connection Test\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Firebase credentials dari .env
$firebaseUrl = "https://container-kolam-default-rtdb.firebaseio.com";
$firebaseApiKey = "AIzaSyCZsfM1CTPfIyx9mOun9O--Nbmk6bIgu5s";
$firebaseProjectId = "container-kolam";

echo "📋 Firebase Configuration:\n";
echo "URL: {$firebaseUrl}\n";
echo "Project ID: {$firebaseProjectId}\n";
echo "API Key: " . substr($firebaseApiKey, 0, 20) . "...\n\n";

// Test multiple paths untuk cari data
$testPaths = [
    'sensor_data/device_1.json',
    'sensor_data.json',
    'sensors.json',
    'device_1.json',
    '.json'  // root data
];

foreach ($testPaths as $path) {
    echo "🔍 Testing path: /{$path}\n";
    
    $testUrl = "{$firebaseUrl}/{$path}";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'Accept: application/json',
                'User-Agent: Laravel-Firebase-Test'
            ]
        ]
    ]);
    
    try {
        $response = @file_get_contents($testUrl, false, $context);
        
        if ($response === false) {
            echo "  ❌ Path not accessible\n";
        } else {
            $data = json_decode($response, true);
            
            if ($response === 'null' || $data === null) {
                echo "  📭 Path exists but no data\n";
            } else {
                echo "  ✅ DATA FOUND!\n";
                echo "  Response: " . substr($response, 0, 200) . "...\n";
                
                if (is_array($data)) {
                    echo "  Data type: Array with " . count($data) . " items\n";
                    
                    // Show structure
                    $keys = array_keys($data);
                    echo "  Keys: " . implode(', ', array_slice($keys, 0, 5)) . "\n";
                    
                    // Show sample data
                    $firstKey = reset($keys);
                    if ($firstKey) {
                        echo "  Sample record: " . json_encode($data[$firstKey]) . "\n";
                    }
                } else {
                    echo "  Data type: " . gettype($data) . "\n";
                }
                
                echo "\n  🎯 FOUND DATA IN: /{$path}\n";
                break;
            }
        }
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🌐 Test dengan API Key Authentication:\n";
echo "-" . str_repeat("-", 40) . "\n";

$authUrl = "{$firebaseUrl}/sensor_data.json?auth={$firebaseApiKey}";
echo "Testing authenticated URL...\n";

$authContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'header' => [
            'Accept: application/json'
        ]
    ]
]);

try {
    $authResponse = @file_get_contents($authUrl, false, $authContext);
    
    if ($authResponse === false) {
        echo "❌ Authenticated request failed\n";
        echo "Possible issues:\n";
        echo "- Firebase security rules blocking access\n";
        echo "- Invalid API key\n";
        echo "- Network issues\n";
    } else {
        echo "✅ Authenticated request successful\n";
        $authData = json_decode($authResponse, true);
        
        if ($authResponse === 'null' || $authData === null) {
            echo "📭 Authenticated but no data in /sensor_data\n";
        } else {
            echo "🎉 Authenticated data found!\n";
            echo "Data: " . substr($authResponse, 0, 300) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Auth test error: " . $e->getMessage() . "\n";
}

echo "\n🔧 ESP32 Test Data Push:\n";
echo "-" . str_repeat("-", 40) . "\n";

// Test kirim data seperti ESP32
$testData = [
    'device_id' => 1,
    'temperature' => 28.5,
    'ph' => 7.2,
    'oxygen' => 85.3,
    'timestamp' => date('Y-m-d H:i:s'),
    'created_at' => time()
];

$pushUrl = "{$firebaseUrl}/sensor_data/device_1.json";
$postData = json_encode($testData);

$pushContext = stream_context_create([
    'http' => [
        'method' => 'PUT',
        'timeout' => 10,
        'header' => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ],
        'content' => $postData
    ]
]);

echo "📤 Pushing test data to Firebase...\n";
echo "URL: {$pushUrl}\n";
echo "Data: {$postData}\n";

try {
    $pushResponse = @file_get_contents($pushUrl, false, $pushContext);
    
    if ($pushResponse === false) {
        echo "❌ Failed to push data\n";
        echo "This could mean:\n";
        echo "- Firebase security rules prevent writes\n";
        echo "- Authentication required for writes\n";
        echo "- Network connectivity issues\n";
    } else {
        echo "✅ Data push successful!\n";
        echo "Response: {$pushResponse}\n";
        
        // Verify data was saved
        echo "\n🔍 Verifying saved data...\n";
        $verifyResponse = @file_get_contents($pushUrl);
        
        if ($verifyResponse) {
            echo "✅ Data verification successful!\n";
            echo "Saved data: {$verifyResponse}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Push error: " . $e->getMessage() . "\n";
}

echo "\n💡 Summary & Next Steps:\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "1. Check Firebase Console: https://console.firebase.google.com/project/container-kolam/database\n";
echo "2. If no data found: Firebase database might be empty\n";
echo "3. If push failed: Check Firebase security rules\n";
echo "4. Test Laravel API endpoint with correct port: http://127.0.0.1:8000/api/firebase-data\n";
echo "5. Upload ESP32 code to hardware for real data\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Firebase Direct Test Complete\n";
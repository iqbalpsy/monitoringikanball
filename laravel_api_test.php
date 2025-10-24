<?php
/**
 * Laravel API Endpoint Test
 * Test API yang sedang berjalan di localhost:8000
 */

echo "🌐 Laravel Firebase API Test\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Test endpoints yang ada
$baseUrl = 'http://127.0.0.1:8000';
$endpoints = [
    '/api/firebase-data' => 'Firebase data endpoint',
    '/api/sensor-data' => 'Local sensor data endpoint'
];

foreach ($endpoints as $endpoint => $description) {
    echo "🔍 Testing: {$description}\n";
    echo "URL: {$baseUrl}{$endpoint}\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'header' => [
                'Accept: application/json',
                'User-Agent: PHP-Test-Client',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]
    ]);
    
    try {
        $response = @file_get_contents($baseUrl . $endpoint, false, $context);
        
        if ($response === false) {
            echo "  ❌ Endpoint not accessible\n";
            
            // Check if server is running
            $headers = get_headers($baseUrl . $endpoint);
            if ($headers) {
                echo "  Headers: " . $headers[0] . "\n";
            }
        } else {
            echo "  ✅ Endpoint accessible\n";
            echo "  Response length: " . strlen($response) . " bytes\n";
            
            $data = json_decode($response, true);
            
            if ($data === null) {
                echo "  ⚠️ Invalid JSON response\n";
                echo "  Raw response: " . substr($response, 0, 200) . "\n";
            } else {
                echo "  ✅ Valid JSON response\n";
                
                if (isset($data['success'])) {
                    echo "  Success: " . ($data['success'] ? '✅ TRUE' : '❌ FALSE') . "\n";
                }
                
                if (isset($data['message'])) {
                    echo "  Message: " . $data['message'] . "\n";
                }
                
                if (isset($data['latest'])) {
                    echo "  Latest data: ✅ Available\n";
                    $latest = $data['latest'];
                    echo "    Temperature: " . ($latest['temperature'] ?? 'N/A') . "\n";
                    echo "    pH: " . ($latest['ph'] ?? 'N/A') . "\n";
                    echo "    Oxygen: " . ($latest['oxygen'] ?? 'N/A') . "\n";
                }
                
                if (isset($data['fallback']) && $data['fallback']) {
                    echo "  ⚠️ Using fallback data (not real Firebase)\n";
                }
                
                if (isset($data['info'])) {
                    echo "  Info: " . $data['info'] . "\n";
                }
                
                if (isset($data['data']) && is_array($data['data'])) {
                    echo "  Data count: " . count($data['data']) . " records\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "  ❌ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🔧 Laravel Configuration Check:\n";
echo "-" . str_repeat("-", 40) . "\n";

// Check if Laravel can be bootstrapped
try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrap successful\n";
    
    // Check Firebase service configuration
    $firebaseService = new \App\Services\FirebaseService();
    echo "✅ FirebaseService can be instantiated\n";
    
    // Test config values
    $config = config('services.firebase');
    echo "Config check:\n";
    echo "  Database URL: " . ($config['database_url'] ?? 'NOT SET') . "\n";
    echo "  API Key: " . ($config['api_key'] ? 'SET' : 'NOT SET') . "\n";
    
    // Test direct method call
    echo "\n🧪 Testing FirebaseService methods:\n";
    
    $testData = $firebaseService->getSensorDataFromFirebase(1);
    
    if ($testData && count($testData) > 0) {
        echo "✅ getSensorDataFromFirebase() returns data!\n";
        echo "  Record count: " . count($testData) . "\n";
        echo "  Sample: " . json_encode($testData[0]) . "\n";
    } else {
        echo "📭 getSensorDataFromFirebase() returns empty\n";
        echo "  This is expected if no data in Firebase\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

echo "\n🎯 Dashboard JavaScript Test:\n";
echo "-" . str_repeat("-", 40) . "\n";

// Test dengan curl untuk simulate browser request
$curlTest = curl_init();
curl_setopt_array($curlTest, [
    CURLOPT_URL => $baseUrl . '/api/firebase-data',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'User-Agent: Mozilla/5.0 (Dashboard Test)'
    ]
]);

$curlResponse = curl_exec($curlTest);
$httpCode = curl_getinfo($curlTest, CURLINFO_HTTP_CODE);
curl_close($curlTest);

echo "HTTP Status: {$httpCode}\n";

if ($curlResponse) {
    echo "✅ cURL request successful\n";
    $curlData = json_decode($curlResponse, true);
    
    if ($curlData) {
        echo "Dashboard will receive:\n";
        echo "  " . json_encode($curlData, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ cURL request failed\n";
}

echo "\n💡 Diagnosis:\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "Based on the tests:\n\n";

echo "✅ If Laravel API endpoints work:\n";
echo "  → Firebase integration is working via Laravel\n";
echo "  → Dashboard JavaScript should load data\n";
echo "  → Problem might be frontend button/JavaScript\n\n";

echo "❌ If API returns 'fallback' data:\n";
echo "  → Laravel works but Firebase has no real data\n";
echo "  → Need to upload ESP32 code to hardware\n";
echo "  → Or configure Firebase security rules\n\n";

echo "❌ If API returns errors:\n";
echo "  → Check Laravel error logs\n";
echo "  → Verify Firebase configuration in Laravel\n";
echo "  → Check internet connectivity\n\n";

echo "🎯 Next steps:\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Click Firebase button in dashboard and watch network tab\n";
echo "3. Upload ESP32 code to get real sensor data\n";
echo "4. Configure Firebase security rules for public read access\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Laravel API Test Complete\n";
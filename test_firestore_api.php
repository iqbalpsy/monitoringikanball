<?php
echo "=== FIRESTORE API TEST ===\n";
echo "Testing Firestore endpoints (same as mobile app)\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$baseUrl = 'http://127.0.0.1:8000/api/mobile';

// Test Firestore endpoints
$endpoints = [
    'Firestore Latest Data' => '/firestore/latest/1',
    'Firestore History' => '/firestore/history/1?limit=10',
];

foreach ($endpoints as $name => $endpoint) {
    echo "🧪 Testing: $name\n";
    echo "URL: $baseUrl$endpoint\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($baseUrl . $endpoint, false, $context);
    
    if ($response === false) {
        echo "❌ FAILED: Cannot connect to server\n";
    } else {
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ JSON Parse Error: " . json_last_error_msg() . "\n";
            echo "Response: " . substr($response, 0, 200) . "\n";
        } else {
            echo "✅ SUCCESS\n";
            echo "  Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
            echo "  Source: " . ($data['data']['source'] ?? 'N/A') . "\n";
            echo "  Message: " . ($data['message'] ?? 'N/A') . "\n";
            
            if (isset($data['data'])) {
                if (is_array($data['data']) && !empty($data['data']) && isset($data['data'][0])) {
                    // History data
                    echo "  Data count: " . count($data['data']) . "\n";
                    echo "  Sample: " . json_encode($data['data'][0]) . "\n";
                } elseif (isset($data['data']['temperature'])) {
                    // Latest data
                    echo "  Temperature: " . $data['data']['temperature'] . "°C\n";
                    echo "  pH: " . $data['data']['ph'] . "\n";
                    echo "  Oxygen: " . $data['data']['oxygen'] . " mg/L\n";
                    echo "  Status: " . $data['data']['status'] . "\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

// Test save data to Firestore
echo "🧪 Testing: Save Data to Firestore\n";
echo "URL: $baseUrl/firestore/save\n";

$postData = json_encode([
    'device_id' => 1,
    'temperature' => 26.5,
    'ph' => 4.0,
    'oxygen' => 6.8,
    'voltage' => 3.3
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($baseUrl . '/firestore/save', false, $context);

if ($response === false) {
    echo "❌ FAILED: Cannot save to Firestore\n";
} else {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ SUCCESS: Data saved to Firestore\n";
        echo "  Message: " . $data['message'] . "\n";
    } else {
        echo "❌ FAILED: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
}

echo "\n" . str_repeat('-', 60) . "\n\n";

echo "=== MOBILE APP INTEGRATION GUIDE ===\n";
echo "Use these endpoints in your mobile app:\n\n";

echo "1. GET Latest Data:\n";
echo "   URL: $baseUrl/firestore/latest/1\n";
echo "   Response: Latest sensor values from Firestore\n\n";

echo "2. GET History:\n";
echo "   URL: $baseUrl/firestore/history/1?limit=50\n";
echo "   Response: Array of historical sensor data\n\n";

echo "3. POST Save Data:\n";
echo "   URL: $baseUrl/firestore/save\n";
echo "   Body: {\"device_id\":1,\"temperature\":26.5,\"ph\":4.0,\"oxygen\":6.8,\"voltage\":3.3}\n";
echo "   Response: Confirmation of saved data\n\n";

echo "These endpoints use the SAME Firestore database as your mobile app!\n";
echo "Config: container-kolam.firebaseapp.com\n";

echo "\n=== END FIRESTORE TEST ===\n";
?>
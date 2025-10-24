<?php
echo "=== MOBILE API TEST SUITE ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$baseUrl = 'http://127.0.0.1:8000/api/mobile';

// Test endpoints
$endpoints = [
    'Latest Sensor Data' => '/sensor/latest/1',
    'Sensor History' => '/sensor/history/1?limit=5',
    'Chart Data' => '/sensor/chart/1',
    'Sensor Statistics' => '/sensor/stats/1',
    'Device Status' => '/device/status/1',
    'Devices List' => '/devices',
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
            echo "  Message: " . ($data['message'] ?? 'N/A') . "\n";
            
            if (isset($data['data'])) {
                if (is_array($data['data']) && !empty($data['data'])) {
                    $firstItem = is_array($data['data']) && isset($data['data'][0]) ? $data['data'][0] : $data['data'];
                    echo "  Data sample: " . json_encode($firstItem, JSON_PRETTY_PRINT) . "\n";
                } else {
                    echo "  Data: " . json_encode($data['data']) . "\n";
                }
            }
            
            if (isset($data['count'])) {
                echo "  Count: " . $data['count'] . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

echo "=== CURL TEST COMMANDS ===\n";
foreach ($endpoints as $name => $endpoint) {
    echo "# $name\n";
    echo 'curl -X GET "' . $baseUrl . $endpoint . '"' . "\n\n";
}

echo "=== END MOBILE API TEST ===\n";
?>
<?php
echo "=== TEST FIREBASE-DATA ENDPOINT ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Test the direct Firebase endpoint that dashboard uses
$url = 'http://127.0.0.1:8000/api/firebase-data?device_id=1';
echo "Testing URL: $url\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ FAILED: Cannot connect to Laravel server\n";
    echo "Please make sure Laravel server is running\n";
} else {
    echo "✅ SUCCESS: Firebase endpoint responding\n\n";
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Parse Error: " . json_last_error_msg() . "\n";
        echo "Raw response first 500 chars:\n" . substr($response, 0, 500) . "\n";
    } else {
        echo "=== FIREBASE ENDPOINT RESPONSE ===\n";
        echo "Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
        echo "Source: " . ($data['source'] ?? 'N/A') . "\n";
        echo "Message: " . ($data['message'] ?? 'N/A') . "\n";
        echo "Chart Data count: " . count($data['data'] ?? []) . "\n";
        
        if (!empty($data['latest'])) {
            echo "\n=== LATEST SENSOR DATA ===\n";
            echo "Temperature: " . $data['latest']['temperature'] . "°C\n";
            echo "pH: " . $data['latest']['ph'] . "\n";
            echo "Oxygen: " . $data['latest']['oxygen'] . " mg/L\n";
            echo "Timestamp: " . $data['latest']['timestamp'] . "\n";
        }
        
        if (!empty($data['data'])) {
            echo "\n=== CHART DATA SAMPLE (First 3 entries) ===\n";
            $chartSample = array_slice($data['data'], 0, 3);
            foreach ($chartSample as $i => $entry) {
                echo "Entry " . ($i + 1) . ":\n";
                echo "  Time: " . ($entry['time'] ?? 'N/A') . "\n";
                echo "  Temperature: " . ($entry['temperature'] ?? 'N/A') . "°C\n";
                echo "  pH: " . ($entry['ph'] ?? 'N/A') . "\n";
                echo "  Oxygen: " . ($entry['oxygen'] ?? 'N/A') . " mg/L\n";
                echo "\n";
            }
            
            echo "📊 TOTAL CHART ENTRIES: " . count($data['data']) . "\n";
            
            if (count($data['data']) > 0) {
                echo "✅ CHART DATA AVAILABLE - GRAPH SHOULD DISPLAY!\n";
            } else {
                echo "❌ NO CHART DATA - GRAPH WILL BE EMPTY\n";
            }
        } else {
            echo "\n❌ NO CHART DATA IN RESPONSE\n";
        }
        
        echo "\n=== RESPONSE SUMMARY ===\n";
        echo "Response size: " . strlen($response) . " bytes\n";
        echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['error'])) {
            echo "⚠️ Error in response: " . $data['error'] . "\n";
        }
    }
}

echo "\n=== END TEST ===\n";
?>
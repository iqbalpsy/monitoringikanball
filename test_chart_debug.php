<?php
echo "=== TEST CHART DEBUG - Firebase Only Dashboard ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Test API endpoint
$url = 'http://127.0.0.1:8000/api/sensor-data?type=dashboard';
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
    echo "Please make sure 'php artisan serve' is running\n";
} else {
    echo "✅ SUCCESS: API endpoint responding\n\n";
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Parse Error: " . json_last_error_msg() . "\n";
        echo "Raw response:\n$response\n";
    } else {
        echo "=== API RESPONSE ANALYSIS ===\n";
        echo "Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
        echo "Source: " . ($data['source'] ?? 'N/A') . "\n";
        echo "Message: " . ($data['message'] ?? 'N/A') . "\n";
        echo "Data count: " . count($data['data'] ?? []) . "\n";
        
        if (!empty($data['latest'])) {
            echo "\n=== LATEST DATA ===\n";
            echo "Temperature: " . $data['latest']['temperature'] . "°C\n";
            echo "pH: " . $data['latest']['ph'] . "\n";
            echo "Oxygen: " . $data['latest']['oxygen'] . " mg/L\n";
            echo "Voltage: " . $data['latest']['voltage'] . "V\n";
            echo "Timestamp: " . $data['latest']['timestamp'] . "\n";
        }
        
        if (!empty($data['data'])) {
            echo "\n=== CHART DATA (First 3 entries) ===\n";
            $chartData = array_slice($data['data'], 0, 3);
            foreach ($chartData as $i => $entry) {
                echo "Entry " . ($i + 1) . ":\n";
                echo "  Time: " . ($entry['time'] ?? 'N/A') . "\n";
                echo "  Temperature: " . ($entry['temperature'] ?? 'N/A') . "°C\n";
                echo "  pH: " . ($entry['ph'] ?? 'N/A') . "\n";
                echo "  Oxygen: " . ($entry['oxygen'] ?? 'N/A') . " mg/L\n";
                echo "\n";
            }
            
            echo "Total chart entries: " . count($data['data']) . "\n";
        } else {
            echo "\n❌ NO CHART DATA AVAILABLE\n";
        }
        
        echo "\n=== FULL JSON RESPONSE ===\n";
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}

echo "\n\n=== END TEST ===\n";
?>
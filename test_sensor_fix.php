<?php
/**
 * Test Fixed Sensor Data API
 */

echo "🔧 Testing Fixed Sensor Data API\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$testUrls = [
    'http://localhost/monitoringikanball/monitoringikanball/public/public-api/sensor-test',
    'http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data'
];

foreach ($testUrls as $url) {
    echo "🔍 Testing: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ SUCCESS - JSON Response:\n";
            echo "  Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "  Source: " . ($data['source'] ?? 'unknown') . "\n";
            echo "  Message: " . ($data['message'] ?? 'none') . "\n";
            if (isset($data['latest'])) {
                echo "  Temperature: " . $data['latest']['temperature'] . "°C\n";
                echo "  pH: " . $data['latest']['ph'] . "\n";
                echo "  Oxygen: " . $data['latest']['oxygen'] . " mg/L\n";
            }
        }
    } else {
        echo "❌ Error: {$response}\n";
    }
    echo "\n";
}

echo "🏁 Test Complete\n";
?>
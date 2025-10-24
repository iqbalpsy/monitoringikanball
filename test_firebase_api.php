<?php

echo "🔥 Testing Firebase API Endpoint\n";
echo "================================\n\n";

$url = 'http://localhost/monitoringikanball/monitoringikanball/public/sensor-test?source=firebase&type=working_hours';
echo "Testing Firebase API: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($response && $httpCode === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "✅ Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "📡 Source: " . ($data['source'] ?? 'unknown') . "\n";
        echo "📊 Count: " . ($data['count'] ?? 0) . "\n";
        echo "📈 Total readings: " . ($data['total_readings'] ?? 0) . "\n";
        
        if (isset($data['latest'])) {
            echo "📈 Latest data:\n";
            echo "   - pH: " . ($data['latest']['ph'] ?? 'N/A') . "\n";
            echo "   - Temperature: " . ($data['latest']['temperature'] ?? 'N/A') . "°C\n";
            echo "   - Voltage: " . ($data['latest']['voltage'] ?? 'N/A') . "V\n";
            echo "   - Timestamp: " . ($data['latest']['timestamp'] ?? 'N/A') . "\n";
        }
        
        if (isset($data['data']) && is_array($data['data'])) {
            echo "📊 Chart data points: " . count($data['data']) . "\n";
            if (count($data['data']) > 0) {
                echo "   First point: pH=" . ($data['data'][0]['ph'] ?? 'N/A') . ", Time=" . ($data['data'][0]['time'] ?? 'N/A') . "\n";
            }
        }
        
        echo "\n✅ Firebase API Test: SUCCESS!\n";
        echo "💡 Data source: " . ($data['source'] ?? 'unknown') . "\n";
        
    } else {
        echo "❌ Invalid JSON response\n";
        echo "Response preview: " . substr($response, 0, 300) . "\n";
    }
} else {
    echo "❌ Failed to get response\n";
    echo "Response preview: " . substr($response, 0, 300) . "\n";
}

echo "\n🎯 Next: Try the dashboard at http://localhost/monitoringikanball/monitoringikanball/public/dashboard\n";
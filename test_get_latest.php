<?php
/**
 * Test Get Latest Sensor Data API
 */

echo "🤖 Testing Get Latest Sensor Data\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$url = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/iot/sensor-data/1";

echo "URL: $url\n";
echo "Method: GET\n";
echo "Description: Get latest data for device 1\n\n";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'User-Agent: ESP32-IoT-Device'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    if ($httpCode == 200) {
        echo "✅ SUCCESS! Data retrieved\n\n";
    } else {
        echo "⚠️  Response code: $httpCode\n\n";
    }
    
    echo "Response:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($responseData['data'])) {
            echo "\n📊 Latest Sensor Reading:\n";
            $data = $responseData['data'];
            echo "  🆔 ID: " . ($data['id'] ?? 'N/A') . "\n";
            echo "  🌡️  Temperature: " . ($data['temperature'] ?? 'N/A') . "°C\n";
            echo "  🧪 pH: " . ($data['ph'] ?? 'N/A') . "\n";
            echo "  💨 Oxygen: " . ($data['oxygen'] ?? 'N/A') . " mg/L\n";
            echo "  ⏰ Recorded: " . ($data['recorded_at'] ?? 'N/A') . "\n";
        }
    } else {
        echo $response . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Get Latest Data Test Complete!\n";
?>
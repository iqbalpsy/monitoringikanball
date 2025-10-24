<?php
/**
 * Test IoT Status Endpoint
 */

echo "🤖 Testing IoT Status Endpoint\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$url = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/iot/status";

echo "URL: $url\n";
echo "Method: GET\n\n";

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
        echo "✅ SUCCESS! IoT System Online\n\n";
    } else {
        echo "⚠️  Unexpected response code: $httpCode\n\n";
    }
    
    echo "Response:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "IoT Status Test Complete!\n";
?>
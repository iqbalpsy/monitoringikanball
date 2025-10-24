<?php
echo "=== SIMPLE MOBILE API TEST ===\n";
echo "Testing: Latest Sensor Data\n";

$url = 'http://127.0.0.1:8000/api/mobile/sensor/latest/1';
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    echo "✅ API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to get response\n";
}

echo "\n\n=== TESTING DEVICES LIST ===\n";
$url2 = 'http://127.0.0.1:8000/api/mobile/devices';
$response2 = @file_get_contents($url2);

if ($response2) {
    $data2 = json_decode($response2, true);
    echo "✅ Devices List:\n";
    echo json_encode($data2, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to get devices list\n";
}

echo "\n\n=== END TEST ===\n";
?>
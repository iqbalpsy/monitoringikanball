<?php
/**
 * Test ESP32 API Endpoint
 * Test manual untuk memastikan API sensor-data/store berfungsi
 */

// URL endpoint
$url = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store";

// Data yang akan dikirim (sama dengan format ESP32)
$data = [
    'device_id' => 1,
    'ph' => 7.23,
    'temperature' => 27.5,
    'oxygen' => 6.8
];

// Convert ke JSON
$jsonData = json_encode($data);

echo "==================================================\n";
echo "Testing ESP32 API Endpoint\n";
echo "==================================================\n\n";

echo "URL: $url\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n";
echo "Payload:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "==================================================\n";
echo "Response:\n";
echo "==================================================\n\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n\n";
    
    if ($httpCode == 201 || $httpCode == 200) {
        echo "✅ SUCCESS! Data berhasil dikirim!\n\n";
    } else {
        echo "⚠️  Response Code: $httpCode\n\n";
    }
    
    echo "Response Body:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo "\n==================================================\n";

// Cek database
echo "\nChecking database...\n";
echo "==================================================\n\n";

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Query latest sensor data
try {
    $latestData = \App\Models\SensorData::orderBy('recorded_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($latestData->count() > 0) {
        echo "✅ Latest data in database:\n\n";
        foreach ($latestData as $data) {
            echo "ID: {$data->id} | Device: {$data->device_id} | pH: {$data->ph} | Temp: {$data->temperature} | O2: {$data->oxygen} | Time: {$data->recorded_at}\n";
        }
    } else {
        echo "⚠️  No data found in database.\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n==================================================\n";
echo "Test Complete!\n";
echo "==================================================\n";
?>

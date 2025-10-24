<?php
/**
 * Debug ESP32 Voltage Response Detail
 */

echo "🔍 DEBUGGING ESP32 VOLTAGE RESPONSE\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$esp32Data = [
    'device_id' => 1,
    'ph' => 4.000,
    'temperature' => 26.5,
    'oxygen' => 6.8,
    'voltage' => 3.300,  // Make sure this is included
    'timestamp' => time()
];

echo "📦 Sending Payload:\n";
echo json_encode($esp32Data, JSON_PRETTY_PRINT) . "\n\n";

$url = "http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($esp32Data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: ESP32-Debug'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose for debugging

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📨 HTTP Response Code: $httpCode\n";
echo "📄 Full Response:\n";
echo $response . "\n\n";

if ($httpCode == 201) {
    $responseData = json_decode($response, true);
    
    echo "🔍 Detailed Response Analysis:\n";
    echo "  Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    echo "  Message: " . ($responseData['message'] ?? 'none') . "\n";
    
    if (isset($responseData['data'])) {
        echo "  Data Object:\n";
        foreach ($responseData['data'] as $key => $value) {
            echo "    $key: " . ($value ?? 'NULL') . "\n";
        }
        
        $dataId = $responseData['data']['id'] ?? null;
        
        // Check raw database record
        if ($dataId) {
            echo "\n🔍 Raw Database Query:\n";
            
            try {
                $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
                $stmt = $pdo->prepare('SELECT * FROM sensor_data WHERE id = ?');
                $stmt->execute([$dataId]);
                $dbRecord = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($dbRecord) {
                    echo "Raw Database Record:\n";
                    foreach ($dbRecord as $column => $value) {
                        echo "  $column: " . ($value ?? 'NULL') . " (" . gettype($value) . ")\n";
                    }
                    
                    echo "\n🧪 Voltage Analysis:\n";
                    echo "  Database voltage: '" . $dbRecord['voltage'] . "'\n";
                    echo "  Is null: " . (is_null($dbRecord['voltage']) ? 'YES' : 'NO') . "\n";
                    echo "  Is empty: " . (empty($dbRecord['voltage']) ? 'YES' : 'NO') . "\n";
                    echo "  Length: " . strlen($dbRecord['voltage']) . "\n";
                    
                    if ($dbRecord['voltage'] == '3.30') {
                        echo "  ✅ VOLTAGE CORRECTLY STORED!\n";
                    } else {
                        echo "  ❌ Voltage not stored correctly\n";
                        echo "  Expected: 3.30\n";
                        echo "  Got: '" . $dbRecord['voltage'] . "'\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "Database error: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🏁 Voltage Debug Complete\n";
?>
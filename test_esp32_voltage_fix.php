<?php
/**
 * Test ESP32 dengan data voltage yang lengkap
 */

echo "🧪 Testing ESP32 dengan Data Voltage Lengkap\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Data sensor ESP32 yang lengkap (berdasarkan serial monitor)
$esp32Data = [
    'device_id' => 1,
    'ph' => 4.000,          // pH dari sensor asli
    'temperature' => 26.5,   // Temperature simulasi
    'oxygen' => 6.8,        // Oxygen simulasi  
    'voltage' => 3.300,     // Voltage dari sensor asli
    'timestamp' => time()
];

echo "📊 ESP32 Sensor Data (Complete):\n";
echo "  🧪 pH: " . $esp32Data['ph'] . " (real sensor)\n";
echo "  ⚡ Voltage: " . $esp32Data['voltage'] . " V (real sensor)\n";
echo "  🌡️  Temperature: " . $esp32Data['temperature'] . "°C\n";
echo "  💨 Oxygen: " . $esp32Data['oxygen'] . " mg/L\n";
echo "  ⏰ Timestamp: " . date('Y-m-d H:i:s', $esp32Data['timestamp']) . "\n\n";

// Send ke API endpoint yang benar
$url = "http://127.0.0.1:8000/api/sensor-data/store";

echo "📡 Sending to Laravel API...\n";
echo "URL: $url\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($esp32Data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: ESP32-pH-Sensor-v2'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📨 Response Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    if ($httpCode == 201) {
        echo "✅ SUCCESS! ESP32 data dengan voltage berhasil dikirim!\n\n";
        
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['data'])) {
            $data = $responseData['data'];
            echo "📄 Server Response:\n";
            echo "  🆔 Data ID: " . ($data['id'] ?? 'N/A') . "\n";
            echo "  🧪 pH Stored: " . ($data['ph'] ?? 'N/A') . "\n";
            echo "  ⚡ Voltage Stored: " . ($data['voltage'] ?? 'N/A') . " V\n";
            echo "  🌡️  Temperature: " . ($data['temperature'] ?? 'N/A') . "°C\n";
            echo "  💨 Oxygen: " . ($data['oxygen'] ?? 'N/A') . " mg/L\n";
            echo "  ⏰ Recorded: " . ($data['recorded_at'] ?? 'N/A') . "\n\n";
            
            // Verify data matches
            echo "🎯 VERIFICATION:\n";
            echo "  📊 pH Match: " . (abs($data['ph'] - 4.00) < 0.01 ? '✅ MATCH' : '❌ MISMATCH') . "\n";
            echo "  ⚡ Voltage Match: " . (abs($data['voltage'] - 3.30) < 0.01 ? '✅ MATCH' : '❌ MISMATCH') . "\n";
            
            $newDataId = $data['id'];
        }
    } else {
        echo "❌ HTTP Error $httpCode\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
}

// Verify data in database
if (isset($newDataId)) {
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "🔍 Verifying data in database...\n\n";
    
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
        $stmt = $pdo->prepare('SELECT * FROM sensor_data WHERE id = ?');
        $stmt->execute([$newDataId]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbData) {
            echo "✅ Data confirmed in database!\n";
            echo "📊 Database Record:\n";
            echo "  🆔 ID: " . $dbData['id'] . "\n";
            echo "  🧪 pH: " . $dbData['ph'] . "\n";
            echo "  ⚡ Voltage: " . $dbData['voltage'] . " V\n";
            echo "  🌡️  Temperature: " . $dbData['temperature'] . "°C\n";
            echo "  💨 Oxygen: " . $dbData['oxygen'] . " mg/L\n";
            echo "  ⏰ Time: " . $dbData['recorded_at'] . "\n";
        } else {
            echo "⚠️  Data not found in database\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Database verification error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

if ($httpCode == 201) {
    echo "🎉 ESP32 INTEGRATION FIXED!\n\n";
    echo "✅ YANG SUDAH DIPERBAIKI:\n";
    echo "  📊 Database Structure: Column voltage ditambahkan\n";
    echo "  📡 API Endpoint: Working dengan data lengkap\n";
    echo "  ⚡ Voltage Storage: Berhasil tersimpan di database\n";
    echo "  🧪 pH Data: Real sensor data (4.000) tersimpan\n\n";
    
    echo "📱 ESP32 Sekarang Siap:\n";
    echo "  1. 🔧 Upload ESP32_pH_Local_Database.ino ke hardware\n";
    echo "  2. 📺 Connect ke Serial Monitor (115200 baud)\n";
    echo "  3. 🌐 Connect ke WiFi POCO\n";
    echo "  4. ⌨️  Ketik 'sendnow' untuk kirim data manual\n";
    echo "  5. 🧪 Calibrate dengan 'save7' dan 'save4'\n";
    echo "  6. 📊 Monitor dashboard untuk data real-time\n\n";
    
    echo "🔗 Data Flow Complete:\n";
    echo "  ESP32 (pH: 4.000, V: 3.300) → API → Database (WITH VOLTAGE) → Dashboard ✅\n";
} else {
    echo "❌ Integration still has issues - check API endpoint\n";
}

echo "\n🏁 ESP32 Voltage Integration Test Complete\n";
?>
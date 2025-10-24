<?php
/**
 * Test ESP32 endpoint dengan IP yang benar
 */

echo "🔍 TESTING CORRECT ESP32 IP ENDPOINT\n";
echo str_repeat("=", 60) . "\n\n";

$correctUrl = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store";
$testData = [
    'device_id' => 1,
    'ph' => 4.0,
    'temperature' => 26.5,
    'oxygen' => 6.8,
    'voltage' => 3.3
];

echo "📡 Testing URL: $correctUrl\n";
echo "📦 Test Data: " . json_encode($testData) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $correctUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    echo "\n🔧 ISSUE: Komputer tidak bisa diakses dari IP 10.31.188.8\n";
    echo "Kemungkinan:\n";
    echo "1. Firewall Windows blocking incoming connections\n";
    echo "2. XAMPP Apache not listening on all interfaces\n";
    echo "3. ESP32 dan komputer tidak di network yang sama\n\n";
    
    echo "💡 SOLUSI CEPAT - Gunakan localhost:\n";
    echo "Ganti ESP32 SERVER_URL menjadi:\n";
    echo "const char* SERVER_URL = \"http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store\";\n\n";
    
} else {
    echo "✅ Connection successful!\n";
    echo "📨 HTTP Code: $httpCode\n";
    echo "📄 Response: $response\n\n";
    
    if ($httpCode == 201) {
        echo "🎉 PERFECT! ESP32 bisa kirim ke IP ini!\n";
        
        // Verify latest record
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
            $stmt = $pdo->query('SELECT id, ph, voltage, recorded_at FROM sensor_data ORDER BY id DESC LIMIT 1');
            $latest = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "📊 Latest record: ID {$latest['id']}, pH {$latest['ph']}, V {$latest['voltage']}, Time {$latest['recorded_at']}\n";
        } catch (Exception $e) {
            echo "Database check error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 NEXT STEPS:\n\n";
echo "1. ✅ ESP32 code sudah diperbaiki:\n";
echo "   - URL: Updated ke IP yang benar\n";
echo "   - Voltage: Ditambahkan ke JSON payload\n\n";
echo "2. 📤 Upload code yang sudah diperbaiki ke ESP32\n\n";
echo "3. 🔄 Restart ESP32 dan monitor Serial output\n\n";
echo "4. ✅ ESP32 seharusnya mulai kirim data real-time!\n";
?>
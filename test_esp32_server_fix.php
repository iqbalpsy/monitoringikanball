<?php
/**
 * Test ESP32 ke alamat server yang benar
 */

echo "🧪 Testing ESP32 ke Server XAMPP (Localhost)\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Data sensor ESP32 yang lengkap
$esp32Data = [
    'device_id' => 1,
    'ph' => 4.000,          // pH dari sensor asli (dari serial monitor)
    'temperature' => 26.5,   // Temperature 
    'oxygen' => 6.8,        // Oxygen
    'voltage' => 3.300,     // Voltage dari sensor asli (dari serial monitor)
    'timestamp' => time()
];

echo "📊 ESP32 Real Sensor Data:\n";
echo "  🧪 pH: " . $esp32Data['ph'] . " (from serial monitor)\n";
echo "  ⚡ Voltage: " . $esp32Data['voltage'] . " V (from serial monitor)\n";
echo "  🌡️  Temperature: " . $esp32Data['temperature'] . "°C\n";
echo "  💨 Oxygen: " . $esp32Data['oxygen'] . " mg/L\n\n";

// Test ke berbagai URL yang mungkin
$testUrls = [
    "http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store",
    "http://127.0.0.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store", 
    "http://10.31.188.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store"
];

foreach ($testUrls as $index => $url) {
    echo ($index + 1) . "️⃣ Testing URL: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($esp32Data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: ESP32-pH-Sensor-Test'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "   📨 Response Code: $httpCode\n";
    
    if ($error) {
        echo "   ❌ Error: $error\n";
    } else {
        if ($httpCode == 201) {
            echo "   ✅ SUCCESS!\n";
            
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['data'])) {
                $data = $responseData['data'];
                echo "   📊 Data ID: " . ($data['id'] ?? 'N/A') . "\n";
                echo "   🧪 pH: " . ($data['ph'] ?? 'N/A') . "\n";
                echo "   ⚡ Voltage: " . ($data['voltage'] ?? 'N/A') . " V\n";
                echo "   ⏰ Time: " . ($data['recorded_at'] ?? 'N/A') . "\n";
                
                // Mark as successful URL
                $workingUrl = $url;
                $successDataId = $data['id'];
            }
        } else {
            echo "   ❌ HTTP Error $httpCode\n";
            if ($response) {
                echo "   Response: " . substr($response, 0, 100) . "...\n";
            }
        }
    }
    echo "\n";
}

// Verify in database if we had success
if (isset($successDataId)) {
    echo str_repeat("-", 60) . "\n";
    echo "✅ SUCCESS! Data berhasil masuk ke database!\n\n";
    
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
        
        // Check latest record
        $stmt = $pdo->query('SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1');
        $latestData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Latest Database Record:\n";
        echo "  🆔 ID: " . $latestData['id'] . "\n";
        echo "  🧪 pH: " . $latestData['ph'] . "\n";
        echo "  ⚡ Voltage: " . ($latestData['voltage'] ?? 'NULL') . " V\n";
        echo "  🌡️  Temperature: " . $latestData['temperature'] . "°C\n";
        echo "  💨 Oxygen: " . $latestData['oxygen'] . " mg/L\n";
        echo "  ⏰ Time: " . $latestData['recorded_at'] . "\n\n";
        
        echo "🎯 DATA MATCH VERIFICATION:\n";
        echo "  📊 pH: " . (abs($latestData['ph'] - 4.00) < 0.01 ? '✅ MATCH (4.00)' : '❌ MISMATCH') . "\n";
        echo "  ⚡ Voltage: " . (abs($latestData['voltage'] - 3.30) < 0.01 ? '✅ MATCH (3.30V)' : '❌ MISMATCH') . "\n";
        
    } catch (Exception $e) {
        echo "❌ Database check error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🔧 ESP32 Configuration untuk Real Hardware:\n";
    echo "Update ESP32 code dengan URL yang working:\n";
    echo "const char* serverURL = \"$workingUrl\";\n\n";
    
} else {
    echo "❌ Semua URL gagal - check XAMPP server status\n";
    echo "Pastikan Apache sudah running di XAMPP\n";
}

echo str_repeat("=", 60) . "\n";
echo "🏁 ESP32 Server Connection Test Complete\n";
?>
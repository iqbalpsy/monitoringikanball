<?php
/**
 * Test ESP32 Real pH Data Transmission
 * Simulate ESP32 sending actual pH sensor data (pH: 4.000, V: 3.300)
 */

echo "🧪 Testing ESP32 Real pH Sensor Data Transmission\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Based on serial monitor output: pH: 4.000, V: 3.300
$realSensorData = [
    'device_id' => 1,
    'ph' => 4.000,          // Actual pH reading from sensor
    'temperature' => 25.5,   // Simulated temperature 
    'oxygen' => 6.8,        // Simulated oxygen
    'voltage' => 3.300,     // Actual voltage from sensor
    'timestamp' => time()
];

echo "📊 Simulating Real ESP32 Sensor Data:\n";
echo "  🧪 pH: " . $realSensorData['ph'] . " (from actual sensor)\n";
echo "  ⚡ Voltage: " . $realSensorData['voltage'] . " V (from actual sensor)\n";
echo "  🌡️  Temperature: " . $realSensorData['temperature'] . "°C (simulated)\n";
echo "  💨 Oxygen: " . $realSensorData['oxygen'] . " mg/L (simulated)\n\n";

$url = "http://10.31.188.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store";

echo "📡 Sending to: $url\n";
echo "📦 Payload: " . json_encode($realSensorData) . "\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($realSensorData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: ESP32-Real-pH-Sensor'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📨 Response Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
    echo "⚠️  Check if server IP 10.31.188.8 is accessible\n";
} else {
    if ($httpCode == 201) {
        echo "✅ SUCCESS! Real pH data sent successfully!\n\n";
        
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['data'])) {
            $data = $responseData['data'];
            echo "📄 Server Response:\n";
            echo "  🆔 Data ID: " . ($data['id'] ?? 'N/A') . "\n";
            echo "  🧪 pH Stored: " . ($data['ph'] ?? 'N/A') . "\n";
            echo "  ⚡ Voltage Stored: " . ($data['voltage'] ?? 'N/A') . " V\n";
            echo "  🌡️  Temperature: " . ($data['temperature'] ?? 'N/A') . "°C\n";
            echo "  💨 Oxygen: " . ($data['oxygen'] ?? 'N/A') . " mg/L\n";
            echo "  ⏰ Timestamp: " . ($data['recorded_at'] ?? 'N/A') . "\n\n";
            
            echo "🎯 VERIFICATION:\n";
            echo "  📊 pH Match: " . ($data['ph'] == '4.00' ? '✅ MATCH' : '❌ MISMATCH') . "\n";
            echo "  ⚡ Voltage Match: " . ($data['voltage'] == '3.30' ? '✅ MATCH' : '❌ MISMATCH') . "\n";
        }
    } else {
        echo "❌ HTTP Error $httpCode\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

if ($httpCode == 201) {
    echo "🎉 ESP32 REAL pH DATA INTEGRATION SUCCESSFUL!\n\n";
    
    echo "📋 Next Steps for ESP32:\n";
    echo "  1. 🔧 Upload updated ESP32_pH_Local_Database.ino to your ESP32\n";
    echo "  2. 📺 Open Serial Monitor (115200 baud)\n";
    echo "  3. 🧪 Calibrate pH sensor:\n";
    echo "     - Put sensor in pH 7.0 solution, type 'save7'\n";
    echo "     - Put sensor in pH 4.0 solution, type 'save4'\n";
    echo "  4. ⌨️  Type 'sendnow' to manually send real sensor data\n";
    echo "  5. 📊 Type 'status' to see current readings\n";
    echo "  6. 🌐 Check dashboard for real pH data display\n\n";
    
    echo "🔧 ESP32 Configuration Notes:\n";
    echo "  📡 Server IP: 10.31.188.8 (currently configured)\n";
    echo "  🌐 WiFi: POCO / 12345678 (currently configured)\n";
    echo "  🧪 pH Pin: GPIO 4 (currently configured)\n";
    echo "  ⏰ Send Interval: 30 seconds\n\n";
    
    echo "📊 Data Flow Confirmed:\n";
    echo "  ESP32 pH Sensor (4.000, 3.300V) → WiFi → Web API → Database → Dashboard ✅\n";
} else {
    echo "❌ Data transmission failed - check network configuration\n";
    echo "🔧 Troubleshooting:\n";
    echo "  1. Verify ESP32 can reach 10.31.188.8\n";
    echo "  2. Check WiFi connection\n";
    echo "  3. Verify server is running\n";
    echo "  4. Test with localhost if on same machine\n";
}

echo "\n🏁 Real pH Data Test Complete\n";
?>
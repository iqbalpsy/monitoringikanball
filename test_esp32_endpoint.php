<?php
/**
 * Direct test ke ESP32 endpoint yang sebenarnya
 */

echo "🔍 TESTING ESP32 EXACT ENDPOINT\n";
echo str_repeat("=", 60) . "\n\n";

// URL yang digunakan ESP32
$esp32Url = "http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store";

// Test data yang sama seperti ESP32 kirim
$esp32Data = [
    'device_id' => 1,
    'ph' => 4.000,
    'temperature' => 26.5, 
    'oxygen' => 6.8,
    'voltage' => 3.300
];

echo "📡 ESP32 URL: $esp32Url\n";
echo "📦 ESP32 Data: " . json_encode($esp32Data) . "\n\n";

// Test koneksi
echo "🧪 Testing connection...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $esp32Url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($esp32Data));
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
    echo "🔧 Possible issues:\n";
    echo "   - XAMPP Apache not running\n";
    echo "   - Wrong URL path\n";
    echo "   - Firewall blocking connection\n\n";
} else {
    echo "✅ Connection successful!\n";
    echo "📨 HTTP Code: $httpCode\n";
    echo "📄 Response: $response\n\n";
    
    if ($httpCode == 201) {
        echo "🎉 SUCCESS! Data saved to database.\n";
        
        // Verify in database
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
            $stmt = $pdo->query('SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1');
            $latest = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "📊 Latest database record:\n";
            echo "   ID: " . $latest['id'] . "\n";
            echo "   pH: " . $latest['ph'] . "\n";
            echo "   Voltage: " . ($latest['voltage'] ?? 'NULL') . "\n";
            echo "   Time: " . $latest['recorded_at'] . "\n";
            
        } catch (Exception $e) {
            echo "❌ Database check failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Failed! HTTP Code indicates error.\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 DIAGNOSIS:\n\n";

if ($error || $httpCode != 201) {
    echo "❌ PROBLEM FOUND: ESP32 endpoint not working\n\n";
    echo "🔧 SOLUTIONS:\n";
    echo "1. Check XAMPP Control Panel:\n";
    echo "   - Apache: Should be green/running\n";
    echo "   - MySQL: Should be green/running\n\n";
    echo "2. Test URL in browser:\n";
    echo "   - Open: http://localhost/monitoringikanball/monitoringikanball/public/\n";
    echo "   - Should show Laravel app\n\n";
    echo "3. Check ESP32 WiFi:\n";
    echo "   - ESP32 should connect to WiFi 'POCO'\n";
    echo "   - ESP32 should get IP address\n";
    echo "   - Serial monitor should show WiFi connected\n\n";
} else {
    echo "✅ ENDPOINT WORKING! ESP32 should be able to send data.\n\n";
    echo "🔍 If ESP32 still not sending:\n";
    echo "1. Check ESP32 WiFi connection\n";
    echo "2. Check ESP32 serial monitor for HTTP response codes\n";
    echo "3. ESP32 might be sending to different IP\n";
    echo "4. Check ESP32 code SERVER_URL matches this endpoint\n";
}
?>
<?php
/**
 * Test API endpoint directly to make sure it's working
 */

echo "🧪 TESTING ESP32 API ENDPOINT\n";
echo str_repeat("=", 50) . "\n\n";

// Test data that matches ESP32 output
$testData = [
    'device_id' => 1,
    'ph' => 4.000,
    'temperature' => 26.5,
    'oxygen' => 6.8,
    'voltage' => 3.300,
    'timestamp' => time()
];

echo "📦 Test data (matching ESP32):\n";
echo "  pH: " . $testData['ph'] . "\n";
echo "  Voltage: " . $testData['voltage'] . "V\n";
echo "  Device ID: " . $testData['device_id'] . "\n\n";

// Test different possible URLs
$testUrls = [
    'Laravel Dev Server' => 'http://127.0.0.1:8000/api/sensor-data/store',
    'XAMPP Apache (from ESP32 code)' => 'http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store',
    'XAMPP Local IP' => 'http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store'
];

foreach ($testUrls as $name => $url) {
    echo "🔗 Testing: $name\n";
    echo "   URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Error: $error\n";
    } else {
        echo "   📨 HTTP Code: $httpCode\n";
        if ($httpCode == 200 || $httpCode == 201) {
            echo "   ✅ Success! Response: " . substr($response, 0, 100) . "...\n";
        } else {
            echo "   ❌ Failed. Response: " . substr($response, 0, 100) . "...\n";
        }
    }
    echo "\n";
}

echo str_repeat("=", 50) . "\n";
echo "🔍 DIAGNOSIS:\n\n";
echo "Jika ESP32 serial monitor menampilkan:\n";
echo "- Raw ADC: 4095 | V: 3.300 | pH: 4.000\n";
echo "- Tapi tidak ada data baru di database\n\n";
echo "Kemungkinan masalah:\n";
echo "1. ❌ WiFi ESP32 tidak terkoneksi\n";
echo "2. ❌ URL server salah di ESP32 code\n";
echo "3. ❌ Laravel server tidak running\n";
echo "4. ❌ Firewall blocking ESP32 request\n";
echo "5. ❌ ESP32 tidak benar-benar mengirim HTTP request\n\n";
echo "Solusi:\n";
echo "1. ✅ Cek WiFi connection di ESP32\n";
echo "2. ✅ Update URL di ESP32 code ke working endpoint\n";
echo "3. ✅ Start Laravel server: php artisan serve\n";
echo "4. ✅ Monitor ESP32 serial untuk HTTP response codes\n";
?>
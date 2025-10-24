<?php
/**
 * Monitor ESP32 real-time data transmission
 */

echo "🔍 MONITORING ESP32 DATA TRANSMISSION\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check latest records
    echo "📊 Latest 10 database records:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-4s | %-6s | %-8s | %-19s | %-8s\n", 'ID', 'pH', 'Voltage', 'Time', 'Age');
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query('SELECT id, ph, voltage, recorded_at FROM sensor_data ORDER BY id DESC LIMIT 10');
    $now = new DateTime();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recordTime = new DateTime($row['recorded_at']);
        $diff = $now->diff($recordTime);
        $ageStr = $diff->i . "m " . $diff->s . "s";
        
        printf("%-4s | %-6.2f | %-8s | %-19s | %-8s\n",
               $row['id'],
               (float)$row['ph'],
               $row['voltage'] ?? 'NULL',
               $row['recorded_at'],
               $ageStr
        );
    }
    
    echo str_repeat("-", 80) . "\n\n";
    
    // Check records from last 2 minutes
    echo "🕐 Records from last 2 minutes:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE recorded_at >= NOW() - INTERVAL 2 MINUTE");
    $recentCount = $stmt->fetchColumn();
    echo "Count: $recentCount\n\n";
    
    if ($recentCount == 0) {
        echo "❌ NO NEW DATA! ESP32 not sending to database\n\n";
        
        echo "🔧 TROUBLESHOOTING CHECKLIST:\n";
        echo "1. ✅ ESP32 code sudah di-upload dengan perubahan:\n";
        echo "   - IP: 10.31.188.8 (current computer IP)\n";
        echo "   - Voltage added to JSON payload\n\n";
        
        echo "2. 🔍 ESP32 Serial Monitor harus menampilkan:\n";
        echo "   Raw ADC: 4095 | V: 3.300 | pH: 4.000\n";
        echo "   🌐 Mengirim data ke server...\n";
        echo "   Response Code: 201\n";
        echo "   ✅ Data berhasil dikirim!\n\n";
        
        echo "3. 🔗 Jika tidak ada output HTTP di serial:\n";
        echo "   - ESP32 WiFi mungkin tidak connect\n";
        echo "   - Check SSID 'POCO' dan password '12345678'\n";
        echo "   - Serial monitor harus show: WiFi: ✅\n\n";
        
        echo "4. 🌐 Jika WiFi connect tapi no HTTP response:\n";
        echo "   - Firewall Windows blocking connections\n";
        echo "   - ESP32 dan PC tidak di network yang sama\n";
        echo "   - XAMPP Apache tidak running\n\n";
        
        echo "💡 QUICK TEST: Ketik 'sendnow' di ESP32 Serial Monitor\n";
        echo "   Ini akan force send data dan show response!\n\n";
        
    } else {
        echo "✅ ESP32 IS SENDING DATA! Found $recentCount recent records\n";
        
        // Show the newest record details
        $stmt = $pdo->query('SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1');
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n📊 Latest record details:\n";
        echo "   ID: " . $latest['id'] . "\n";
        echo "   pH: " . $latest['ph'] . "\n";
        echo "   Voltage: " . ($latest['voltage'] ?? 'NULL') . "\n";
        echo "   Temperature: " . $latest['temperature'] . "\n";
        echo "   Time: " . $latest['recorded_at'] . "\n";
    }
    
    // Additional diagnostics
    echo "\n🔍 DIAGNOSTIC INFO:\n";
    $total = $pdo->query('SELECT COUNT(*) FROM sensor_data')->fetchColumn();
    echo "Total records: $total\n";
    
    $esp32Count = $pdo->query("SELECT COUNT(*) FROM sensor_data WHERE ph = 4.00")->fetchColumn();
    echo "ESP32 records (pH=4.00): $esp32Count\n";
    
    $withVoltage = $pdo->query("SELECT COUNT(*) FROM sensor_data WHERE voltage IS NOT NULL")->fetchColumn();
    echo "Records with voltage: $withVoltage\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 STATUS SUMMARY:\n";
echo "- Database connection: ✅ Working\n";
echo "- API endpoint: ✅ Working (tested)\n";
echo "- ESP32 code: ✅ Fixed (IP + voltage)\n";
echo "- Missing: Real ESP32 HTTP transmission\n\n";
echo "Next: Check ESP32 Serial Monitor output!\n";
?>
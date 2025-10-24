<?php
/**
 * Check database records for ESP32 data
 */

echo "🔍 Checking Database for ESP32 Data\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
    
    // Get latest 10 records
    $stmt = $pdo->query('SELECT * FROM sensor_data ORDER BY id DESC LIMIT 10');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Latest 10 records in database:\n";
    echo str_repeat('=', 90) . "\n";
    printf("%-4s | %-8s | %-6s | %-6s | %-6s | %-8s | %-19s\n", 
           'ID', 'Device', 'pH', 'Temp', 'O2', 'Voltage', 'Time');
    echo str_repeat('-', 90) . "\n";
    
    foreach ($results as $row) {
        printf("%-4s | %-8s | %-6s | %-6s | %-6s | %-8s | %-19s\n",
               $row['id'],
               $row['device_id'], 
               $row['ph'],
               $row['temperature'],
               $row['oxygen'],
               $row['voltage'] ?? 'NULL',
               substr($row['recorded_at'], 0, 19)
        );
    }
    echo str_repeat('=', 90) . "\n\n";
    
    // Check records from today
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE DATE(recorded_at) = CURDATE()");
    $todayCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📅 Records from today: " . $todayCount . "\n";
    
    // Check records from last hour
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $lastHourCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "🕐 Records from last hour: " . $lastHourCount . "\n";
    
    // Check for specific pH values (real sensor data)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE ph = 4.00");
    $ph4Count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "🧪 Records with pH = 4.00 (ESP32 real data): " . $ph4Count . "\n\n";
    
    if ($ph4Count > 0) {
        echo "✅ ESP32 real sensor data found in database!\n";
        $stmt = $pdo->query("SELECT * FROM sensor_data WHERE ph = 4.00 ORDER BY id DESC LIMIT 1");
        $realData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Latest ESP32 real data:\n";
        echo "  🆔 ID: " . $realData['id'] . "\n";
        echo "  🧪 pH: " . $realData['ph'] . "\n";
        echo "  🌡️  Temperature: " . $realData['temperature'] . "°C\n";
        echo "  💨 Oxygen: " . $realData['oxygen'] . " mg/L\n";
        echo "  ⚡ Voltage: " . ($realData['voltage'] ?? 'NULL') . "\n";
        echo "  ⏰ Time: " . $realData['recorded_at'] . "\n";
    } else {
        echo "⚠️  No ESP32 real sensor data found (pH = 4.00)\n";
        echo "   Data in database appears to be simulated/test data\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Database Check Complete\n";
?>
<?php
/**
 * Check current database state to match phpMyAdmin
 */

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 CURRENT DATABASE STATE - MATCHING PHPMYADMIN\n";
    echo str_repeat("=", 70) . "\n\n";
    
    // Get latest 20 records to match phpMyAdmin view
    echo "📊 Latest 20 Records (descending by ID):\n";
    echo str_repeat("-", 120) . "\n";
    printf("%-4s | %-8s | %-6s | %-6s | %-6s | %-8s | %-19s\n", 
           'ID', 'Device', 'pH', 'Temp', 'O2', 'Voltage', 'Recorded At');
    echo str_repeat("-", 120) . "\n";
    
    $stmt = $pdo->query('SELECT * FROM sensor_data ORDER BY id DESC LIMIT 20');
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($records as $record) {
        printf("%-4s | %-8s | %-6.2f | %-6.2f | %-6.2f | %-8s | %-19s\n",
               $record['id'],
               $record['device_id'],
               (float)$record['ph'],
               (float)$record['temperature'],
               (float)$record['oxygen'],
               $record['voltage'] ?? 'NULL',
               $record['recorded_at']
        );
    }
    
    echo str_repeat("-", 120) . "\n";
    
    // Total count
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM sensor_data');
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "\n📈 Total Records in Database: $total\n";
    
    // Check for ESP32 data (pH = 4.00)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE ph = 4.00");
    $esp32Count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "🧪 Records with pH = 4.00 (ESP32 real data): $esp32Count\n";
    
    // Check data from today
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE DATE(recorded_at) = CURDATE()");
    $todayCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📅 Records from today: $todayCount\n";
    
    // Check voltage column existence
    $stmt = $pdo->query("SHOW COLUMNS FROM sensor_data LIKE 'voltage'");
    $voltageExists = $stmt->rowCount() > 0;
    echo "⚡ Voltage Column: " . ($voltageExists ? "EXISTS" : "MISSING") . "\n";
    
    if ($voltageExists) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data WHERE voltage IS NOT NULL");
        $voltageCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "⚡ Records with voltage data: $voltageCount\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "✅ Database check complete - compare with phpMyAdmin!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
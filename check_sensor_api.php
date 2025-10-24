<?php
/**
 * Check real-time ESP32 data transmission
 */

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 ESP32 REAL-TIME DATA CHECK\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Check records from last 10 minutes
    echo "📊 Records from last 10 minutes:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-4s | %-6s | %-8s | %-19s\n", 'ID', 'pH', 'Voltage', 'Time');
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("
        SELECT id, ph, voltage, recorded_at 
        FROM sensor_data 
        WHERE recorded_at >= NOW() - INTERVAL 10 MINUTE 
        ORDER BY id DESC
    ");
    
    $recentCount = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-4s | %-6.2f | %-8s | %-19s\n",
               $row['id'],
               (float)$row['ph'],
               $row['voltage'] ?? 'NULL',
               $row['recorded_at']
        );
        $recentCount++;
    }
    
    if ($recentCount == 0) {
        echo "❌ No new data in last 10 minutes!\n";
    } else {
        echo "\n✅ Found $recentCount recent records\n";
    }
    
    echo str_repeat("-", 80) . "\n\n";
    
    // Get the very latest record
    echo "🕐 Latest record in database:\n";
    $stmt = $pdo->query('SELECT id, ph, voltage, recorded_at FROM sensor_data ORDER BY id DESC LIMIT 1');
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latest) {
        echo "  🆔 ID: " . $latest['id'] . "\n";
        echo "  🧪 pH: " . $latest['ph'] . "\n"; 
        echo "  ⚡ Voltage: " . ($latest['voltage'] ?? 'NULL') . "\n";
        echo "  ⏰ Time: " . $latest['recorded_at'] . "\n";
        
        // Calculate how old this record is
        $recordTime = new DateTime($latest['recorded_at']);
        $now = new DateTime();
        $diff = $now->diff($recordTime);
        
        if ($diff->i < 1) {
            echo "  📈 Status: 🟢 FRESH (less than 1 minute ago)\n";
        } elseif ($diff->i < 5) {
            echo "  📈 Status: 🟡 RECENT ({$diff->i} minutes ago)\n";
        } else {
            echo "  📈 Status: 🔴 OLD ({$diff->i} minutes ago)\n";
        }
    }
    
    // Total count
    $total = $pdo->query('SELECT COUNT(*) FROM sensor_data')->fetchColumn();
    echo "\n📊 Total records: $total\n";
    
    // Check for ESP32 pattern (pH = 4.00)
    $esp32Count = $pdo->query("SELECT COUNT(*) FROM sensor_data WHERE ph = 4.00")->fetchColumn();
    echo "🧪 ESP32 records (pH = 4.00): $esp32Count\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    
    if ($recentCount == 0) {
        echo "🚨 PROBLEM: ESP32 data not reaching database!\n\n";
        echo "🔧 TROUBLESHOOTING STEPS:\n";
        echo "1. Check WiFi connection on ESP32\n";
        echo "2. Check if ESP32 sending to correct URL\n";
        echo "3. Check Laravel server is running\n";
        echo "4. Check API endpoint /sensor-data/store\n";
        echo "5. Monitor serial output for HTTP response codes\n";
    } else {
        echo "✅ ESP32 data is reaching database!\n";
        echo "📊 Data transmission: WORKING\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>

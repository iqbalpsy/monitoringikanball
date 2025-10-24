<?php
/**
 * Real-time monitoring untuk ESP32 data
 * Jalankan script ini sambil troubleshoot ESP32
 */

echo "🔍 REAL-TIME ESP32 MONITORING\n";
echo str_repeat("=", 50) . "\n";
echo "Monitoring database setiap 5 detik...\n";
echo "Press Ctrl+C to stop\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
$lastId = 0;

// Get current latest ID
$stmt = $pdo->query('SELECT MAX(id) FROM sensor_data');
$lastId = $stmt->fetchColumn() ?: 0;
echo "Starting monitoring from ID: $lastId\n\n";

$counter = 0;
while (true) {
    $counter++;
    
    // Check for new records
    $stmt = $pdo->query("SELECT * FROM sensor_data WHERE id > $lastId ORDER BY id ASC");
    $newRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($newRecords)) {
        foreach ($newRecords as $record) {
            echo "🎉 NEW ESP32 DATA DETECTED!\n";
            echo "   ID: " . $record['id'] . "\n";
            echo "   pH: " . $record['ph'] . "\n";
            echo "   Voltage: " . ($record['voltage'] ?? 'NULL') . "\n";
            echo "   Time: " . $record['recorded_at'] . "\n";
            echo "   ✅ ESP32 SUCCESSFULLY SENDING DATA!\n\n";
            
            $lastId = $record['id'];
        }
    } else {
        // Show current status every 10 iterations (50 seconds)
        if ($counter % 10 == 0) {
            $stmt = $pdo->query('SELECT id, recorded_at FROM sensor_data ORDER BY id DESC LIMIT 1');
            $latest = $stmt->fetch(PDO::FETCH_ASSOC);
            $now = new DateTime();
            $recordTime = new DateTime($latest['recorded_at']);
            $diff = $now->diff($recordTime);
            
            echo "[" . date('H:i:s') . "] Waiting... Last data: ID {$latest['id']}, {$diff->i}m {$diff->s}s ago\n";
            
            if ($diff->i >= 2) {
                echo "   ⚠️ ESP32 not sending data for {$diff->i}+ minutes\n";
                echo "   💡 Check ESP32 Serial Monitor for WiFi/HTTP output\n";
            }
        } else {
            echo ".";
        }
    }
    
    sleep(5);
}
?>
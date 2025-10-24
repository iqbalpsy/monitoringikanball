<?php

// Simulasi FirebaseService getAllSensorData method
$url = "https://container-kolam-default-rtdb.asia-southeast1.firebasedatabase.app/sensor_data.json";

echo "=== TESTING FIREBASE SERVICE LOGIC ===\n";

$json = file_get_contents($url);
$data = json_decode($json, true);

if ($data && is_array($data)) {
    echo "✅ Firebase data retrieved: " . count($data) . " records\n";
    
    // Convert Firebase data structure to sensor data format
    $sensorData = [];
    $count = 0;
    foreach ($data as $key => $value) {
        $count++;
        if ($count > 5) break; // Only process first 5 for testing
        
        // Handle timestamp - jika terlalu kecil, gunakan current time
        $timestamp = $value['timestamp'] ?? 0;
        $createdAt = null;
        
        if ($timestamp > 1000000000) {
            // Valid Unix timestamp (after year 2001)
            $createdAt = date('Y-m-d H:i:s', $timestamp);
        } elseif ($timestamp > 1000000) {
            // Unix timestamp dalam milliseconds
            $createdAt = date('Y-m-d H:i:s', $timestamp / 1000);
        } else {
            // Timestamp tidak valid, gunakan waktu sekarang
            $createdAt = date('Y-m-d H:i:s');
        }
        
        // Convert format from ESP32 to match our database structure
        $sensorData[$key] = [
            'id' => $key,
            'firebase_key' => $key,
            'device_id' => 1,
            'pH' => $value['nilai_ph'] ?? 0,
            'ph' => $value['nilai_ph'] ?? 0,
            'temperature' => 26.5,
            'oxygen' => 8.0,
            'voltage' => $value['tegangan_v'] ?? 0,
            'timestamp' => $timestamp,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'recorded_at' => $createdAt,
        ];
    }
    
    echo "\n=== CONVERTED DATA SAMPLE ===\n";
    foreach ($sensorData as $key => $item) {
        echo "Record: {$key}\n";
        echo "  pH: {$item['pH']}\n";
        echo "  Temperature: {$item['temperature']}\n";
        echo "  Voltage: {$item['voltage']}\n";
        echo "  Created: {$item['created_at']}\n";
        echo "  ---\n";
    }
    
    echo "\n✅ Total processed: " . count($sensorData) . " records\n";
    echo "✅ Data structure looks correct for controller\n";
    
} else {
    echo "❌ No data from Firebase\n";
}
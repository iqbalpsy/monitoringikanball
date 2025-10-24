<?php

use Illuminate\Support\Facades\Http;

// Test Firebase service directly without Laravel
$databaseUrl = 'https://container-kolam-default-rtdb.asia-southeast1.firebasedatabase.app';

echo "=== TESTING FIREBASE SERVICE LOGIC ===\n";

try {
    $url = "{$databaseUrl}/sensor_data.json";
    
    $response = Http::timeout(10)->get($url);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if ($data && is_array($data)) {
            echo "✅ Firebase data retrieved: " . count($data) . " records\n";
            
            // Convert Firebase data structure to sensor data format
            $sensorData = [];
            $count = 0;
            foreach ($data as $key => $value) {
                $count++;
                if ($count > 3) break; // Only process first 3
                
                // Handle timestamp
                $timestamp = $value['timestamp'] ?? 0;
                $createdAt = null;
                
                if ($timestamp > 1000000000) {
                    $createdAt = date('Y-m-d H:i:s', $timestamp);
                } elseif ($timestamp > 1000000) {
                    $createdAt = date('Y-m-d H:i:s', $timestamp / 1000);
                } else {
                    $createdAt = date('Y-m-d H:i:s');
                }
                
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
            
            echo "\n=== FIREBASE SERVICE RETURN FORMAT ===\n";
            foreach ($sensorData as $key => $item) {
                echo "Key: {$key}\n";
                echo "  ID: {$item['id']}\n";
                echo "  pH: {$item['ph']}\n"; 
                echo "  Temperature: {$item['temperature']}\n";
                echo "  Voltage: {$item['voltage']}\n";
                echo "  Created: {$item['created_at']}\n";
                echo "  ---\n";
            }
            
            echo "\n=== CONTROLLER MAPPING TEST ===\n";
            // Test controller mapping logic
            $allData = collect($sensorData)->map(function ($item, $key) {
                return (object) [
                    'id' => $item['id'] ?? $key,
                    'device_id' => $item['device_id'] ?? 1,
                    'temperature' => isset($item['temperature']) ? floatval($item['temperature']) : null,
                    'ph' => isset($item['pH']) ? floatval($item['pH']) : (isset($item['ph']) ? floatval($item['ph']) : null),
                    'oxygen' => isset($item['oxygen']) ? floatval($item['oxygen']) : null,
                    'voltage' => isset($item['voltage']) ? floatval($item['voltage']) : null,
                    'timestamp' => $item['timestamp'] ?? 0,
                    'created_at' => isset($item['created_at']) ? 
                        \Carbon\Carbon::parse($item['created_at']) : 
                        now(),
                    'device' => (object) ['name' => 'ESP32 Device #1', 'id' => 1]
                ];
            });
            
            echo "✅ Controller mapping successful\n";
            echo "✅ Collection count: " . $allData->count() . "\n";
            
            foreach ($allData as $item) {
                echo "Object ID: {$item->id}\n";
                echo "  pH: {$item->ph}\n";
                echo "  Temperature: {$item->temperature}\n";
                echo "  Voltage: {$item->voltage}\n";
                echo "  Created: {$item->created_at}\n";
                echo "  ---\n";
                break; // Just show first one
            }
            
        } else {
            echo "❌ No data or invalid format\n";
        }
    } else {
        echo "❌ HTTP request failed: " . $response->status() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
<?php

$url = "https://container-kolam-default-rtdb.asia-southeast1.firebasedatabase.app/sensor_data.json";

echo "=== TESTING DIRECT FIREBASE CONNECTION ===\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 10
    ]
]);

$json = file_get_contents($url, false, $context);

if ($json) {
    echo "✅ Firebase connection successful\n";
    
    $data = json_decode($json, true);
    
    if ($data && is_array($data)) {
        echo "✅ JSON decode successful\n";
        echo "📊 Total records: " . count($data) . "\n";
        
        // Show first record structure
        $firstKey = array_key_first($data);
        $firstRecord = $data[$firstKey];
        
        echo "\n=== FIRST RECORD STRUCTURE ===\n";
        echo "Key: {$firstKey}\n";
        echo "Data structure:\n";
        print_r($firstRecord);
        
        // Show expected structure
        echo "\n=== EXPECTED CONVERSION ===\n";
        $converted = [
            'id' => $firstKey,
            'firebase_key' => $firstKey,
            'device_id' => 1,
            'ph' => $firstRecord['nilai_ph'] ?? 0,
            'temperature' => 25.0,
            'oxygen' => 8.0,
            'voltage' => $firstRecord['tegangan_v'] ?? 0,
            'timestamp' => $firstRecord['timestamp'] ?? 0,
            'created_at' => isset($firstRecord['timestamp']) 
                ? date('Y-m-d H:i:s', $firstRecord['timestamp'] / 1000) 
                : date('Y-m-d H:i:s'),
        ];
        
        echo "Converted structure:\n";
        print_r($converted);
        
    } else {
        echo "❌ JSON decode failed or no data\n";
        echo "Raw data: " . substr($json, 0, 500) . "...\n";
    }
} else {
    echo "❌ Firebase connection failed\n";
}
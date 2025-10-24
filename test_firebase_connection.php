<?php
/**
 * Test Script: Ambil Data dari Firebase dan Tampilkan
 * 
 * Script ini akan:
 * 1. Connect ke Firebase Realtime Database
 * 2. Ambil data sensor dari path /sensor_data
 * 3. Display data dalam format table
 * 4. (Optional) Sync data ke MySQL lokal
 * 
 * Usage: php test_firebase_connection.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "═══════════════════════════════════════════════════════\n";
echo "   TEST: Ambil Data dari Firebase Realtime Database\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Get Firebase configuration from .env
$firebaseUrl = env('FIREBASE_DATABASE_URL');
$projectId = env('FIREBASE_PROJECT_ID');

echo "📋 Firebase Configuration:\n";
echo "   Database URL : $firebaseUrl\n";
echo "   Project ID   : $projectId\n";
echo "\n";

if (empty($firebaseUrl)) {
    echo "❌ ERROR: FIREBASE_DATABASE_URL not set in .env file!\n";
    echo "\nPlease add to .env:\n";
    echo "FIREBASE_DATABASE_URL=https://container-kolam-default-rtdb.firebaseio.com\n";
    exit(1);
}

// ============================================================
// TEST 1: Ambil Semua Data dari Firebase
// ============================================================
echo "TEST 1: Ambil Semua Data Sensor dari Firebase\n";
echo "───────────────────────────────────────────────────────\n";

try {
    // Construct Firebase REST API endpoint
    // Path: /sensor_data.json (get all sensor data)
    $endpoint = rtrim($firebaseUrl, '/') . '/sensor_data.json';
    
    echo "🔗 Connecting to: $endpoint\n\n";
    
    // Make HTTP GET request to Firebase
    $response = Http::timeout(10)->get($endpoint);
    
    if ($response->successful()) {
        echo "✅ Connection successful! HTTP Status: " . $response->status() . "\n\n";
        
        $data = $response->json();
        
        if (empty($data)) {
            echo "⚠️  No data found in Firebase!\n";
            echo "\nPossible reasons:\n";
            echo "  1. ESP32 belum kirim data ke Firebase\n";
            echo "  2. Path /sensor_data belum ada\n";
            echo "  3. Firebase database masih kosong\n\n";
            
            echo "💡 Cara mengatasi:\n";
            echo "  1. Upload ESP32 code dengan Firebase config\n";
            echo "  2. Ketik 'sendnow' di Serial Monitor ESP32\n";
            echo "  3. Refresh Firebase Console → Data tab\n";
            
        } else {
            echo "✅ Data found! Total records: " . count($data) . "\n\n";
            
            // Display data in table format
            echo "📊 Data Sensor dari Firebase:\n";
            echo "════════════════════════════════════════════════════════════════════════\n";
            echo sprintf("%-25s | %-10s | %-8s | %-12s | %-8s\n", 
                "Firebase Key", "Device ID", "pH", "Temp (°C)", "O2 (mg/L)"
            );
            echo "════════════════════════════════════════════════════════════════════════\n";
            
            $count = 0;
            foreach ($data as $key => $item) {
                if (is_array($item) && isset($item['device_id'])) {
                    echo sprintf("%-25s | %-10s | %-8s | %-12s | %-8s\n",
                        substr($key, 0, 25),
                        $item['device_id'] ?? 'N/A',
                        number_format($item['ph'] ?? 0, 2),
                        number_format($item['temperature'] ?? 0, 1),
                        number_format($item['oxygen'] ?? 0, 1)
                    );
                    $count++;
                    
                    // Limit display to 10 records
                    if ($count >= 10) {
                        echo "... (showing first 10 of " . count($data) . " total records)\n";
                        break;
                    }
                }
            }
            echo "════════════════════════════════════════════════════════════════════════\n";
            
            // Show latest data details
            echo "\n📌 Latest Data Details:\n";
            echo "────────────────────────────────\n";
            $latestKey = array_key_last($data);
            $latest = $data[$latestKey];
            
            if (is_array($latest)) {
                echo "Firebase Key  : $latestKey\n";
                echo "Device ID     : " . ($latest['device_id'] ?? 'N/A') . "\n";
                echo "pH Level      : " . number_format($latest['ph'] ?? 0, 2) . "\n";
                echo "Temperature   : " . number_format($latest['temperature'] ?? 0, 1) . " °C\n";
                echo "Oxygen        : " . number_format($latest['oxygen'] ?? 0, 1) . " mg/L\n";
                echo "Voltage       : " . number_format($latest['voltage'] ?? 0, 3) . " V\n";
                
                if (isset($latest['timestamp'])) {
                    $timestamp = $latest['timestamp'];
                    // Convert milliseconds to seconds if needed
                    if ($timestamp > 10000000000) {
                        $timestamp = $timestamp / 1000;
                    }
                    echo "Timestamp     : " . date('Y-m-d H:i:s', $timestamp) . "\n";
                }
            }
        }
        
    } else {
        echo "❌ Connection failed! HTTP Status: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n\n";
        
        echo "Common errors:\n";
        echo "  - 404: Database URL salah atau database belum dibuat\n";
        echo "  - 401: Authentication required (perlu database secret)\n";
        echo "  - 403: Permission denied (check Firebase rules)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}

// ============================================================
// TEST 2: Ambil Data untuk Device Tertentu
// ============================================================
echo "\n\nTEST 2: Filter Data untuk Device ID = 1\n";
echo "───────────────────────────────────────────────────────\n";

try {
    // Query Firebase dengan orderBy dan limitToLast
    $endpoint = rtrim($firebaseUrl, '/') . '/sensor_data.json?orderBy="device_id"&equalTo=1&limitToLast=5';
    
    echo "🔗 Endpoint: $endpoint\n\n";
    
    $response = Http::timeout(10)->get($endpoint);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if (empty($data)) {
            echo "⚠️  No data found for Device ID = 1\n";
        } else {
            echo "✅ Found " . count($data) . " records for Device ID = 1\n\n";
            
            echo "Latest 5 readings:\n";
            echo "────────────────────────────────────────────────────────\n";
            
            foreach ($data as $key => $item) {
                echo "pH: " . number_format($item['ph'] ?? 0, 2);
                echo " | Temp: " . number_format($item['temperature'] ?? 0, 1) . "°C";
                echo " | O2: " . number_format($item['oxygen'] ?? 0, 1) . " mg/L\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// ============================================================
// TEST 3: Format Data untuk Chart (Dashboard)
// ============================================================
echo "\n\nTEST 3: Format Data untuk Chart Dashboard\n";
echo "───────────────────────────────────────────────────────\n";

try {
    $endpoint = rtrim($firebaseUrl, '/') . '/sensor_data.json?limitToLast=10';
    
    $response = Http::timeout(10)->get($endpoint);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if (!empty($data)) {
            $chartData = [];
            
            foreach ($data as $key => $item) {
                if (is_array($item)) {
                    $chartData[] = [
                        'time' => isset($item['timestamp']) ? 
                            date('H:i', $item['timestamp'] / 1000) : 
                            date('H:i'),
                        'ph' => round($item['ph'] ?? 0, 2),
                        'temperature' => round($item['temperature'] ?? 0, 1),
                        'oxygen' => round($item['oxygen'] ?? 0, 1),
                    ];
                }
            }
            
            echo "✅ Chart data ready! " . count($chartData) . " points\n\n";
            
            echo "JSON format for Chart.js:\n";
            echo "────────────────────────────────────────────────────────\n";
            echo json_encode($chartData, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// ============================================================
// SUMMARY & NEXT STEPS
// ============================================================
echo "\n\n═══════════════════════════════════════════════════════\n";
echo "   SUMMARY - Cara Implementasi di Web Dashboard\n";
echo "═══════════════════════════════════════════════════════\n\n";

echo "✅ STEP 1: Update DashboardController\n";
echo "────────────────────────────────────────────────────────\n";
echo "File: app/Http/Controllers/DashboardController.php\n\n";
echo "public function getSensorData(Request \$request) {\n";
echo "    \$firebaseUrl = env('FIREBASE_DATABASE_URL');\n";
echo "    \$endpoint = \$firebaseUrl . '/sensor_data.json?limitToLast=10';\n";
echo "    \n";
echo "    \$response = Http::get(\$endpoint);\n";
echo "    \$data = \$response->json();\n";
echo "    \n";
echo "    // Format data untuk chart\n";
echo "    \$chartData = [];\n";
echo "    foreach (\$data as \$item) {\n";
echo "        \$chartData[] = [\n";
echo "            'ph' => \$item['ph'],\n";
echo "            'temperature' => \$item['temperature'],\n";
echo "            'oxygen' => \$item['oxygen'],\n";
echo "        ];\n";
echo "    }\n";
echo "    \n";
echo "    return response()->json(\$chartData);\n";
echo "}\n\n";

echo "✅ STEP 2: Update JavaScript (AJAX)\n";
echo "────────────────────────────────────────────────────────\n";
echo "File: resources/views/dashboard/user.blade.php\n\n";
echo "<script>\n";
echo "function loadFirebaseData() {\n";
echo "    fetch('/api/dashboard/sensor-data?source=firebase')\n";
echo "        .then(response => response.json())\n";
echo "        .then(data => {\n";
echo "            // Update cards\n";
echo "            document.getElementById('ph-value').textContent = data.latest.ph;\n";
echo "            \n";
echo "            // Update chart\n";
echo "            updateChart(data.data);\n";
echo "        });\n";
echo "}\n";
echo "\n";
echo "// Auto-refresh setiap 30 detik\n";
echo "setInterval(loadFirebaseData, 30000);\n";
echo "loadFirebaseData();\n";
echo "</script>\n\n";

echo "✅ STEP 3: Test di Browser\n";
echo "────────────────────────────────────────────────────────\n";
echo "URL: http://localhost/api/dashboard/sensor-data?source=firebase\n\n";

echo "📚 DOKUMENTASI LENGKAP:\n";
echo "────────────────────────────────────────────────────────\n";
echo "1. FIREBASE_PULL_DATA_GUIDE.md - Panduan implementasi lengkap\n";
echo "2. FIREBASE_SETUP_INSTRUCTIONS.md - Setup Firebase\n";
echo "3. QUICK_START_SENSOR_SYNC.md - Quick start guide\n\n";

echo "═══════════════════════════════════════════════════════\n";
echo "   TEST COMPLETE! ✅\n";
echo "═══════════════════════════════════════════════════════\n\n";

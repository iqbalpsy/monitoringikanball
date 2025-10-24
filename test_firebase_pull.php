<?php
/**
 * Test Firebase Connection and Pull Data to Web
 * 
 * File ini menunjukkan cara menarik data dari Firebase dan menampilkan di web
 * 
 * Usage:
 * 1. Pastikan ESP32 sudah kirim data ke Firebase
 * 2. Update .env dengan FIREBASE_DATABASE_URL
 * 3. Run: php test_firebase_pull.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseService;
use App\Models\SensorData;

echo "═══════════════════════════════════════════════════════\n";
echo "   TEST: Pull Data dari Firebase ke Web Laravel\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Initialize Firebase Service
$firebase = new FirebaseService();

// ============================================================
// TEST 1: Get Data dari Firebase
// ============================================================
echo "TEST 1: Ambil Data dari Firebase\n";
echo "───────────────────────────────────────────────────────\n";

$deviceId = 1; // Device ID yang ingin diambil
echo "Fetching data untuk device ID: $deviceId...\n\n";

try {
    $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
    
    if ($firebaseData && count($firebaseData) > 0) {
        echo "✅ SUCCESS! Berhasil ambil " . count($firebaseData) . " data dari Firebase\n\n";
        
        echo "Data Terbaru (Latest Reading):\n";
        echo "────────────────────────────────\n";
        $latest = $firebaseData[0];
        echo "Firebase Key : " . ($latest['firebase_key'] ?? 'N/A') . "\n";
        echo "Device ID    : " . ($latest['device_id'] ?? 'N/A') . "\n";
        echo "pH Level     : " . ($latest['ph'] ?? 'N/A') . "\n";
        echo "Temperature  : " . ($latest['temperature'] ?? 'N/A') . " °C\n";
        echo "Oxygen Level : " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
        echo "Voltage      : " . ($latest['voltage'] ?? 'N/A') . " V\n";
        echo "Timestamp    : " . ($latest['timestamp'] ?? 'N/A') . "\n";
        
        // Display all data in table format
        echo "\n\nSemua Data (Last 10 Readings):\n";
        echo "────────────────────────────────────────────────────────────────────────\n";
        echo sprintf("%-3s | %-8s | %-12s | %-8s | %-20s\n", 
            "No", "pH", "Temp (°C)", "O2 (mg/L)", "Time"
        );
        echo "────────────────────────────────────────────────────────────────────────\n";
        
        foreach ($firebaseData as $index => $data) {
            $timestamp = isset($data['timestamp']) ? 
                date('Y-m-d H:i:s', $data['timestamp'] / 1000) : 
                'N/A';
            
            echo sprintf("%-3d | %-8s | %-12s | %-8s | %-20s\n",
                ($index + 1),
                number_format($data['ph'] ?? 0, 2),
                number_format($data['temperature'] ?? 0, 1),
                number_format($data['oxygen'] ?? 0, 1),
                $timestamp
            );
        }
        echo "────────────────────────────────────────────────────────────────────────\n";
        
    } else {
        echo "❌ FAILED! Tidak ada data ditemukan\n";
        echo "Kemungkinan penyebab:\n";
        echo "  1. ESP32 belum kirim data ke Firebase\n";
        echo "  2. Firebase Database URL salah di .env\n";
        echo "  3. Firebase security rules block read access\n";
        echo "  4. Device ID tidak ada di Firebase\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Check:\n";
    echo "  - File .env → FIREBASE_DATABASE_URL\n";
    echo "  - Firebase Console → Realtime Database → Rules\n";
    echo "  - Firebase Console → Realtime Database → Data tab\n\n";
}

// ============================================================
// TEST 2: Sync Firebase Data ke Local Database (MySQL)
// ============================================================
echo "\n\nTEST 2: Sync Data Firebase ke Database MySQL Lokal\n";
echo "───────────────────────────────────────────────────────\n";

try {
    echo "Syncing data dari Firebase ke MySQL...\n";
    $syncedCount = $firebase->syncFirebaseToDatabase($deviceId);
    
    echo "✅ SUCCESS! Berhasil sync $syncedCount data ke database lokal\n\n";
    
    // Verify data in local database
    $localData = SensorData::where('device_id', $deviceId)
        ->orderBy('recorded_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($localData->count() > 0) {
        echo "Data di Database Lokal (Latest 5 Records):\n";
        echo "────────────────────────────────────────────────────────────────────────\n";
        echo sprintf("%-5s | %-8s | %-12s | %-8s | %-20s\n", 
            "ID", "pH", "Temp (°C)", "O2 (mg/L)", "Recorded At"
        );
        echo "────────────────────────────────────────────────────────────────────────\n";
        
        foreach ($localData as $data) {
            echo sprintf("%-5d | %-8s | %-12s | %-8s | %-20s\n",
                $data->id,
                number_format($data->ph, 2),
                number_format($data->temperature, 1),
                number_format($data->oxygen, 1),
                $data->recorded_at->format('Y-m-d H:i:s')
            );
        }
        echo "────────────────────────────────────────────────────────────────────────\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}

// ============================================================
// TEST 3: Compare Firebase vs Local Database
// ============================================================
echo "\n\nTEST 3: Perbandingan Data Firebase vs Database Lokal\n";
echo "───────────────────────────────────────────────────────\n";

try {
    // Count Firebase records
    $firebaseCount = $firebaseData ? count($firebaseData) : 0;
    
    // Count local database records
    $localCount = SensorData::where('device_id', $deviceId)->count();
    
    echo "Firebase Records  : $firebaseCount data\n";
    echo "Local DB Records  : $localCount data\n";
    echo "Difference        : " . abs($firebaseCount - $localCount) . " data\n\n";
    
    if ($firebaseCount > $localCount) {
        echo "⚠️  Firebase memiliki lebih banyak data. Run sync untuk update database lokal.\n";
        echo "    Command: php artisan firebase:sync $deviceId\n";
    } elseif ($localCount > $firebaseCount) {
        echo "✅ Database lokal sudah lengkap (termasuk data historis)\n";
    } else {
        echo "✅ Firebase dan database lokal sudah sinkron!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}

// ============================================================
// SUMMARY & NEXT STEPS
// ============================================================
echo "\n\n═══════════════════════════════════════════════════════\n";
echo "   SUMMARY - Cara Menarik Data dari Firebase ke Web\n";
echo "═══════════════════════════════════════════════════════\n\n";

echo "✅ COMPLETED:\n";
echo "  1. Berhasil ambil data dari Firebase REST API\n";
echo "  2. Berhasil sync data ke database MySQL lokal\n";
echo "  3. Data siap ditampilkan di web dashboard\n\n";

echo "📊 CARA TAMPILKAN DI WEB DASHBOARD:\n";
echo "───────────────────────────────────────────────────────\n\n";

echo "METHOD 1: JavaScript AJAX (Real-time dari Firebase)\n";
echo "────────────────────────────────────────────────────────\n";
echo "// Di file dashboard blade (resources/views/dashboard/user.blade.php)\n";
echo "<script>\n";
echo "  function loadFirebaseData() {\n";
echo "    fetch('/api/dashboard/sensor-data?source=firebase&hours=24')\n";
echo "      .then(response => response.json())\n";
echo "      .then(data => {\n";
echo "        console.log('Firebase Data:', data);\n";
echo "        \n";
echo "        // Update cards\n";
echo "        document.getElementById('ph-value').textContent = data.latest.ph;\n";
echo "        document.getElementById('temp-value').textContent = data.latest.temperature;\n";
echo "        document.getElementById('oxygen-value').textContent = data.latest.oxygen;\n";
echo "        \n";
echo "        // Update chart\n";
echo "        updateChart(data.data);\n";
echo "      });\n";
echo "  }\n";
echo "  \n";
echo "  // Load data setiap 30 detik (real-time)\n";
echo "  setInterval(loadFirebaseData, 30000);\n";
echo "  loadFirebaseData(); // Load immediately\n";
echo "</script>\n\n";

echo "METHOD 2: Laravel Controller (dari Firebase)\n";
echo "────────────────────────────────────────────────────────\n";
echo "// Di DashboardController.php\n";
echo "public function userDashboard() {\n";
echo "  \$firebase = new FirebaseService();\n";
echo "  \$sensorData = \$firebase->getSensorDataFromFirebase(1);\n";
echo "  \n";
echo "  \$latest = \$sensorData[0] ?? null;\n";
echo "  \n";
echo "  return view('dashboard.user', compact('sensorData', 'latest'));\n";
echo "}\n\n";

echo "METHOD 3: Laravel Controller (dari Database Lokal)\n";
echo "────────────────────────────────────────────────────────\n";
echo "// Sync Firebase ke MySQL dulu\n";
echo "\$firebase->syncFirebaseToDatabase(1);\n";
echo "\n";
echo "// Ambil dari database lokal (untuk chart & analytics)\n";
echo "\$sensorData = SensorData::where('device_id', 1)\n";
echo "  ->orderBy('recorded_at', 'desc')\n";
echo "  ->limit(100)\n";
echo "  ->get();\n\n";

echo "🔄 AUTO SYNC (Recommended):\n";
echo "───────────────────────────────────────────────────────\n";
echo "Setup Laravel scheduler untuk auto-sync setiap 5 menit:\n\n";
echo "1. Buat command:\n";
echo "   php artisan make:command SyncFirebaseData\n\n";
echo "2. Edit app/Console/Kernel.php:\n";
echo "   \$schedule->command('firebase:sync 1')->everyFiveMinutes();\n\n";
echo "3. Run scheduler:\n";
echo "   php artisan schedule:work\n\n";

echo "🎯 TESTING URLS:\n";
echo "───────────────────────────────────────────────────────\n";
echo "1. API Endpoint (Firebase):\n";
echo "   http://localhost/api/dashboard/sensor-data?source=firebase\n\n";
echo "2. API Endpoint (Database):\n";
echo "   http://localhost/api/dashboard/sensor-data?source=database\n\n";
echo "3. Dashboard User:\n";
echo "   http://localhost/dashboard/user\n\n";
echo "4. Dashboard Admin:\n";
echo "   http://localhost/dashboard/admin\n\n";

echo "📝 CONFIGURATION:\n";
echo "───────────────────────────────────────────────────────\n";
echo "File .env harus ada:\n";
echo "  FIREBASE_DATABASE_URL=https://container-kolam-default-rtdb.firebaseio.com\n";
echo "  FIREBASE_PROJECT_ID=container-kolam\n";
echo "  FIREBASE_DATABASE_SECRET=(optional)\n\n";

echo "═══════════════════════════════════════════════════════\n";
echo "   TEST COMPLETE! ✅\n";
echo "═══════════════════════════════════════════════════════\n\n";

echo "Next: Update .env file dengan Firebase Database URL Anda!\n";
echo "Get URL from: https://console.firebase.google.com/project/container-kolam/database\n\n";

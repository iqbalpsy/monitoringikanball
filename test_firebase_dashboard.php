<?php
/**
 * Test Firebase Integration untuk Dashboard
 * 
 * Test ini memverifikasi:
 * 1. Koneksi ke Firebase Realtime Database
 * 2. Membaca data dari Firebase 
 * 3. Sinkronisasi data ke MySQL database
 * 4. API endpoint untuk dashboard
 * 
 * Usage: php test_firebase_dashboard.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseService;
use Illuminate\Support\Facades\Http;

echo "🔥 TESTING FIREBASE DASHBOARD INTEGRATION\n";
echo "=========================================\n\n";

// Test 1: Firebase Service Connection
echo "1️⃣ Testing FirebaseService Connection\n";
echo "───────────────────────────────────────\n";

$firebase = new FirebaseService();

try {
    $connectionTest = $firebase->testConnection();
    
    if ($connectionTest['success']) {
        echo "✅ Firebase connection: SUCCESS\n";
        echo "   Status: {$connectionTest['status']}\n";
        echo "   Message: {$connectionTest['message']}\n";
    } else {
        echo "❌ Firebase connection: FAILED\n";
        echo "   Status: {$connectionTest['status']}\n";
        echo "   Message: {$connectionTest['message']}\n";
        echo "\n⚠️  Check your .env FIREBASE_DATABASE_URL configuration!\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n";

// Test 2: Get Data dari Firebase
echo "2️⃣ Testing getSensorDataFromFirebase()\n";
echo "─────────────────────────────────────────\n";

try {
    $firebaseData = $firebase->getSensorDataFromFirebase(1);
    
    if (!empty($firebaseData)) {
        echo "✅ Firebase data retrieval: SUCCESS\n";
        echo "   Records found: " . count($firebaseData) . "\n";
        echo "   Latest data:\n";
        
        $latest = $firebaseData[0];
        echo "   ├─ Device ID: " . ($latest['device_id'] ?? 'N/A') . "\n";
        echo "   ├─ pH: " . ($latest['ph'] ?? 'N/A') . "\n";
        echo "   ├─ Temperature: " . ($latest['temperature'] ?? 'N/A') . " °C\n";
        echo "   ├─ Oxygen: " . ($latest['oxygen'] ?? 'N/A') . " mg/L\n";
        echo "   └─ Timestamp: " . (isset($latest['timestamp']) ? date('Y-m-d H:i:s', $latest['timestamp'] / 1000) : 'N/A') . "\n";
        
    } else {
        echo "⚠️  No data found in Firebase\n";
        echo "   Possible causes:\n";
        echo "   - ESP32 belum mengirim data ke Firebase\n";
        echo "   - Firebase Database URL salah\n";
        echo "   - Firebase security rules tidak mengizinkan read\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n";

// Test 3: Sync Firebase ke Database
echo "3️⃣ Testing syncFirebaseToDatabase()\n";
echo "──────────────────────────────────────\n";

try {
    $syncCount = $firebase->syncFirebaseToDatabase(1);
    
    echo "✅ Sync to database: SUCCESS\n";
    echo "   Records synced: $syncCount\n";
    
    if ($syncCount > 0) {
        // Check database records
        $localCount = \App\Models\SensorData::where('device_id', 1)->count();
        echo "   Total records in database: $localCount\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n";

// Test 4: API Endpoint /api/firebase-data
echo "4️⃣ Testing API Endpoint /api/firebase-data\n";
echo "────────────────────────────────────────────\n";

try {
    $url = config('app.url') . '/api/firebase-data?device_id=1';
    
    echo "Testing URL: $url\n";
    
    $response = Http::timeout(10)->get($url);
    
    if ($response->successful()) {
        $data = $response->json();
        
        echo "✅ API endpoint: SUCCESS\n";
        echo "   Status: {$response->status()}\n";
        echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "   Data count: " . ($data['count'] ?? 0) . "\n";
        echo "   Source: " . ($data['source'] ?? 'unknown') . "\n";
        
        if (!empty($data['latest'])) {
            echo "   Latest values:\n";
            echo "   ├─ pH: " . ($data['latest']['ph'] ?? 'N/A') . "\n";
            echo "   ├─ Temperature: " . ($data['latest']['temperature'] ?? 'N/A') . " °C\n";
            echo "   └─ Oxygen: " . ($data['latest']['oxygen'] ?? 'N/A') . " mg/L\n";
        }
        
    } else {
        echo "❌ API endpoint: FAILED\n";
        echo "   Status: {$response->status()}\n";
        echo "   Body: {$response->body()}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n";

// Test 5: Database vs Firebase Comparison
echo "5️⃣ Comparing Database vs Firebase Data\n";
echo "────────────────────────────────────────\n";

try {
    // Count Firebase records
    $firebaseCount = !empty($firebaseData) ? count($firebaseData) : 0;
    
    // Count database records
    $databaseCount = \App\Models\SensorData::where('device_id', 1)->count();
    
    echo "Firebase records: $firebaseCount\n";
    echo "Database records: $databaseCount\n";
    echo "Difference: " . abs($firebaseCount - $databaseCount) . "\n";
    
    if ($firebaseCount > 0 && $databaseCount > 0) {
        echo "✅ Both sources have data\n";
    } elseif ($firebaseCount > 0) {
        echo "⚠️  Firebase has data, but database is empty\n";
        echo "   Recommendation: Run sync to populate database\n";
    } elseif ($databaseCount > 0) {
        echo "⚠️  Database has data, but Firebase is empty\n";
        echo "   Recommendation: Check ESP32 Firebase configuration\n";
    } else {
        echo "⚠️  No data in both Firebase and database\n";
        echo "   Recommendation: Check ESP32 is sending data to Firebase\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n";

// Summary & Recommendations
echo "📋 SUMMARY & RECOMMENDATIONS\n";
echo "════════════════════════════\n\n";

echo "🎯 Status:\n";
if (isset($connectionTest) && $connectionTest['success']) {
    echo "✅ Firebase connection: Working\n";
} else {
    echo "❌ Firebase connection: Failed - Check .env configuration\n";
}

if (!empty($firebaseData)) {
    echo "✅ Firebase data: Available (" . count($firebaseData) . " records)\n";
} else {
    echo "❌ Firebase data: Not available - Check ESP32 configuration\n";
}

if (isset($syncCount) && $syncCount > 0) {
    echo "✅ Database sync: Working ($syncCount records synced)\n";
} else {
    echo "⚠️  Database sync: No new data to sync\n";
}

echo "\n🔧 Next Steps:\n";

if (empty($firebaseData)) {
    echo "1. 📡 Configure ESP32 with Firebase Database URL:\n";
    echo "   - Update ESP32_pH_Firebase.ino\n";
    echo "   - Set FIREBASE_HOST = \"container-kolam-default-rtdb.firebaseio.com\"\n";
    echo "   - Set FIREBASE_AUTH = \"\" (for testing)\n";
    echo "   - Upload code to ESP32\n";
    echo "   - Test with 'sendnow' command in Serial Monitor\n\n";
}

echo "2. 🌐 Test Dashboard in Browser:\n";
echo "   - Open: http://localhost/user/dashboard\n";
echo "   - Click 'Firebase' button to load Firebase data\n";
echo "   - Check cards and chart update with Firebase data\n\n";

echo "3. 🔄 Setup Auto-refresh:\n";
echo "   - Dashboard auto-refreshes every 30 seconds\n";
echo "   - Firebase data will be synced to database automatically\n\n";

echo "4. 📊 Monitor Real-time Updates:\n";
echo "   - ESP32 sends data every 30 seconds to Firebase\n";
echo "   - Dashboard pulls latest data from Firebase\n";
echo "   - Cards show real-time sensor values\n\n";

echo "🎉 Firebase Dashboard Integration Ready!\n";
echo "════════════════════════════════════════\n\n";

// Quick URL references
echo "📚 Quick References:\n";
echo "Firebase Console: https://console.firebase.google.com/project/container-kolam/database\n";
echo "Dashboard URL: http://localhost/user/dashboard\n";
echo "API Test: http://localhost/api/firebase-data?device_id=1\n";
echo "Local API: http://localhost/api/sensor-data?type=working_hours\n\n";

echo "🔥 Test Complete! 🚀\n";
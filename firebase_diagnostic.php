<?php
/**
 * Firebase Connection Diagnostic Tool
 * Debug Firebase integration step by step
 */

require_once 'vendor/autoload.php';

echo "🔥 Firebase Connection Diagnostic\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Step 1: Check Environment Configuration
echo "📋 Step 1: Firebase Configuration Check\n";
echo "-" . str_repeat("-", 40) . "\n";

$firebaseUrl = env('FIREBASE_DATABASE_URL');
$firebaseApiKey = env('FIREBASE_API_KEY');
$firebaseProjectId = env('FIREBASE_PROJECT_ID');

echo "Firebase Database URL: " . ($firebaseUrl ?: '❌ NOT SET') . "\n";
echo "Firebase API Key: " . ($firebaseApiKey ? '✅ SET (' . substr($firebaseApiKey, 0, 10) . '...)' : '❌ NOT SET') . "\n";
echo "Firebase Project ID: " . ($firebaseProjectId ?: '❌ NOT SET') . "\n\n";

// Step 2: Test Direct Firebase Connection
echo "📡 Step 2: Direct Firebase REST API Test\n";
echo "-" . str_repeat("-", 40) . "\n";

$testUrl = "https://container-kolam-default-rtdb.firebaseio.com/sensor_data/device_1.json?orderBy=\"timestamp\"&limitToLast=1";

echo "Testing URL: {$testUrl}\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'header' => [
            'Accept: application/json',
            'User-Agent: Laravel-Firebase-Test'
        ]
    ]
]);

try {
    echo "⏳ Making direct Firebase request...\n";
    $response = file_get_contents($testUrl, false, $context);
    
    if ($response === false) {
        echo "❌ Direct Firebase connection failed\n";
        echo "Possible issues:\n";
        echo "- Firebase Database Rules too restrictive\n";
        echo "- Network connectivity issues\n";
        echo "- Invalid Firebase URL\n\n";
    } else {
        echo "✅ Direct Firebase connection successful\n";
        $data = json_decode($response, true);
        
        if ($data === null || empty($data)) {
            echo "📭 Firebase connected but NO DATA found\n";
            echo "Response: " . $response . "\n";
            echo "This means:\n";
            echo "- ESP32 hasn't sent data yet\n";
            echo "- Data is stored in different path\n";
            echo "- Firebase database is empty\n\n";
        } else {
            echo "🎉 Firebase HAS DATA!\n";
            echo "Data count: " . count($data) . " records\n";
            echo "Sample data: " . json_encode(array_slice($data, 0, 1, true)) . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n\n";
}

// Step 3: Test Laravel Firebase Service
echo "🔧 Step 3: Laravel FirebaseService Test\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Test if we can load Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrap successful\n";
    
    // Test FirebaseService
    $firebaseService = new \App\Services\FirebaseService();
    echo "✅ FirebaseService instantiated\n";
    
    $firebaseData = $firebaseService->getSensorDataFromFirebase(1);
    
    if ($firebaseData && count($firebaseData) > 0) {
        echo "🎉 FirebaseService returning data!\n";
        echo "Records found: " . count($firebaseData) . "\n";
        echo "Latest data: " . json_encode($firebaseData[0]) . "\n\n";
    } else {
        echo "📭 FirebaseService returns empty data\n";
        echo "This could mean:\n";
        echo "- Method is working but no data in Firebase\n";
        echo "- Wrong device ID (trying device_1)\n";
        echo "- Data structure mismatch\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel/FirebaseService error: " . $e->getMessage() . "\n\n";
}

// Step 4: Test API Endpoint
echo "🌐 Step 4: Web API Endpoint Test\n";
echo "-" . str_repeat("-", 40) . "\n";

$apiUrl = 'http://127.0.0.1:8001/api/firebase-data';
echo "Testing API: {$apiUrl}\n";

$apiContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 15,
        'header' => [
            'Accept: application/json',
            'User-Agent: PHP-Test-Client'
        ]
    ]
]);

try {
    $apiResponse = @file_get_contents($apiUrl, false, $apiContext);
    
    if ($apiResponse === false) {
        echo "❌ API endpoint not accessible\n";
        echo "Possible issues:\n";
        echo "- Laravel server not running\n";
        echo "- Route not configured\n";
        echo "- Authentication required\n\n";
    } else {
        echo "✅ API endpoint accessible\n";
        $apiData = json_decode($apiResponse, true);
        
        if ($apiData && isset($apiData['success'])) {
            echo "API Response: " . ($apiData['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
            
            if (isset($apiData['latest'])) {
                echo "Latest data returned: YES\n";
                echo "Temperature: " . ($apiData['latest']['temperature'] ?? 'N/A') . "\n";
                echo "pH: " . ($apiData['latest']['ph'] ?? 'N/A') . "\n";
                echo "Oxygen: " . ($apiData['latest']['oxygen'] ?? 'N/A') . "\n";
            }
            
            if (isset($apiData['fallback']) && $apiData['fallback']) {
                echo "⚠️ API is returning FALLBACK data (not real Firebase data)\n";
            }
            
            if (isset($apiData['info'])) {
                echo "Info: " . $apiData['info'] . "\n";
            }
        } else {
            echo "❌ Invalid API response format\n";
            echo "Response: " . substr($apiResponse, 0, 200) . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ API test error: " . $e->getMessage() . "\n\n";
}

// Step 5: Recommendations
echo "💡 Step 5: Diagnosis & Recommendations\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "Based on the tests above:\n\n";

echo "If Direct Firebase shows NO DATA:\n";
echo "➤ ESP32 hasn't sent data to Firebase yet\n";
echo "➤ Upload ESP32_pH_Firebase.ino to hardware\n";
echo "➤ Use 'sendnow' command to manually send test data\n";
echo "➤ Check Firebase Console: https://console.firebase.google.com/project/container-kolam/database\n\n";

echo "If FirebaseService fails:\n";
echo "➤ Check .env configuration\n";
echo "➤ Verify config/services.php Firebase settings\n";
echo "➤ Run: php artisan config:clear\n\n";

echo "If API endpoint fails:\n";
echo "➤ Ensure Laravel server running: php artisan serve --port=8001\n";
echo "➤ Check routes: php artisan route:list | grep firebase\n";
echo "➤ Verify DashboardController has getFirebaseData method\n\n";

echo "If web dashboard shows fallback data:\n";
echo "➤ This is normal when no real Firebase data exists\n";
echo "➤ Click 'Firebase' button to trigger data load\n";
echo "➤ Check browser console for JavaScript errors\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Check Firebase Console for data\n";
echo "2. If no data: Upload & test ESP32 code\n";
echo "3. If data exists: Check FirebaseService path/structure\n";
echo "4. Test dashboard Firebase toggle button\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Firebase Diagnostic Complete\n";
<?php
// Direct controller test without HTTP
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== DIRECT FIREBASE CONTROLLER TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Mock Laravel environment
    $app->make(\Illuminate\Contracts\Http\Kernel::class);
    
    // Create request
    $request = new \Illuminate\Http\Request(['device_id' => 1]);
    
    echo "🧪 Testing getFirebaseData method directly...\n";
    
    // Test Firebase service first
    $firebase = new \App\Services\FirebaseService();
    echo "✅ FirebaseService instantiated\n";
    
    // Test getting Firebase data
    $firebaseData = $firebase->getSensorDataFromFirebase(1);
    echo "📡 Firebase raw data count: " . (is_array($firebaseData) ? count($firebaseData) : 'null') . "\n";
    
    // Test hourly aggregated data  
    $chartData = $firebase->getHourlyAggregatedData('firebase');
    echo "📊 Chart data count: " . $chartData->count() . "\n";
    
    if ($chartData->count() > 0) {
        echo "✅ Chart data available!\n";
        echo "First chart entry: " . json_encode($chartData->first()) . "\n";
    } else {
        echo "⚠️ No chart data from Firebase service\n";
    }
    
    // Now test the controller
    $controller = new \App\Http\Controllers\DashboardController();
    $response = $controller->getFirebaseData($request);
    
    $data = $response->getData(true);
    
    echo "\n=== FIREBASE CONTROLLER RESPONSE ===\n";
    echo "Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Source: " . ($data['source'] ?? 'N/A') . "\n";
    echo "Message: " . ($data['message'] ?? 'N/A') . "\n";
    echo "Chart Data count: " . count($data['data'] ?? []) . "\n";
    
    if (!empty($data['data'])) {
        echo "\n📊 CHART DATA SAMPLE:\n";
        $sample = array_slice($data['data'], 0, 3);
        foreach ($sample as $i => $entry) {
            echo "  " . ($i + 1) . ". Time: " . $entry['time'] . " - Temp: " . $entry['temperature'] . "°C - pH: " . $entry['ph'] . " - O2: " . $entry['oxygen'] . "\n";
        }
        
        echo "\n🎯 CHART DATA IS AVAILABLE! The problem is elsewhere.\n";
    } else {
        echo "\n❌ NO CHART DATA - This is the root problem!\n";
    }
    
    if (isset($data['error'])) {
        echo "⚠️ Error in response: " . $data['error'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== END DIRECT TEST ===\n";
?>

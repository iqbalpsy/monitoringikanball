<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a fake request
$request = Request::create('/api/sensor-data', 'GET', ['type' => 'dashboard']);

echo "=== DIRECT CONTROLLER TEST - Firebase Chart Debug ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Get the controller
    $controller = new App\Http\Controllers\DashboardController();
    
    // Call the method directly
    $response = $controller->getSensorData($request);
    
    $data = $response->getData(true);
    
    echo "✅ Controller method called successfully\n";
    echo "Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Source: " . ($data['source'] ?? 'N/A') . "\n";
    echo "Message: " . ($data['message'] ?? 'N/A') . "\n";
    echo "Data count: " . count($data['data'] ?? []) . "\n";
    
    if (!empty($data['latest'])) {
        echo "\n=== LATEST DATA ===\n";
        echo "Temperature: " . $data['latest']['temperature'] . "°C\n";
        echo "pH: " . $data['latest']['ph'] . "\n";
        echo "Oxygen: " . $data['latest']['oxygen'] . " mg/L\n";
        echo "Voltage: " . $data['latest']['voltage'] . "V\n";
        echo "Timestamp: " . $data['latest']['timestamp'] . "\n";
    }
    
    if (!empty($data['data'])) {
        echo "\n=== CHART DATA (First 3 entries) ===\n";
        $chartData = array_slice($data['data'], 0, 3);
        foreach ($chartData as $i => $entry) {
            echo "Entry " . ($i + 1) . ":\n";
            echo "  Time: " . ($entry['time'] ?? 'N/A') . "\n";
            echo "  Temperature: " . ($entry['temperature'] ?? 'N/A') . "°C\n";
            echo "  pH: " . ($entry['ph'] ?? 'N/A') . "\n";
            echo "  Oxygen: " . ($entry['oxygen'] ?? 'N/A') . " mg/L\n";
            echo "\n";
        }
        
        echo "Total chart entries: " . count($data['data']) . "\n";
        
        if (count($data['data']) > 0) {
            echo "\n🎯 CHART DATA IS AVAILABLE - CHART SHOULD WORK!\n";
        } else {
            echo "\n❌ NO CHART DATA - THIS IS THE PROBLEM\n";
        }
    } else {
        echo "\n❌ NO CHART DATA AVAILABLE\n";
    }
    
    echo "\n=== FULL JSON RESPONSE ===\n";
    echo json_encode($data, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}

echo "\n\n=== END DIRECT TEST ===\n";
?>
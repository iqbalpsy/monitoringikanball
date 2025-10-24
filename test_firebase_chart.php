<?php
/**
 * Test Firebase Chart Data Fix
 */

echo "📊 Testing Firebase Chart Data Fix\n";
echo "=" . str_repeat("=", 50) . "\n\n";

function testFirebaseChartData($url, $description) {
    echo "🔍 Testing: {$description}\n";
    echo "URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ SUCCESS - Firebase API Response:\n";
            echo "  Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "  Source: " . ($data['source'] ?? 'unknown') . "\n";
            echo "  Fallback: " . ($data['fallback'] ? 'true' : 'false') . "\n";
            
            if (isset($data['data']) && is_array($data['data'])) {
                echo "  📊 Chart Data: " . count($data['data']) . " points\n";
                if (count($data['data']) > 0) {
                    echo "  ✅ CHART WILL DISPLAY!\n";
                    echo "  Sample point: " . json_encode($data['data'][0]) . "\n";
                    
                    // Verify data structure
                    $firstPoint = $data['data'][0];
                    $hasTemp = isset($firstPoint['temperature']);
                    $hasPh = isset($firstPoint['ph']);
                    $hasOxygen = isset($firstPoint['oxygen']);
                    $hasTime = isset($firstPoint['time']);
                    
                    echo "  Data structure check:\n";
                    echo "    Temperature: " . ($hasTemp ? '✅' : '❌') . "\n";
                    echo "    pH: " . ($hasPh ? '✅' : '❌') . "\n";
                    echo "    Oxygen: " . ($hasOxygen ? '✅' : '❌') . "\n";
                    echo "    Time: " . ($hasTime ? '✅' : '❌') . "\n";
                    
                    if ($hasTemp && $hasPh && $hasOxygen && $hasTime) {
                        echo "  🎉 PERFECT: Chart data structure is complete!\n";
                    }
                } else {
                    echo "  ❌ EMPTY CHART DATA - Chart will not display\n";
                }
            } else {
                echo "  ❌ NO CHART DATA PROPERTY\n";
            }
            
            if (isset($data['latest'])) {
                echo "  📋 Latest Values:\n";
                echo "    Temperature: " . $data['latest']['temperature'] . "°C\n";
                echo "    pH: " . $data['latest']['ph'] . "\n";
                echo "    Oxygen: " . $data['latest']['oxygen'] . " mg/L\n";
            }
            
            if (isset($data['info'])) {
                echo "  ℹ️  Info: " . $data['info'] . "\n";
            }
        }
    } else {
        echo "❌ Error: HTTP {$httpCode}\n";
    }
    echo "\n";
}

// Test Firebase endpoint
testFirebaseChartData(
    'http://localhost/monitoringikanball/monitoringikanball/public/public-api/firebase-test',
    'Firebase Chart Data Endpoint'
);

echo "🎯 CHART FIX VERIFICATION:\n";
echo "-" . str_repeat("-", 30) . "\n";
echo "If 'Chart Data' shows > 0 points with complete structure,\n";
echo "then Firebase chart should now display properly!\n";
echo "\n";
echo "To test in browser:\n";
echo "1. Login to dashboard\n";
echo "2. Click 'Firebase' button\n";
echo "3. Check browser console for chart logs\n";
echo "4. Verify chart displays sample data\n";

echo "\n🏁 Chart Fix Test Complete\n";
?>
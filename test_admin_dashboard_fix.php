<?php
/**
 * Quick Test for Admin Dashboard Error Fix
 */

require_once 'vendor/autoload.php';

echo "🧪 Testing Admin Dashboard Error Fix\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$url = 'http://127.0.0.1:8001/admin/dashboard';

echo "📊 Testing Admin Dashboard Load\n";
echo "URL: {$url}\n\n";

// Create context for the request
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Accept: text/html',
            'User-Agent: PHP-Test-Agent'
        ],
        'timeout' => 15
    ]
]);

try {
    echo "⏳ Loading admin dashboard...\n";
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to load admin dashboard\n";
        echo "This could mean:\n";
        echo "- Authentication required (admin login needed)\n";
        echo "- Server error occurred\n";
        echo "- Route not accessible\n";
        exit(1);
    }
    
    // Check for error indicators
    if (strpos($response, 'ErrorException') !== false) {
        echo "❌ Error found in response\n";
        echo "Response contains ErrorException\n";
        
        // Try to extract error message
        if (preg_match('/Undefined variable \$\w+/', $response, $matches)) {
            echo "Error: {$matches[0]}\n";
        }
        exit(1);
    }
    
    if (strpos($response, 'Internal Server Error') !== false) {
        echo "❌ Internal Server Error detected\n";
        exit(1);
    }
    
    // Check for successful load indicators
    if (strpos($response, 'Admin Dashboard') !== false || 
        strpos($response, 'admin-temp-value') !== false ||
        strpos($response, 'Dashboard Overview') !== false) {
        
        echo "✅ Admin dashboard loaded successfully!\n\n";
        
        echo "📊 Dashboard Analysis:\n";
        
        // Check for key elements
        if (strpos($response, 'admin-temp-value') !== false) {
            echo "- ✅ Temperature card elements found\n";
        }
        if (strpos($response, 'admin-ph-value') !== false) {
            echo "- ✅ pH card elements found\n";
        }
        if (strpos($response, 'admin-oxygen-value') !== false) {
            echo "- ✅ Oxygen card elements found\n";
        }
        if (strpos($response, 'Firebase') !== false) {
            echo "- ✅ Firebase integration UI found\n";
        }
        
        $responseSize = strlen($response);
        echo "- 📏 Response size: " . number_format($responseSize) . " bytes\n";
        
        echo "\n🎯 Admin Dashboard Status: WORKING ✅\n";
        echo "🔧 $latestData error: FIXED ✅\n";
        
    } else {
        echo "⚠️ Dashboard loaded but content may be incomplete\n";
        echo "Response size: " . strlen($response) . " bytes\n";
        
        if (strlen($response) < 1000) {
            echo "Response preview:\n";
            echo substr($response, 0, 500) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Admin Dashboard Error Fix Test Complete\n";
echo "\nNext steps:\n";
echo "1. Login as admin at: http://127.0.0.1:8001/login\n";
echo "2. Access admin dashboard to verify visual display\n";
echo "3. Test Firebase integration toggle buttons\n";
echo "4. Verify real-time sensor cards update correctly\n";
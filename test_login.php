<?php
echo "=== LOGIN TEST ===\n";

$loginUrl = 'http://127.0.0.1:8000/auth/login';
$email = 'admin@admin.com';
$password = 'admin123';

// Get CSRF token first
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$loginPage = @file_get_contents('http://127.0.0.1:8000/', false, $context);

if ($loginPage === false) {
    echo "❌ Cannot access login page\n";
    exit;
}

// Extract CSRF token
preg_match('/<input type="hidden" name="_token" value="([^"]+)"/', $loginPage, $matches);
if (empty($matches[1])) {
    echo "❌ Cannot extract CSRF token\n";
    exit;
}

$csrfToken = $matches[1];
echo "✅ CSRF Token: $csrfToken\n";

// Prepare login data
$postData = http_build_query([
    '_token' => $csrfToken,
    'email' => $email,
    'password' => $password
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postData,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$loginResponse = @file_get_contents($loginUrl, false, $context);

if ($loginResponse === false) {
    echo "❌ Login failed\n";
} else {
    // Check if redirected to dashboard
    if (strpos($loginResponse, 'dashboard') !== false || strpos($loginResponse, 'Logout') !== false) {
        echo "✅ Login successful\n";
        
        // Extract session cookies from headers
        $headers = $http_response_header ?? [];
        $cookies = [];
        foreach ($headers as $header) {
            if (strpos($header, 'Set-Cookie:') === 0) {
                $cookies[] = trim(substr($header, 11));
            }
        }
        
        if (!empty($cookies)) {
            $cookieHeader = 'Cookie: ' . implode('; ', array_map(function($cookie) {
                return explode(';', $cookie)[0];
            }, $cookies));
            
            echo "Cookies: $cookieHeader\n";
            
            // Now test API with cookies
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => $cookieHeader,
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $apiResponse = @file_get_contents('http://127.0.0.1:8000/api/sensor-data?type=dashboard', false, $context);
            
            if ($apiResponse !== false) {
                echo "\n=== API TEST WITH SESSION ===\n";
                $data = json_decode($apiResponse, true);
                if ($data) {
                    echo "Success: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
                    echo "Source: " . ($data['source'] ?? 'N/A') . "\n";
                    echo "Data count: " . count($data['data'] ?? []) . "\n";
                    echo "Chart data available: " . (!empty($data['data']) ? 'YES' : 'NO') . "\n";
                } else {
                    echo "❌ API returned invalid JSON\n";
                    echo "Response: " . substr($apiResponse, 0, 200) . "\n";
                }
            } else {
                echo "❌ API call failed even with session\n";
            }
        }
    } else {
        echo "❌ Login failed - no redirect to dashboard\n";
        echo "Response: " . substr($loginResponse, 0, 200) . "\n";
    }
}

echo "\n=== END LOGIN TEST ===\n";
?>
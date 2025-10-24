<?php
echo "Testing current IP endpoint...\n";
$url = 'http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store';
$data = json_encode(['device_id'=>1,'ph'=>4.0,'temperature'=>26.5,'oxygen'=>6.8,'voltage'=>3.3]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $code\n";
if ($error) {
    echo "Error: $error\n";
} else {
    echo "Response: " . substr($response, 0, 100) . "...\n";
}
?>
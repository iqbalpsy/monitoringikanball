<?php
/**
 * Cek sumber data sensor di database
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==================================================\n";
echo "Cek Sumber Data Sensor di Database\n";
echo "==================================================\n\n";

try {
    $sensorData = \App\Models\SensorData::orderBy('recorded_at', 'desc')
        ->limit(10)
        ->get();
    
    if ($sensorData->count() > 0) {
        echo "Total data: " . $sensorData->count() . " (showing last 10)\n\n";
        
        foreach ($sensorData as $data) {
            echo "ID: {$data->id}\n";
            echo "  Device: {$data->device_id}\n";
            echo "  pH: {$data->ph}\n";
            echo "  Temperature: {$data->temperature}°C\n";
            echo "  Oxygen: {$data->oxygen} mg/L\n";
            echo "  Recorded: {$data->recorded_at}\n";
            echo "  Created: {$data->created_at}\n";
            echo "  ---\n";
        }
        
        echo "\n==================================================\n";
        echo "Analisis:\n";
        echo "==================================================\n\n";
        
        // Cek data terakhir
        $latest = $sensorData->first();
        $daysDiff = now()->diffInDays($latest->recorded_at);
        $hoursDiff = now()->diffInHours($latest->recorded_at);
        $minutesDiff = now()->diffInMinutes($latest->recorded_at);
        
        echo "Data terakhir:\n";
        echo "  - Timestamp: {$latest->recorded_at}\n";
        
        if ($minutesDiff < 5) {
            echo "  - Status: 🟢 REAL-TIME (kurang dari 5 menit yang lalu)\n";
            echo "  - Sumber: Kemungkinan dari IoT device yang aktif\n";
        } elseif ($hoursDiff < 24) {
            echo "  - Status: 🟡 RECENT (beberapa jam yang lalu)\n";
            echo "  - Sumber: Bisa dari IoT atau input manual\n";
        } else {
            echo "  - Status: 🔴 OLD DATA ({$daysDiff} hari yang lalu)\n";
            echo "  - Sumber: Data lama / dummy / testing\n";
        }
        
        // Cek pattern data
        echo "\nPattern data:\n";
        $devices = $sensorData->pluck('device_id')->unique();
        echo "  - Jumlah device: " . $devices->count() . "\n";
        echo "  - Device IDs: " . $devices->implode(', ') . "\n";
        
        // Cek interval
        if ($sensorData->count() >= 2) {
            $first = $sensorData->get(0)->recorded_at;
            $second = $sensorData->get(1)->recorded_at;
            $interval = $first->diffInSeconds($second);
            
            echo "\nInterval antar data:\n";
            echo "  - {$interval} detik antara 2 data terakhir\n";
            
            if ($interval >= 25 && $interval <= 35) {
                echo "  - Pattern: 🤖 AUTO-SEND (interval ~30 detik)\n";
                echo "  - Sumber: Kemungkinan besar dari ESP32 IoT\n";
            } else {
                echo "  - Pattern: 📝 MANUAL atau IRREGULAR\n";
                echo "  - Sumber: Input manual atau testing\n";
            }
        }
        
    } else {
        echo "❌ Tidak ada data sensor di database.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n==================================================\n";
echo "Kesimpulan:\n";
echo "==================================================\n\n";

echo "Untuk mendapatkan data REAL dari IoT:\n";
echo "1. Upload code ESP32_pH_XAMPP_Code.ino ke board\n";
echo "2. Pastikan WiFi connect\n";
echo "3. Kalibrasi sensor (save7, save4)\n";
echo "4. Data akan auto-send tiap 30 detik\n";
echo "5. Dashboard akan menampilkan data real-time\n\n";
?>

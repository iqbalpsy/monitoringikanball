<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "  DATABASE CONNECTION TEST\n";
echo "===========================================\n\n";

// Test 1: Basic Connection
echo "📡 Testing database connection...\n";

try {
    DB::connection()->getPdo();
    echo "✅ SUCCESS: Connected to database\n\n";
    
    // Test 2: Database Name
    $dbName = DB::connection()->getDatabaseName();
    echo "📊 Database Name: {$dbName}\n\n";
    
    // Test 3: List Tables
    $tables = DB::select('SHOW TABLES');
    echo "📋 Total Tables: " . count($tables) . "\n\n";
    
    if (count($tables) > 0) {
        echo "Tables Found:\n";
        echo "-------------\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            
            // Get row count
            $count = DB::table($tableName)->count();
            echo "  • {$tableName} ({$count} rows)\n";
        }
        echo "\n";
    }
    
    // Test 4: Check Important Tables
    echo "🔍 Checking Important Tables:\n";
    echo "-----------------------------\n";
    
    $importantTables = ['users', 'devices', 'sensor_data', 'sessions'];
    foreach ($importantTables as $table) {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            echo "  ✅ {$table}: {$count} records\n";
        } else {
            echo "  ❌ {$table}: NOT FOUND\n";
        }
    }
    echo "\n";
    
    // Test 5: Check Sensor Data
    if (DB::getSchemaBuilder()->hasTable('sensor_data')) {
        $latestSensor = DB::table('sensor_data')
            ->orderBy('recorded_at', 'desc')
            ->first();
        
        if ($latestSensor) {
            echo "🌡️  Latest Sensor Data:\n";
            echo "----------------------\n";
            echo "  Device ID: {$latestSensor->device_id}\n";
            echo "  Temperature: {$latestSensor->temperature}°C\n";
            echo "  pH: {$latestSensor->ph}\n";
            echo "  Oxygen: {$latestSensor->oxygen} mg/L\n";
            echo "  Recorded: {$latestSensor->recorded_at}\n";
            echo "\n";
        }
    }
    
    // Test 6: Connection Details
    echo "⚙️  Connection Details:\n";
    echo "----------------------\n";
    echo "  Driver: " . DB::connection()->getDriverName() . "\n";
    echo "  Host: " . config('database.connections.mysql.host') . "\n";
    echo "  Port: " . config('database.connections.mysql.port') . "\n";
    echo "  Database: " . config('database.connections.mysql.database') . "\n";
    echo "  Username: " . config('database.connections.mysql.username') . "\n";
    echo "\n";
    
    echo "===========================================\n";
    echo "✅ ALL TESTS PASSED!\n";
    echo "===========================================\n";
    
} catch (\Exception $e) {
    echo "❌ FAILED: Cannot connect to database\n\n";
    echo "Error Details:\n";
    echo "--------------\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
    
    echo "💡 Troubleshooting Steps:\n";
    echo "-------------------------\n";
    echo "1. Check if MySQL is running in XAMPP Control Panel\n";
    echo "2. Verify .env file settings:\n";
    echo "   - DB_HOST=" . env('DB_HOST') . "\n";
    echo "   - DB_PORT=" . env('DB_PORT') . "\n";
    echo "   - DB_DATABASE=" . env('DB_DATABASE') . "\n";
    echo "   - DB_USERNAME=" . env('DB_USERNAME') . "\n";
    echo "\n";
    echo "3. Check if port 3306 is free:\n";
    echo "   netstat -ano | findstr :3306\n";
    echo "\n";
    echo "4. Try to connect manually:\n";
    echo "   mysql -u root -p\n";
    echo "\n";
    
    exit(1);
}

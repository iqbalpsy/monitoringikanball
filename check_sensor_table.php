<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking sensor_data table structure...\n\n";

try {
    // Check if table exists
    if (!Schema::hasTable('sensor_data')) {
        echo "❌ Table 'sensor_data' does NOT exist!\n";
        echo "Please run: php artisan migrate\n";
        exit(1);
    }
    
    echo "✅ Table 'sensor_data' exists\n\n";
    
    // Get all columns
    $columns = DB::select('DESCRIBE sensor_data');
    
    echo "Table Columns:\n";
    echo "=============\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\n";
    
    // Count records
    $count = DB::table('sensor_data')->count();
    echo "Total records: {$count}\n";
    
    // Show latest record
    $latest = DB::table('sensor_data')->latest('recorded_at')->first();
    if ($latest) {
        echo "\nLatest record:\n";
        echo "- ID: {$latest->id}\n";
        echo "- Device ID: {$latest->device_id}\n";
        
        // Check which columns exist
        if (property_exists($latest, 'ph_level')) {
            echo "- pH Level: {$latest->ph_level}\n";
        } else {
            echo "- pH: " . (property_exists($latest, 'ph') ? $latest->ph : 'N/A') . "\n";
        }
        
        echo "- Temperature: {$latest->temperature}\n";
        
        if (property_exists($latest, 'oxygen_level')) {
            echo "- Oxygen Level: {$latest->oxygen_level}\n";
        } else {
            echo "- Oxygen: " . (property_exists($latest, 'oxygen') ? $latest->oxygen : 'N/A') . "\n";
        }
        
        echo "- Recorded At: {$latest->recorded_at}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Database check complete!\n";

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear old sensor data
        DB::table('sensor_data')->truncate();
        
        // Get devices
        $devices = DB::table('devices')->get();
        
        if ($devices->isEmpty()) {
            return;
        }
        
        // Create hourly sensor data for last 24 hours
        $startTime = Carbon::now()->subDay()->startOfHour();
        
        foreach ($devices as $device) {
            for ($i = 0; $i < 24; $i++) {
                $recordTime = $startTime->copy()->addHours($i);
                
                // Generate realistic varying values based on device
                if (strpos($device->device_id, '001') !== false) {
                    // Device 1 - Kolam A (Lele)
                    DB::table('sensor_data')->insert([
                        'device_id' => $device->id,
                        'ph' => 7.2 + (rand(-30, 30) / 100), // 6.9 - 7.5
                        'temperature' => 27.0 + (rand(-20, 20) / 10), // 25.0 - 29.0
                        'oxygen' => 6.8 + (rand(-15, 15) / 10), // 5.3 - 8.3
                        'recorded_at' => $recordTime,
                        'created_at' => $recordTime,
                        'updated_at' => $recordTime,
                    ]);
                } else {
                    // Device 2 - Kolam B (Nila)
                    DB::table('sensor_data')->insert([
                        'device_id' => $device->id,
                        'ph' => 7.0 + (rand(-25, 35) / 100), // 6.75 - 7.35
                        'temperature' => 28.0 + (rand(-25, 25) / 10), // 25.5 - 30.5
                        'oxygen' => 6.2 + (rand(-12, 18) / 10), // 5.0 - 8.0
                        'recorded_at' => $recordTime,
                        'created_at' => $recordTime,
                        'updated_at' => $recordTime,
                    ]);
                }
            }
            
            // Add current data point (latest reading)
            if (strpos($device->device_id, '001') !== false) {
                DB::table('sensor_data')->insert([
                    'device_id' => $device->id,
                    'ph' => 7.3,
                    'temperature' => 27.5,
                    'oxygen' => 6.9,
                    'recorded_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                DB::table('sensor_data')->insert([
                    'device_id' => $device->id,
                    'ph' => 7.1,
                    'temperature' => 28.2,
                    'oxygen' => 6.5,
                    'recorded_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to rollback - data will remain
    }
};

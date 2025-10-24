<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user or create one
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            // If no admin exists, use first user
            $admin = User::first();
        }
        
        if (!$admin) {
            $this->command->error('No users found! Please create a user first.');
            return;
        }

        $devices = [
            [
                'name' => 'Sensor Kolam 1',
                'device_id' => 'ESP32-001',
                'location' => 'Kolam Utama',
                'description' => 'Sensor monitoring untuk kolam utama',
                'status' => 'online',
                'settings' => json_encode([
                    'sample_rate' => 60,
                    'alert_threshold' => [
                        'ph' => ['min' => 6.5, 'max' => 8.5],
                        'temperature' => ['min' => 24, 'max' => 30],
                        'oxygen' => ['min' => 5, 'max' => 10]
                    ]
                ]),
                'created_by' => $admin->id,
                'is_active' => true,
                'last_seen_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sensor Kolam 2',
                'device_id' => 'ESP8266-001',
                'location' => 'Kolam Pembesaran',
                'description' => 'Sensor monitoring untuk kolam pembesaran',
                'status' => 'online',
                'settings' => json_encode([
                    'sample_rate' => 60,
                    'alert_threshold' => [
                        'ph' => ['min' => 6.5, 'max' => 8.5],
                        'temperature' => ['min' => 24, 'max' => 30],
                        'oxygen' => ['min' => 5, 'max' => 10]
                    ]
                ]),
                'created_by' => $admin->id,
                'is_active' => true,
                'last_seen_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('devices')->insert($devices);
        
        $this->command->info('Devices seeded successfully! Total: ' . count($devices) . ' devices');
    }
}

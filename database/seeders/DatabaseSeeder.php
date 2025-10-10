<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\UserDeviceAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin IoT Fish',
            'email' => 'admin@fishmonitoring.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Regular Users
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Create Devices
        $device1 = Device::create([
            'name' => 'Kolam A - Lele',
            'device_id' => 'IOT_FISH_001',
            'location' => 'Kolam A, Sektor Utara',
            'description' => 'Monitoring kualitas air kolam lele budidaya',
            'status' => 'online',
            'settings' => [
                'ph_min' => 6.5,
                'ph_max' => 8.5,
                'temp_min' => 24,
                'temp_max' => 30,
                'oxygen_min' => 5,
            ],
            'created_by' => $admin->id,
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        $device2 = Device::create([
            'name' => 'Kolam B - Nila',
            'device_id' => 'IOT_FISH_002',
            'location' => 'Kolam B, Sektor Selatan',
            'description' => 'Monitoring kualitas air kolam nila',
            'status' => 'online',
            'settings' => [
                'ph_min' => 6.0,
                'ph_max' => 8.0,
                'temp_min' => 25,
                'temp_max' => 32,
                'oxygen_min' => 4,
            ],
            'created_by' => $admin->id,
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        // Grant access to users
        UserDeviceAccess::grantViewAccess($user1->id, $device1->id, $admin->id);
        UserDeviceAccess::grantViewAccess($user1->id, $device2->id, $admin->id);
        UserDeviceAccess::grantViewAccess($user2->id, $device1->id, $admin->id);

        // Create sample sensor data for last 24 hours
        $startTime = now()->subDay();
        
        for ($i = 0; $i < 144; $i++) { // Every 10 minutes for 24 hours
            $recordTime = $startTime->copy()->addMinutes($i * 10);
            
            // Device 1 data (slightly varying)
            SensorData::create([
                'device_id' => $device1->id,
                'ph_level' => 7.2 + (rand(-20, 20) / 100), // 7.0 - 7.4
                'temperature' => 27 + (rand(-15, 15) / 10), // 25.5 - 28.5
                'oxygen_level' => 6.5 + (rand(-10, 15) / 10), // 5.5 - 8.0
                'turbidity' => 2.5 + (rand(0, 10) / 10), // 2.5 - 3.5
                'recorded_at' => $recordTime,
            ]);

            // Device 2 data (slightly different ranges)
            SensorData::create([
                'device_id' => $device2->id,
                'ph_level' => 7.0 + (rand(-15, 25) / 100), // 6.85 - 7.25
                'temperature' => 28 + (rand(-20, 20) / 10), // 26.0 - 30.0
                'oxygen_level' => 5.8 + (rand(-8, 12) / 10), // 5.0 - 7.0
                'turbidity' => 3.0 + (rand(0, 15) / 10), // 3.0 - 4.5
                'recorded_at' => $recordTime,
            ]);
        }

        echo "Database seeding completed!\n";
        echo "Admin: admin@fishmonitoring.com / password123\n";
        echo "User 1: budi@example.com / password123\n";
        echo "User 2: siti@example.com / password123\n";
        echo "Devices: 2 devices with 24 hours of sensor data\n";
    }
}

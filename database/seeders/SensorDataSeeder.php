<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deviceId = 1; // Assuming device ID 1 exists
        
        // Generate data for the last 7 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(7);
        
        $data = [];
        
        // Generate hourly data
        for ($date = $startDate; $date->lte($endDate); $date->addHour()) {
            $data[] = [
                'device_id' => $deviceId,
                'ph_level' => $this->generatePh(),
                'temperature' => $this->generateTemperature(),
                'oxygen_level' => $this->generateOxygen(),
                'turbidity' => $this->generateTurbidity(),
                'raw_data' => null,
                'recorded_at' => $date->format('Y-m-d H:i:s'),
                'created_at' => $date->format('Y-m-d H:i:s'),
                'updated_at' => $date->format('Y-m-d H:i:s'),
            ];
        }
        
        // Insert in chunks for better performance
        $chunks = array_chunk($data, 100);
        foreach ($chunks as $chunk) {
            DB::table('sensor_data')->insert($chunk);
        }
        
        $this->command->info('Sensor data seeded successfully! Total: ' . count($data) . ' records');
    }
    
    /**
     * Generate realistic pH level (6.5 - 8.5)
     */
    private function generatePh(): float
    {
        return round(mt_rand(650, 850) / 100, 2);
    }
    
    /**
     * Generate realistic temperature (24 - 30°C)
     */
    private function generateTemperature(): float
    {
        return round(mt_rand(2400, 3000) / 100, 2);
    }
    
    /**
     * Generate realistic oxygen level (5 - 8 mg/L)
     */
    private function generateOxygen(): float
    {
        return round(mt_rand(500, 800) / 100, 2);
    }
    
    /**
     * Generate realistic turbidity (0 - 10 NTU)
     */
    private function generateTurbidity(): float
    {
        return round(mt_rand(0, 1000) / 100, 2);
    }
}

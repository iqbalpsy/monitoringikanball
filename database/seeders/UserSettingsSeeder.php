<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserSettings;

class UserSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create default settings for each user if not exists
            UserSettings::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'temp_min' => 24.00,
                    'temp_max' => 30.00,
                    'ph_min' => 6.50,
                    'ph_max' => 8.50,
                    'oxygen_min' => 5.00,
                    'oxygen_max' => 8.00,
                    'email_notifications' => true,
                    'push_notifications' => true,
                ]
            );
        }

        $this->command->info('User settings created successfully for all users!');
    }
}

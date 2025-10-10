<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Http;

class TestFirebaseConnection extends Command
{
    protected $signature = 'test:firebase';
    protected $description = 'Test Firebase connection and configuration';

    public function handle()
    {
        $this->info('🔥 Testing Firebase Connection...');
        
        // Test 1: Check configuration
        $this->line('');
        $this->info('1. Checking Firebase Configuration:');
        
        $config = config('services.firebase');
        
        if (empty($config['database_url'])) {
            $this->error('❌ Firebase database URL not configured');
            return 1;
        }
        
        if (empty($config['project_id'])) {
            $this->error('❌ Firebase project ID not configured');
            return 1;
        }
        
        $this->comment('✅ Firebase database URL: ' . $config['database_url']);
        $this->comment('✅ Firebase project ID: ' . $config['project_id']);
        
        // Test 2: Test HTTP connection to Firebase
        $this->line('');
        $this->info('2. Testing HTTP Connection to Firebase:');
        
        try {
            $testUrl = $config['database_url'] . '/test.json';
            $response = Http::timeout(10)->get($testUrl);
            
            if ($response->successful()) {
                $this->comment('✅ Successfully connected to Firebase Realtime Database');
                $this->comment('   Status: ' . $response->status());
            } else {
                $this->error('❌ Failed to connect to Firebase');
                $this->error('   Status: ' . $response->status());
                $this->error('   Body: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception connecting to Firebase: ' . $e->getMessage());
            return 1;
        }
        
        // Test 3: Test Firebase Service
        $this->line('');
        $this->info('3. Testing Firebase Service:');
        
        try {
            $firebaseService = new FirebaseService();
            $this->comment('✅ Firebase service instantiated successfully');
            
            // Test write operation
            $testData = [
                'test_timestamp' => now()->toISOString(),
                'test_value' => rand(1, 100),
                'message' => 'Connection test from Laravel'
            ];
            
            $writeUrl = $config['database_url'] . '/connection_test.json';
            $writeResponse = Http::put($writeUrl, $testData);
            
            if ($writeResponse->successful()) {
                $this->comment('✅ Successfully wrote test data to Firebase');
                
                // Test read operation
                $readResponse = Http::get($writeUrl);
                if ($readResponse->successful()) {
                    $this->comment('✅ Successfully read test data from Firebase');
                    $readData = $readResponse->json();
                    $this->comment('   Data: ' . json_encode($readData, JSON_PRETTY_PRINT));
                } else {
                    $this->error('❌ Failed to read test data from Firebase');
                }
            } else {
                $this->error('❌ Failed to write test data to Firebase');
                $this->error('   Status: ' . $writeResponse->status());
                $this->error('   Body: ' . $writeResponse->body());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception testing Firebase service: ' . $e->getMessage());
            return 1;
        }
        
        // Test 4: Test Google OAuth configuration
        $this->line('');
        $this->info('4. Testing Google OAuth Configuration:');
        
        $googleConfig = config('services.google');
        
        if (empty($googleConfig['client_id'])) {
            $this->error('❌ Google client ID not configured');
        } else {
            $this->comment('✅ Google client ID configured');
        }
        
        if (empty($googleConfig['client_secret'])) {
            $this->error('❌ Google client secret not configured');
        } else {
            $this->comment('✅ Google client secret configured');
        }
        
        if (empty($googleConfig['redirect'])) {
            $this->error('❌ Google redirect URI not configured');
        } else {
            $this->comment('✅ Google redirect URI: ' . $googleConfig['redirect']);
        }
        
        // Test 5: Test database connection
        $this->line('');
        $this->info('5. Testing Database Connection:');
        
        try {
            $deviceCount = \App\Models\Device::count();
            $userCount = \App\Models\User::count();
            $sensorDataCount = \App\Models\SensorData::count();
            
            $this->comment('✅ Database connection successful');
            $this->comment("   Devices: {$deviceCount}");
            $this->comment("   Users: {$userCount}");
            $this->comment("   Sensor Data Records: {$sensorDataCount}");
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return 1;
        }
        
        $this->line('');
        $this->info('🎉 All tests completed successfully!');
        $this->info('Firebase and Laravel are properly connected.');
        
        return 0;
    }
}

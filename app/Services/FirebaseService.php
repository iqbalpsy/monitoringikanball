<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FirebaseService
{
    protected $databaseUrl;
    protected $projectId;

    public function __construct()
    {
        $this->databaseUrl = config('services.firebase.database_url');
        $this->projectId = config('services.firebase.project_id');
    }

    /**
     * Send sensor data to Firebase Realtime Database
     */
    public function pushSensorData($deviceId, $data)
    {
        try {
            $path = "devices/{$deviceId}/sensor_data";
            $url = "{$this->databaseUrl}/{$path}.json";

            $response = Http::post($url, [
                'timestamp' => now()->toISOString(),
                'ph_level' => $data['ph_level'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'oxygen_level' => $data['oxygen_level'] ?? null,
                'turbidity' => $data['turbidity'] ?? null,
                'device_status' => 'online',
                'recorded_at' => $data['recorded_at'] ?? now()->toISOString(),
            ]);

            if ($response->successful()) {
                Log::info("Sensor data pushed to Firebase for device: {$deviceId}");
                return $response->json();
            }

            Log::error("Failed to push sensor data to Firebase", [
                'device_id' => $deviceId,
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Firebase push error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get latest sensor data from Firebase
     */
    public function getLatestSensorData($deviceId)
    {
        try {
            $path = "devices/{$deviceId}/sensor_data";
            $url = "{$this->databaseUrl}/{$path}.json?orderBy=\"timestamp\"&limitToLast=1";

            $response = Http::get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Firebase get error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send device control command to Firebase
     */
    public function sendDeviceControl($deviceId, $command, $parameters = [])
    {
        try {
            $path = "devices/{$deviceId}/controls";
            $url = "{$this->databaseUrl}/{$path}.json";

            $controlData = [
                'command' => $command,
                'parameters' => $parameters,
                'timestamp' => now()->toISOString(),
                'status' => 'pending',
                'user_id' => Auth::id(),
            ];

            $response = Http::post($url, $controlData);

            if ($response->successful()) {
                Log::info("Control command sent to Firebase", [
                    'device_id' => $deviceId,
                    'command' => $command
                ]);
                return $response->json();
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Firebase control error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update device status in Firebase
     */
    public function updateDeviceStatus($deviceId, $status, $additionalData = [])
    {
        try {
            $path = "devices/{$deviceId}/status";
            $url = "{$this->databaseUrl}/{$path}.json";

            $statusData = array_merge([
                'status' => $status,
                'last_updated' => now()->toISOString(),
            ], $additionalData);

            $response = Http::put($url, $statusData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Firebase status update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get device status from Firebase
     */
    public function getDeviceStatus($deviceId)
    {
        try {
            $path = "devices/{$deviceId}/status";
            $url = "{$this->databaseUrl}/{$path}.json";

            $response = Http::get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Firebase get status error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listen to Firebase changes (for real-time updates)
     * This method sets up Server-Sent Events
     */
    public function streamDeviceData($deviceId, $callback = null)
    {
        try {
            $path = "devices/{$deviceId}";
            $url = "{$this->databaseUrl}/{$path}.json";

            // Setup Server-Sent Events stream
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                echo "data: " . json_encode($data) . "\n\n";
                flush();
            }

        } catch (\Exception $e) {
            Log::error("Firebase stream error: " . $e->getMessage());
        }
    }

    /**
     * Create device structure in Firebase
     */
    public function createDeviceInFirebase($deviceId, $deviceData)
    {
        try {
            $path = "devices/{$deviceId}";
            $url = "{$this->databaseUrl}/{$path}.json";

            $firebaseData = [
                'info' => [
                    'name' => $deviceData['name'],
                    'location' => $deviceData['location'] ?? '',
                    'device_id' => $deviceId,
                    'created_at' => now()->toISOString(),
                ],
                'status' => [
                    'status' => 'offline',
                    'last_seen' => null,
                ],
                'sensor_data' => [],
                'controls' => [],
                'settings' => $deviceData['settings'] ?? []
            ];

            $response = Http::put($url, $firebaseData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Firebase create device error: " . $e->getMessage());
            return false;
        }
    }
}

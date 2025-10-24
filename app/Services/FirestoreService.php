<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FirestoreService
{
    private $projectId;
    private $baseUrl;
    
    public function __construct()
    {
        $this->projectId = 'container-kolam';
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    /**
     * Get sensor data from Firestore
     */
    public function getSensorDataFromFirestore($deviceId = 1)
    {
        try {
            // Get data from Firestore collection
            $url = $this->baseUrl . "/sensor_data?orderBy=timestamp%20desc&pageSize=100";
            
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['documents'])) {
                    $sensorData = [];
                    
                    foreach ($data['documents'] as $doc) {
                        $fields = $doc['fields'] ?? [];
                        
                        // Parse Firestore field format
                        $sensorData[] = [
                            'id' => basename($doc['name']),
                            'device_id' => $this->getFirestoreValue($fields['device_id'] ?? null),
                            'temperature' => $this->getFirestoreValue($fields['temperature'] ?? null),
                            'ph' => $this->getFirestoreValue($fields['ph'] ?? null),
                            'oxygen' => $this->getFirestoreValue($fields['oxygen'] ?? null),
                            'voltage' => $this->getFirestoreValue($fields['voltage'] ?? null),
                            'timestamp' => $this->getFirestoreTimestamp($fields['timestamp'] ?? null),
                            'created_at' => $this->getFirestoreTimestamp($fields['created_at'] ?? null)
                        ];
                    }
                    
                    Log::info('Firestore sensor data retrieved', ['count' => count($sensorData)]);
                    return $sensorData;
                }
            }
            
            Log::warning('No Firestore data found');
            return [];
            
        } catch (\Exception $e) {
            Log::error('Firestore connection failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get hourly aggregated data from Firestore
     */
    public function getHourlyAggregatedDataFromFirestore($type = 'working_hours')
    {
        try {
            $sensorData = $this->getSensorDataFromFirestore();
            
            if (empty($sensorData)) {
                return collect();
            }
            
            // Group by hour (working hours 8-17)
            $hourlyData = collect();
            
            for ($hour = 8; $hour <= 17; $hour++) {
                $hourData = collect($sensorData)->filter(function ($item) use ($hour) {
                    $timestamp = $item['timestamp'] ?? null;
                    if (!$timestamp) return false;
                    
                    return Carbon::parse($timestamp)->hour == $hour;
                });
                
                if ($hourData->isNotEmpty()) {
                    $hourlyData->push([
                        'time' => sprintf('%02d:00', $hour),
                        'hour' => $hour,
                        'temperature' => round($hourData->avg('temperature'), 1),
                        'ph' => round($hourData->avg('ph'), 1),
                        'oxygen' => round($hourData->avg('oxygen'), 1),
                        'readings' => $hourData->count()
                    ]);
                }
            }
            
            return $hourlyData;
            
        } catch (\Exception $e) {
            Log::error('Firestore hourly aggregation failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Save sensor data to Firestore
     */
    public function saveSensorDataToFirestore($sensorData)
    {
        try {
            $url = $this->baseUrl . "/sensor_data";
            
            // Format data for Firestore
            $firestoreData = [
                'fields' => [
                    'device_id' => ['integerValue' => (string)($sensorData['device_id'] ?? 1)],
                    'temperature' => ['doubleValue' => (float)($sensorData['temperature'] ?? 0)],
                    'ph' => ['doubleValue' => (float)($sensorData['ph'] ?? 0)],
                    'oxygen' => ['doubleValue' => (float)($sensorData['oxygen'] ?? 0)],
                    'voltage' => ['doubleValue' => (float)($sensorData['voltage'] ?? 0)],
                    'timestamp' => ['timestampValue' => now()->toISOString()],
                    'created_at' => ['timestampValue' => now()->toISOString()]
                ]
            ];
            
            $response = Http::timeout(10)->post($url, $firestoreData);
            
            if ($response->successful()) {
                Log::info('Sensor data saved to Firestore', $sensorData);
                return true;
            }
            
            Log::error('Failed to save to Firestore', ['response' => $response->body()]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('Firestore save failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Parse Firestore field value
     */
    private function getFirestoreValue($field)
    {
        if (!$field) return null;
        
        if (isset($field['doubleValue'])) {
            return (float)$field['doubleValue'];
        }
        
        if (isset($field['integerValue'])) {
            return (int)$field['integerValue'];
        }
        
        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        }
        
        return null;
    }

    /**
     * Parse Firestore timestamp
     */
    private function getFirestoreTimestamp($field)
    {
        if (!$field || !isset($field['timestampValue'])) {
            return now()->toISOString();
        }
        
        return $field['timestampValue'];
    }

    /**
     * Test Firestore connection
     */
    public function testFirestoreConnection()
    {
        try {
            $url = $this->baseUrl . "/sensor_data?pageSize=1";
            $response = Http::timeout(5)->get($url);
            
            return $response->successful();
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\DeviceControl;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IoTController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get user's accessible devices
     */
    public function getDevices(Request $request)
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            // Admin can see all devices
            $devices = Device::with(['latestSensorData', 'creator'])
                ->where('is_active', true)
                ->get();
        } else {
            // Regular user can only see devices they have access to
            $devices = $user->accessibleDevices()
                ->where('devices.is_active', true)
                ->where('user_device_access.can_view_data', true)
                ->with(['latestSensorData'])
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $devices->map(function($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'device_id' => $device->device_id,
                    'location' => $device->location,
                    'status' => $device->status,
                    'last_seen_at' => $device->last_seen_at,
                    'latest_data' => $device->latestSensorData ? [
                        'ph_level' => $device->latestSensorData->ph_level,
                        'temperature' => $device->latestSensorData->temperature,
                        'oxygen_level' => $device->latestSensorData->oxygen_level,
                        'turbidity' => $device->latestSensorData->turbidity,
                        'recorded_at' => $device->latestSensorData->recorded_at,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * Get device details
     */
    public function getDevice(Request $request, $deviceId)
    {
        $user = $request->user();
        
        $device = Device::find($deviceId);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        // Check access
        if (!$user->isAdmin()) {
            $hasAccess = $user->accessibleDevices()
                ->where('devices.id', $deviceId)
                ->where('user_device_access.can_view_data', true)
                ->exists();
                
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke device ini'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $device->id,
                'name' => $device->name,
                'device_id' => $device->device_id,
                'location' => $device->location,
                'description' => $device->description,
                'status' => $device->status,
                'settings' => $device->settings,
                'last_seen_at' => $device->last_seen_at,
                'created_at' => $device->created_at,
            ]
        ]);
    }

    /**
     * Get sensor data for a device
     */
    public function getSensorData(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check device exists and user has access
        $device = Device::find($deviceId);
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        if (!$user->isAdmin()) {
            $hasAccess = $user->accessibleDevices()
                ->where('devices.id', $deviceId)
                ->where('user_device_access.can_view_data', true)
                ->exists();
                
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke device ini'
                ], 403);
            }
        }

        // Get query parameters
        $limit = $request->get('limit', 100);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = SensorData::where('device_id', $deviceId)
            ->orderBy('recorded_at', 'desc');

        if ($startDate && $endDate) {
            $query->inTimeRange($startDate, $endDate);
        }

        $sensorData = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $sensorData,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'device_id' => $device->device_id,
            ]
        ]);
    }

    /**
     * Receive sensor data from IoT device
     */
    public function receiveSensorData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|exists:devices,device_id',
            'ph_level' => 'nullable|numeric|between:0,14',
            'temperature' => 'nullable|numeric|between:-10,50',
            'oxygen_level' => 'nullable|numeric|min:0',
            'turbidity' => 'nullable|numeric|min:0',
            'recorded_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $device = Device::where('device_id', $request->device_id)->first();
            
            // Update device last seen
            $device->updateLastSeen();
            $device->update(['status' => 'online']);

            // Save to database
            $sensorData = SensorData::create([
                'device_id' => $device->id,
                'ph_level' => $request->ph_level,
                'temperature' => $request->temperature,
                'oxygen_level' => $request->oxygen_level,
                'turbidity' => $request->turbidity,
                'recorded_at' => $request->recorded_at ?? now(),
            ]);

            // Push to Firebase for real-time updates
            $this->firebaseService->pushSensorData($device->device_id, [
                'ph_level' => $request->ph_level,
                'temperature' => $request->temperature,
                'oxygen_level' => $request->oxygen_level,
                'turbidity' => $request->turbidity,
                'recorded_at' => $sensorData->recorded_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data sensor berhasil diterima',
                'data' => $sensorData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data sensor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send control command to device (Admin only)
     */
    public function sendControl(Request $request, $deviceId)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat mengontrol device'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|string',
            'parameters' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $device = Device::find($deviceId);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device tidak ditemukan'
                ], 404);
            }

            // Save control command to database
            $control = DeviceControl::create([
                'device_id' => $device->id,
                'user_id' => $user->id,
                'action' => $request->action,
                'parameters' => $request->parameters ?? [],
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Send command to Firebase for IoT device to pick up
            $this->firebaseService->sendDeviceControl(
                $device->device_id,
                $request->action,
                $request->parameters ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Perintah kontrol berhasil dikirim',
                'data' => $control
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah kontrol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get control history for a device
     */
    public function getControlHistory(Request $request, $deviceId)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat melihat riwayat kontrol'
            ], 403);
        }

        $device = Device::find($deviceId);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        $controls = DeviceControl::where('device_id', $deviceId)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $controls,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'device_id' => $device->device_id,
            ]
        ]);
    }

    /**
     * Stream real-time data for a device
     */
    public function streamDeviceData(Request $request, $deviceId)
    {
        $user = $request->user();
        
        $device = Device::find($deviceId);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        // Check access
        if (!$user->isAdmin()) {
            $hasAccess = $user->accessibleDevices()
                ->where('devices.id', $deviceId)
                ->where('user_device_access.can_view_data', true)
                ->exists();
                
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke device ini'
                ], 403);
            }
        }

        // Set up Server-Sent Events for real-time streaming
        return response()->stream(function () use ($device) {
            $this->firebaseService->streamDeviceData($device->device_id);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}

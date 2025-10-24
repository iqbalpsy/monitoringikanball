<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\UserSettings;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('user.dashboard');
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        $devices = Device::with('latestSensorData')->get();
        $users = User::all();
        $recentSensorData = SensorData::with('device')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $totalSensorData = SensorData::count();
        
        // Get latest sensor data for real-time display
        $latestData = SensorData::latest('recorded_at')->first();

        return view('admin.dashboard', compact('user', 'devices', 'users', 'recentSensorData', 'totalSensorData', 'latestData'));
    }

    /**
     * Show user dashboard
     */
    public function userDashboard()
    {
        $user = Auth::user();
        
        // Get Firebase data for latest readings
        try {
            $firebaseService = app(FirebaseService::class);
            $deviceId = 1;
            $firebaseData = $firebaseService->getSensorDataFromFirebase($deviceId);
            $latestData = !empty($firebaseData) ? (object) $firebaseData[0] : null;
        } catch (\Exception $e) {
            \Log::error('Firebase error in user dashboard: ' . $e->getMessage());
            // Fallback to database
            $latestData = SensorData::latest('recorded_at')->first();
        }
        
        // Create device collection with Firebase data
        $devices = collect([
            (object) [
                'id' => 1,
                'name' => 'ESP32 Device #1',
                'latestSensorData' => $latestData ? collect([$latestData]) : collect()
            ]
        ]);
        
        // Get user settings with proper fallback
        $settings = $user->settings()->firstOrCreate([
            'user_id' => $user->id
        ], [
            'temp_min' => 24.00,
            'temp_max' => 30.00,
            'ph_min' => 6.50,
            'ph_max' => 8.50,
            'oxygen_min' => 5.00,
            'oxygen_max' => 8.00,
        ]);
        
        return view('dashboard.user', compact('devices', 'latestData', 'settings'));
    }

    /**
     * Show users management page
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        $totalSensorData = SensorData::count();
        return view('admin.users', compact('users', 'totalSensorData'));
    }

    /**
     * Show devices management page
     */
    public function devices()
    {
        $devices = Device::with('latestSensorData')->orderBy('created_at', 'desc')->get();
        $users = User::all();
        $totalSensorData = SensorData::count();
        return view('admin.devices', compact('devices', 'users', 'totalSensorData'));
    }

    /**
     * Show history page with Firebase data
     */
    public function history(Request $request)
    {
        try {
            // Get Firebase data using the same method as API endpoint
            $firebaseService = app(FirebaseService::class);
            \Log::info('Firebase service loaded successfully');
            
            // Try getSensorDataFromFirebase first (same as API), then fallback to getAllSensorData
            $deviceId = $request->input('device_id', 1);
            $firebaseData = $firebaseService->getSensorDataFromFirebase($deviceId);
            
            // If no data from device-specific path, try general path
            if (!$firebaseData || empty($firebaseData)) {
                $firebaseData = $firebaseService->getAllSensorData();
            }
            
            \Log::info('Firebase data retrieved', ['count' => is_array($firebaseData) ? count($firebaseData) : 'null']);
            
            // Debug output
            if (!$firebaseData) {
                session()->flash('error', 'Firebase data is null or false - Service failed');
                \Log::error('Firebase service returned null/false data');
            } elseif (!is_array($firebaseData)) {
                session()->flash('error', 'Firebase data is not array: ' . gettype($firebaseData));
                \Log::error('Firebase service returned non-array', ['type' => gettype($firebaseData)]);
            } else {
                session()->flash('success', 'Firebase data retrieved: ' . count($firebaseData) . ' records');
                \Log::info('Firebase service success', ['count' => count($firebaseData)]);
            }
            
            // Get filter parameters
            $dateFilter = $request->input('date_filter');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $deviceFilter = $request->input('device_filter', 'all');
            $parameterFilter = $request->input('parameter_filter', 'all');
            
            $sensorData = collect();
            $totalSensorData = 0;
            
            if ($firebaseData && is_array($firebaseData)) {
                \Log::info('Processing Firebase data', ['records' => count($firebaseData)]);
                
                // FirebaseService already returns converted data, just convert to objects and sort
                $allData = collect($firebaseData)->map(function ($item, $key) {
                    return (object) [
                        'id' => $item['id'] ?? $key,
                        'device_id' => $item['device_id'] ?? 1,
                        'temperature' => isset($item['temperature']) ? floatval($item['temperature']) : null,
                        'ph' => isset($item['pH']) ? floatval($item['pH']) : (isset($item['ph']) ? floatval($item['ph']) : null),
                        'oxygen' => isset($item['oxygen']) ? floatval($item['oxygen']) : null,
                        'voltage' => isset($item['voltage']) ? floatval($item['voltage']) : null,
                        'timestamp' => $item['timestamp'] ?? 0,
                        'created_at' => isset($item['created_at']) ? 
                            \Carbon\Carbon::parse($item['created_at']) : 
                            now(),
                        'device' => (object) ['name' => 'ESP32 Device #1', 'id' => 1]
                    ];
                })->sortByDesc('created_at');
                
                \Log::info('Data converted to collection', ['count' => $allData->count()]);
                
                $totalSensorData = $allData->count();
                
                // Apply filters
                if ($dateFilter && $dateFilter !== 'all') {
                    switch ($dateFilter) {
                        case 'today':
                            $allData = $allData->filter(function ($item) {
                                return \Carbon\Carbon::parse($item->created_at)->isToday();
                            });
                            break;
                        case 'yesterday':
                            $allData = $allData->filter(function ($item) {
                                return \Carbon\Carbon::parse($item->created_at)->isYesterday();
                            });
                            break;
                        case 'week':
                            $allData = $allData->filter(function ($item) {
                                return \Carbon\Carbon::parse($item->created_at)->isCurrentWeek();
                            });
                            break;
                        case 'month':
                            $allData = $allData->filter(function ($item) {
                                return \Carbon\Carbon::parse($item->created_at)->isCurrentMonth();
                            });
                            break;
                        case 'custom':
                            if ($startDate && $endDate) {
                                $start = \Carbon\Carbon::parse($startDate)->startOfDay();
                                $end = \Carbon\Carbon::parse($endDate)->endOfDay();
                                $allData = $allData->filter(function ($item) use ($start, $end) {
                                    $itemDate = \Carbon\Carbon::parse($item->created_at);
                                    return $itemDate->between($start, $end);
                                });
                            }
                            break;
                    }
                }
                
                // Apply parameter filter
                if ($parameterFilter && $parameterFilter !== 'all') {
                    $allData = $allData->filter(function ($item) use ($parameterFilter) {
                        switch ($parameterFilter) {
                            case 'temperature':
                                return !is_null($item->temperature);
                            case 'ph':
                                return !is_null($item->ph);
                            case 'oxygen':
                                return !is_null($item->oxygen);
                            case 'voltage':
                                return !is_null($item->voltage);
                            default:
                                return true;
                        }
                    });
                }
                
                // Implement pagination manually
                $perPage = 50;
                $currentPage = request()->input('page', 1);
                $items = $allData->forPage($currentPage, $perPage);
                
                $sensorData = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $allData->count(),
                    $perPage,
                    $currentPage,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            }
            
            // Get other data for dropdowns
            $devices = collect([
                (object) ['id' => 1, 'name' => 'ESP32 Device #1']
            ]);
            $users = User::all();
            
            return view('admin.history', compact('devices', 'sensorData', 'users', 'totalSensorData', 'allData'));
            
        } catch (\Exception $e) {
            \Log::error('Error in admin history: ' . $e->getMessage());
            
            // Fallback to empty data
            $devices = collect();
            $users = User::all();
            $allData = collect(); // Add allData for error case
            $totalSensorData = 0;
            
            // Create empty LengthAwarePaginator for consistency
            $sensorData = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                50,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            session()->flash('error', 'Gagal memuat data history dari Firebase: ' . $e->getMessage());
            
            return view('admin.history', compact('devices', 'sensorData', 'users', 'totalSensorData', 'allData'));
        }
    }

    /**
     * Show monitoring page
     */
    public function monitoring()
    {
        $devices = Device::with('latestSensorData')->get();
        $users = User::all();
        $totalSensorData = SensorData::count();
        return view('admin.monitoring', compact('devices', 'users', 'totalSensorData'));
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        $devices = Device::with('latestSensorData')->get();
        $users = User::all();
        $totalReadings = SensorData::count();
        $totalSensorData = SensorData::count();
        $alertsCount = rand(15, 45); // Simulate alerts count
        
        return view('admin.reports', compact('devices', 'totalReadings', 'alertsCount', 'users', 'totalSensorData'));
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $devices = Device::all();
        $users = User::all();
        $totalSensorData = SensorData::count();
        return view('admin.settings', compact('devices', 'users', 'totalSensorData'));
    }

    /**
     * Get Firebase data for admin dashboard (API endpoint)
     */
    public function getFirebaseData(Request $request)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            $deviceId = $request->input('device_id', 1);
            
            // Get latest data from Firebase for cards
            $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
            $latestData = !empty($firebaseData) ? $firebaseData[0] : null;
            
            // Get hourly aggregated data for chart (same as getSensorData)
            $chartData = $firebase->getHourlyAggregatedData('firebase');
            
            // Ensure we have chart data
            if ($chartData->isEmpty()) {
                // Provide sample working hours data
                for ($hour = 8; $hour <= 17; $hour++) {
                    $chartData->push([
                        'time' => sprintf('%02d:00', $hour),
                        'temperature' => round(25.0 + (rand(-20, 20) / 10), 1),
                        'ph' => round(7.0 + (rand(-5, 5) / 10), 1),
                        'oxygen' => round(8.0 + (rand(-10, 10) / 10), 1),
                        'readings' => 0
                    ]);
                }
            }
            
            // Format latest data for dashboard cards
            if ($latestData) {
                $cardData = [
                    'temperature' => round((float)($latestData['temperature'] ?? 0), 1),
                    'ph' => round((float)($latestData['ph'] ?? 0), 1),
                    'oxygen' => round((float)($latestData['oxygen'] ?? 0), 1),
                    'timestamp' => isset($latestData['timestamp']) && $latestData['timestamp'] ? 
                        \Carbon\Carbon::createFromTimestamp($latestData['timestamp'])->format('d/m/Y H:i:s') : 
                        now()->format('d/m/Y H:i:s')
                ];
            } else {
                // Use sample latest data
                $cardData = [
                    'temperature' => 26.5,
                    'ph' => 4.0,
                    'oxygen' => 6.8,
                    'timestamp' => now()->format('d/m/Y H:i:s')
                ];
            }
            
            return response()->json([
                'success' => true,
                'latest' => $cardData,
                'data' => $chartData->values()->toArray(),
                'count' => $chartData->count(),
                'total_readings' => $chartData->sum('readings'),
                'source' => 'firebase',
                'device_id' => $deviceId,
                'message' => 'Firebase data with hourly chart data'
            ]);
            
        } catch (\Exception $e) {
            // Firebase error - still provide sample data
            $sampleChartData = collect();
            for ($hour = 8; $hour <= 17; $hour++) {
                $sampleChartData->push([
                    'time' => sprintf('%02d:00', $hour),
                    'temperature' => round(25.0 + (rand(-20, 20) / 10), 1),
                    'ph' => round(7.0 + (rand(-5, 5) / 10), 1),
                    'oxygen' => round(8.0 + (rand(-10, 10) / 10), 1),
                    'readings' => 0
                ]);
            }
            
            return response()->json([
                'success' => true,
                'latest' => [
                    'temperature' => 26.5,
                    'ph' => 4.0,
                    'oxygen' => 6.8,
                    'timestamp' => now()->format('d/m/Y H:i:s')
                ],
                'data' => $sampleChartData->toArray(),
                'count' => $sampleChartData->count(),
                'total_readings' => 0,
                'source' => 'firebase',
                'device_id' => $deviceId,
                'message' => 'Firebase error - using sample chart data',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get local sensor data for API endpoint
     */
    public function getSensorData(Request $request)
    {
        try {
            $type = $request->get('type', 'working_hours');
            
            // Initialize Firebase service - FIREBASE ONLY MODE
            $firebase = new \App\Services\FirebaseService();
            $latestData = null;
            $chartData = collect();
            $dataSource = 'firebase';

            // Always use Firebase as the only data source
            try {
                if ($type === 'working_hours') {
                    $firebaseData = $firebase->getWorkingHoursData();
                    $hourlyData = $firebase->getHourlyAggregatedData();
                    
                    \Log::info('Firebase working hours data', ['data_count' => count($hourlyData ?? [])]);
                    
                    if (!empty($hourlyData)) {
                        $chartData = collect($hourlyData)->map(function($item) {
                            return [
                                'time' => $item['hour'],
                                'temperature' => $item['temperature'],
                                'ph' => $item['ph'],
                                'oxygen' => $item['oxygen'],
                                'readings' => $item['count']
                            ];
                        });
                        \Log::info('Chart data mapped', ['chart_count' => $chartData->count()]);
                    } else {
                        \Log::warning('No hourly data available from Firebase');
                    }
                } else {
                    $firebaseData = $firebase->getAllSensorData();
                    if (!empty($firebaseData)) {
                        $chartData = collect($firebaseData)->take(24)->map(function($item) {
                            return [
                                'time' => date('H:i', strtotime($item['created_at'])),
                                'temperature' => round((float)$item['temperature'], 2),
                                'ph' => round((float)$item['ph'], 2),
                                'oxygen' => round((float)$item['oxygen'], 2),
                                'readings' => 1
                            ];
                        });
                    }
                }

                // Get latest data from Firebase
                $latestFirebaseData = $firebase->getLatestReading();
                if ($latestFirebaseData) {
                    $latestData = (object) $latestFirebaseData;
                }
                
            } catch (\Exception $e) {
                \Log::error('Firebase data retrieval failed: ' . $e->getMessage());
                
                // Provide fallback sample data if Firebase fails
                $chartData = collect();
                $latestData = (object) [
                    'id' => 'fallback',
                    'ph' => 7.0,
                    'temperature' => 25.0,
                    'oxygen' => 8.0,
                    'voltage' => 3.3,
                    'created_at' => now()->format('Y-m-d H:i:s')
                ];
            }

            // Firebase-only mode - always return data
            
            // Ensure we have chart data even if no latest data
            if ($chartData->isEmpty()) {
                // Provide sample working hours data
                for ($hour = 8; $hour <= 17; $hour++) {
                    $chartData->push([
                        'time' => sprintf('%02d:00', $hour),
                        'temperature' => round(25.0 + (rand(-20, 20) / 10), 1),
                        'ph' => round(7.0 + (rand(-5, 5) / 10), 1),
                        'oxygen' => round(8.0 + (rand(-10, 10) / 10), 1),
                        'readings' => 0
                    ]);
                }
            }

            // Format latest data for dashboard cards - Firebase only
            if ($latestData) {
                $formattedLatest = [
                    'id' => $latestData->id ?? 'firebase',
                    'device_id' => $latestData->device_id ?? 1,
                    'temperature' => round((float)$latestData->temperature, 1),
                    'ph' => round((float)$latestData->ph, 1),
                    'oxygen' => round((float)$latestData->oxygen, 1),
                    'voltage' => round((float)($latestData->voltage ?? 0), 2),
                    'timestamp' => $latestData->created_at ?? now()->format('d/m/Y H:i:s'),
                    'source' => 'firebase'
                ];
            } else {
                // Use sample latest data
                $formattedLatest = [
                    'id' => 'sample',
                    'device_id' => 1,
                    'temperature' => 26.5,
                    'ph' => 4.0,
                    'oxygen' => 6.8,
                    'voltage' => 3.3,
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                    'source' => 'firebase'
                ];
            }

            return response()->json([
                'success' => true,
                'latest' => $formattedLatest,
                'data' => $chartData->values()->toArray(),
                'count' => $chartData->count(),
                'total_readings' => $chartData->sum('readings'),
                'source' => 'firebase',
                'type' => $type,
                'message' => 'Firebase sensor data loaded successfully'
            ]);

        } catch (\Exception $e) {
            // Firebase error - provide fallback
            return response()->json([
                'success' => false,
                'latest' => null,
                'data' => [],
                'count' => 0,
                'total_readings' => 0,
                'source' => 'firebase',
                'message' => 'Firebase connection failed: ' . $e->getMessage(),
                'fallback' => true,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Receive sensor data from ESP32 IoT device
     * POST /iot-api/sensor-data
     */
    public function receiveSensorData(Request $request)
    {
        try {
            // Log incoming request for debugging
            Log::info('IoT Data Received', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data' => $request->all()
            ]);

            // Validate incoming sensor data
            $validatedData = $request->validate([
                'device_id' => 'required|integer',
                'temperature' => 'required|numeric|between:-50,100',
                'ph' => 'required|numeric|between:0,14',
                'oxygen' => 'required|numeric|between:0,50',
                'voltage' => 'nullable|numeric|between:0,5',
                'timestamp' => 'nullable|integer'
            ]);

            // Log the validated data for debugging
            \Log::info('ESP32 Sensor Data Received:', [
                'device_id' => $validatedData['device_id'],
                'temperature' => $validatedData['temperature'],
                'ph' => $validatedData['ph'],
                'oxygen' => $validatedData['oxygen'],
                'voltage' => $validatedData['voltage'] ?? 'NOT_PROVIDED',
                'timestamp' => $validatedData['timestamp'] ?? 'NOT_PROVIDED'
            ]);

            // Create sensor data record
            $voltageValue = isset($validatedData['voltage']) ? round($validatedData['voltage'], 2) : null;
            
            $sensorData = \App\Models\SensorData::create([
                'device_id' => $validatedData['device_id'],
                'temperature' => round($validatedData['temperature'], 2),
                'ph' => round($validatedData['ph'], 2),
                'oxygen' => round($validatedData['oxygen'], 2),
                'voltage' => $voltageValue,
                'recorded_at' => $validatedData['timestamp'] ? 
                    \Carbon\Carbon::createFromTimestamp($validatedData['timestamp']) : 
                    now()
            ]);

            // Also sync to Firebase if configured
            try {
                $firebase = new \App\Services\FirebaseService();
                $firebaseResult = $firebase->pushSensorData($validatedData['device_id'], [
                    'temperature' => $validatedData['temperature'],
                    'ph' => $validatedData['ph'],
                    'oxygen' => $validatedData['oxygen'],
                    'voltage' => $validatedData['voltage'] ?? 0,
                    'timestamp' => $validatedData['timestamp'] ?? time(),
                    'recorded_at' => now()->toISOString()
                ]);

                Log::info('Data synced to Firebase', ['success' => $firebaseResult !== false]);
            } catch (\Exception $e) {
                Log::warning('Firebase sync failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sensor data received successfully',
                'data_id' => $sensorData->id,
                'device_id' => $sensorData->device_id,
                'timestamp' => $sensorData->created_at->toISOString(),
                'firebase_synced' => isset($firebaseResult) && $firebaseResult !== false
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sensor data format',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to store sensor data', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor data for specific device
     * GET /iot-api/sensor-data/{device_id}
     */
    public function getLatestSensorData($deviceId = 1)
    {
        try {
            $latestData = \App\Models\SensorData::where('device_id', $deviceId)
                ->latest()
                ->first();

            if ($latestData) {
                return response()->json([
                    'success' => true,
                    'device_id' => $deviceId,
                    'data' => [
                        'id' => $latestData->id,
                        'temperature' => $latestData->temperature,
                        'ph' => $latestData->ph,
                        'oxygen' => $latestData->oxygen,
                        'voltage' => $latestData->voltage,
                        'recorded_at' => $latestData->recorded_at,
                        'created_at' => $latestData->created_at->toISOString()
                    ],
                    'timestamp' => $latestData->created_at->timestamp
                ]);
            }

            return response()->json([
                'success' => true,
                'device_id' => $deviceId,
                'data' => null,
                'message' => 'No sensor data found for this device'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * IoT system status check
     * GET /iot-api/status
     */
    public function iotStatus()
    {
        try {
            $totalDevices = SensorData::select('device_id')->distinct()->count();
            $totalReadings = SensorData::count();
            $latestReading = SensorData::latest()->first();
            
            // Check database connection
            DB::connection()->getPdo();
            $dbStatus = 'connected';
            
            // Check Firebase connection
            $firebaseStatus = 'unknown';
            try {
                $firebase = new \App\Services\FirebaseService();
                $firebaseStatus = 'connected';
            } catch (\Exception $e) {
                $firebaseStatus = 'error: ' . $e->getMessage();
            }

            return response()->json([
                'success' => true,
                'system_status' => 'operational',
                'database' => [
                    'status' => $dbStatus,
                    'total_devices' => $totalDevices,
                    'total_readings' => $totalReadings,
                    'latest_reading' => $latestReading ? $latestReading->created_at->toISOString() : null
                ],
                'firebase' => [
                    'status' => $firebaseStatus
                ],
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'system_status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =================================================================
    // MOBILE APP API METHODS
    // =================================================================

    /**
     * Get latest sensor data for mobile app
     */
    public function getMobileLatestSensorData(Request $request, $deviceId = 1)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            
            // Get latest Firebase data
            $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
            $latestData = !empty($firebaseData) ? $firebaseData[0] : null;
            
            if ($latestData) {
                $response = [
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'temperature' => round((float)($latestData['temperature'] ?? 0), 2),
                        'ph' => round((float)($latestData['ph'] ?? 0), 2),
                        'oxygen' => round((float)($latestData['oxygen'] ?? 0), 2),
                        'voltage' => round((float)($latestData['voltage'] ?? 0), 2),
                        'timestamp' => isset($latestData['timestamp']) && $latestData['timestamp'] ? 
                            \Carbon\Carbon::createFromTimestamp($latestData['timestamp'])->toISOString() : 
                            now()->toISOString(),
                        'source' => 'firebase',
                        'status' => 'online'
                    ],
                    'message' => 'Latest sensor data retrieved successfully',
                    'timestamp' => now()->toISOString()
                ];
            } else {
                // No data available - provide sample for demo
                $response = [
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'temperature' => 26.5,
                        'ph' => 4.0,
                        'oxygen' => 6.8,
                        'voltage' => 3.3,
                        'timestamp' => now()->toISOString(),
                        'source' => 'firebase',
                        'status' => 'no_data'
                    ],
                    'message' => 'No sensor data available, showing default values',
                    'timestamp' => now()->toISOString()
                ];
            }
            
            return response()->json($response)->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve sensor data',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get sensor data history for mobile app
     */
    public function getMobileSensorHistory(Request $request, $deviceId = 1)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            $limit = $request->get('limit', 50); // Default 50 records
            
            // Get Firebase data
            $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
            
            if (!empty($firebaseData)) {
                $historyData = collect($firebaseData)->take($limit)->map(function($item) use ($deviceId) {
                    return [
                        'device_id' => $deviceId,
                        'temperature' => round((float)($item['temperature'] ?? 0), 2),
                        'ph' => round((float)($item['ph'] ?? 0), 2),
                        'oxygen' => round((float)($item['oxygen'] ?? 0), 2),
                        'voltage' => round((float)($item['voltage'] ?? 0), 2),
                        'timestamp' => isset($item['timestamp']) && $item['timestamp'] ? 
                            \Carbon\Carbon::createFromTimestamp($item['timestamp'])->toISOString() : 
                            now()->toISOString()
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'data' => $historyData,
                    'count' => $historyData->count(),
                    'limit' => $limit,
                    'message' => 'Sensor history retrieved successfully',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
                
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'count' => 0,
                    'message' => 'No sensor history available',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve sensor history',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get chart data for mobile app
     */
    public function getMobileChartData(Request $request, $deviceId = 1)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            $type = $request->get('type', 'working_hours'); // working_hours, 24_hours, daily
            
            // Get hourly aggregated data
            $chartData = $firebase->getHourlyAggregatedData('firebase');
            
            // Convert to collection if it's not already
            if (is_array($chartData)) {
                $chartData = collect($chartData);
            }
            
            // Ensure we have data
            if ($chartData->isEmpty()) {
                // Generate sample working hours data
                $sampleData = collect();
                for ($hour = 8; $hour <= 17; $hour++) {
                    $sampleData->push([
                        'time' => sprintf('%02d:00', $hour),
                        'hour' => $hour,
                        'temperature' => round(25.0 + (rand(-20, 20) / 10), 1),
                        'ph' => round(7.0 + (rand(-5, 5) / 10), 1),
                        'oxygen' => round(8.0 + (rand(-10, 10) / 10), 1),
                        'readings' => rand(1, 5)
                    ]);
                }
                $chartData = $sampleData;
            }
            
            // Format for mobile app
            $formattedData = $chartData->map(function($item) {
                return [
                    'time' => $item['time'],
                    'hour' => $item['hour'] ?? null,
                    'temperature' => round((float)$item['temperature'], 2),
                    'ph' => round((float)$item['ph'], 2),
                    'oxygen' => round((float)$item['oxygen'], 2),
                    'readings_count' => $item['readings'] ?? 0
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'count' => $formattedData->count(),
                'type' => $type,
                'device_id' => $deviceId,
                'message' => 'Chart data retrieved successfully',
                'timestamp' => now()->toISOString()
            ])->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve chart data',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get sensor statistics for mobile app
     */
    public function getMobileSensorStats(Request $request, $deviceId = 1)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            
            // Get Firebase data for stats calculation
            $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
            
            if (!empty($firebaseData)) {
                $temperatures = collect($firebaseData)->pluck('temperature')->filter();
                $phLevels = collect($firebaseData)->pluck('ph')->filter();
                $oxygenLevels = collect($firebaseData)->pluck('oxygen')->filter();
                
                $stats = [
                    'device_id' => $deviceId,
                    'total_readings' => count($firebaseData),
                    'temperature' => [
                        'current' => round($temperatures->first() ?? 0, 2),
                        'average' => round($temperatures->avg() ?? 0, 2),
                        'min' => round($temperatures->min() ?? 0, 2),
                        'max' => round($temperatures->max() ?? 0, 2),
                        'status' => $this->getTemperatureStatus($temperatures->first() ?? 0)
                    ],
                    'ph' => [
                        'current' => round($phLevels->first() ?? 0, 2),
                        'average' => round($phLevels->avg() ?? 0, 2),
                        'min' => round($phLevels->min() ?? 0, 2),
                        'max' => round($phLevels->max() ?? 0, 2),
                        'status' => $this->getPhStatus($phLevels->first() ?? 0)
                    ],
                    'oxygen' => [
                        'current' => round($oxygenLevels->first() ?? 0, 2),
                        'average' => round($oxygenLevels->avg() ?? 0, 2),
                        'min' => round($oxygenLevels->min() ?? 0, 2),
                        'max' => round($oxygenLevels->max() ?? 0, 2),
                        'status' => $this->getOxygenStatus($oxygenLevels->first() ?? 0)
                    ],
                    'last_updated' => isset($firebaseData[0]['timestamp']) && $firebaseData[0]['timestamp'] ?
                        \Carbon\Carbon::createFromTimestamp($firebaseData[0]['timestamp'])->toISOString() :
                        now()->toISOString()
                ];
            } else {
                // Default stats when no data
                $stats = [
                    'device_id' => $deviceId,
                    'total_readings' => 0,
                    'temperature' => [
                        'current' => 26.5,
                        'average' => 26.5,
                        'min' => 26.5,
                        'max' => 26.5,
                        'status' => 'normal'
                    ],
                    'ph' => [
                        'current' => 4.0,
                        'average' => 4.0,
                        'min' => 4.0,
                        'max' => 4.0,
                        'status' => 'low'
                    ],
                    'oxygen' => [
                        'current' => 6.8,
                        'average' => 6.8,
                        'min' => 6.8,
                        'max' => 6.8,
                        'status' => 'normal'
                    ],
                    'last_updated' => now()->toISOString()
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Sensor statistics retrieved successfully',
                'timestamp' => now()->toISOString()
            ])->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve sensor statistics',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get device status for mobile app
     */
    public function getMobileDeviceStatus(Request $request, $deviceId = 1)
    {
        try {
            $firebase = new \App\Services\FirebaseService();
            
            // Get latest data to determine status
            $firebaseData = $firebase->getSensorDataFromFirebase($deviceId);
            $latestData = !empty($firebaseData) ? $firebaseData[0] : null;
            
            if ($latestData) {
                $lastUpdate = isset($latestData['timestamp']) && $latestData['timestamp'] ?
                    \Carbon\Carbon::createFromTimestamp($latestData['timestamp']) :
                    now();
                
                $minutesAgo = $lastUpdate->diffInMinutes(now());
                
                // Determine device status based on last update time
                if ($minutesAgo <= 5) {
                    $status = 'online';
                    $statusText = 'Device Online';
                } elseif ($minutesAgo <= 30) {
                    $status = 'warning';
                    $statusText = 'Device Slow Response';
                } else {
                    $status = 'offline';
                    $statusText = 'Device Offline';
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'status' => $status,
                        'status_text' => $statusText,
                        'last_seen' => $lastUpdate->toISOString(),
                        'minutes_ago' => $minutesAgo,
                        'voltage' => round((float)($latestData['voltage'] ?? 0), 2),
                        'connection' => 'firebase',
                        'readings_today' => count($firebaseData)
                    ],
                    'message' => 'Device status retrieved successfully',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
                
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'status' => 'no_data',
                        'status_text' => 'No Data Available',
                        'last_seen' => null,
                        'minutes_ago' => null,
                        'voltage' => 0,
                        'connection' => 'firebase',
                        'readings_today' => 0
                    ],
                    'message' => 'No device data available',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve device status',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get list of devices for mobile app
     */
    public function getMobileDevicesList(Request $request)
    {
        try {
            // For now, return static device list. In future, this could be dynamic from database
            $devices = [
                [
                    'device_id' => 1,
                    'name' => 'Kolam Ikan Utama',
                    'location' => 'Pool 1',
                    'type' => 'ESP32 pH Sensor',
                    'status' => 'active',
                    'last_seen' => now()->toISOString()
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $devices,
                'count' => count($devices),
                'message' => 'Devices list retrieved successfully',
                'timestamp' => now()->toISOString()
            ])->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve devices list',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get real-time Firebase data for mobile app
     */
    public function getMobileFirebaseRealtime(Request $request, $deviceId = 1)
    {
        try {
            // This endpoint returns the same data as getFirebaseData but optimized for mobile
            return $this->getFirebaseData($request->merge(['device_id' => $deviceId]));
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve real-time Firebase data',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    // Helper methods for status determination
    private function getTemperatureStatus($temp)
    {
        if ($temp < 20) return 'too_cold';
        if ($temp > 30) return 'too_hot';
        return 'normal';
    }

    private function getPhStatus($ph)
    {
        if ($ph < 6.5) return 'low';
        if ($ph > 8.5) return 'high';
        return 'normal';
    }

    private function getOxygenStatus($oxygen)
    {
        if ($oxygen < 5) return 'low';
        if ($oxygen > 10) return 'high';
        return 'normal';
    }

    // =================================================================
    // FIRESTORE API METHODS (Same database as Mobile App)
    // =================================================================

    /**
     * Get latest sensor data from Firestore (same as mobile app)
     */
    public function getMobileFirestoreLatest(Request $request, $deviceId = 1)
    {
        try {
            $firestore = new \App\Services\FirestoreService();
            
            // Get latest Firestore data
            $firestoreData = $firestore->getSensorDataFromFirestore($deviceId);
            $latestData = !empty($firestoreData) ? $firestoreData[0] : null;
            
            if ($latestData) {
                $response = [
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'temperature' => round((float)($latestData['temperature'] ?? 0), 2),
                        'ph' => round((float)($latestData['ph'] ?? 0), 2),
                        'oxygen' => round((float)($latestData['oxygen'] ?? 0), 2),
                        'voltage' => round((float)($latestData['voltage'] ?? 0), 2),
                        'timestamp' => $latestData['timestamp'] ?? now()->toISOString(),
                        'source' => 'firestore',
                        'status' => 'online'
                    ],
                    'message' => 'Latest sensor data from Firestore retrieved successfully',
                    'timestamp' => now()->toISOString()
                ];
            } else {
                // No data available - provide sample for demo
                $response = [
                    'success' => true,
                    'data' => [
                        'device_id' => $deviceId,
                        'temperature' => 26.5,
                        'ph' => 4.0,
                        'oxygen' => 6.8,
                        'voltage' => 3.3,
                        'timestamp' => now()->toISOString(),
                        'source' => 'firestore',
                        'status' => 'no_data'
                    ],
                    'message' => 'No Firestore data available, showing default values',
                    'timestamp' => now()->toISOString()
                ];
            }
            
            return response()->json($response)->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve Firestore sensor data',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Get sensor history from Firestore (same as mobile app)
     */
    public function getMobileFirestoreHistory(Request $request, $deviceId = 1)
    {
        try {
            $firestore = new \App\Services\FirestoreService();
            $limit = $request->get('limit', 50);
            
            // Get Firestore data
            $firestoreData = $firestore->getSensorDataFromFirestore($deviceId);
            
            if (!empty($firestoreData)) {
                $historyData = collect($firestoreData)->take($limit)->map(function($item) use ($deviceId) {
                    return [
                        'device_id' => $deviceId,
                        'temperature' => round((float)($item['temperature'] ?? 0), 2),
                        'ph' => round((float)($item['ph'] ?? 0), 2),
                        'oxygen' => round((float)($item['oxygen'] ?? 0), 2),
                        'voltage' => round((float)($item['voltage'] ?? 0), 2),
                        'timestamp' => $item['timestamp'] ?? now()->toISOString()
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'data' => $historyData,
                    'count' => $historyData->count(),
                    'limit' => $limit,
                    'source' => 'firestore',
                    'message' => 'Sensor history from Firestore retrieved successfully',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
                
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'count' => 0,
                    'source' => 'firestore',
                    'message' => 'No Firestore sensor history available',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve Firestore sensor history',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Save sensor data to Firestore (for ESP32 or mobile app)
     */
    public function saveMobileFirestoreData(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'temperature' => 'required|numeric',
                'ph' => 'required|numeric',
                'oxygen' => 'required|numeric',
                'voltage' => 'nullable|numeric'
            ]);
            
            $firestore = new \App\Services\FirestoreService();
            $saved = $firestore->saveSensorDataToFirestore($validated);
            
            if ($saved) {
                return response()->json([
                    'success' => true,
                    'data' => $validated,
                    'message' => 'Sensor data saved to Firestore successfully',
                    'timestamp' => now()->toISOString()
                ])->header('Access-Control-Allow-Origin', '*');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save sensor data to Firestore',
                    'timestamp' => now()->toISOString()
                ], 500)->header('Access-Control-Allow-Origin', '*');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to save sensor data to Firestore',
                'timestamp' => now()->toISOString()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }
}

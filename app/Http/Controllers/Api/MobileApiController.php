<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SensorData;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MobileApiController extends Controller
{
    /**
     * Register new user (Mobile)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Create default settings for user
            UserSettings::create([
                'user_id' => $user->id,
                'temp_min' => 24.00,
                'temp_max' => 30.00,
                'ph_min' => 6.50,
                'ph_max' => 8.50,
                'oxygen_min' => 5.00,
                'oxygen_max' => 8.00,
            ]);

            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_active' => $user->is_active,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user (Mobile)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.'
            ], 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                ],
                'token' => $token,
            ]
        ], 200);
    }

    /**
     * Logout user (Mobile)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user profile (Mobile)
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ],
                'settings' => $settings ? [
                    'temp_min' => (float) $settings->temp_min,
                    'temp_max' => (float) $settings->temp_max,
                    'ph_min' => (float) $settings->ph_min,
                    'ph_max' => (float) $settings->ph_max,
                    'oxygen_min' => (float) $settings->oxygen_min,
                    'oxygen_max' => (float) $settings->oxygen_max,
                ] : null,
            ]
        ], 200);
    }

    /**
     * Update user profile (Mobile)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password lama salah'
                    ], 401);
                }
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update profile gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard data (Mobile)
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();
            $settings = $user->settings;

            // Get latest sensor data
            $latestData = SensorData::latest('recorded_at')->first();

            // Get sensor data for Working Hours (08:00-17:00) from latest date with data
            if ($latestData) {
                $latestDate = Carbon::parse($latestData->recorded_at)->startOfDay();
                $startTime = $latestDate->copy()->setHour(8)->setMinute(0)->setSecond(0);
                $endTime = $latestDate->copy()->setHour(17)->setMinute(0)->setSecond(0);
            } else {
                $startTime = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
                $endTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
            }

            $sensorData = SensorData::whereBetween('recorded_at', [$startTime, $endTime])
                ->orderBy('recorded_at', 'asc')
                ->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->recorded_at)->format('Y-m-d H:00:00');
                })
                ->map(function($group) {
                    return [
                        'temperature' => round((float)$group->avg('temperature'), 2),
                        'ph' => round((float)$group->avg('ph'), 2),
                        'oxygen' => round((float)$group->avg('oxygen'), 2),
                        'time' => Carbon::parse($group->first()->recorded_at)->format('H:00'),
                        'recorded_at' => $group->first()->recorded_at
                    ];
                })
                ->values();

            // Calculate statistics
            $stats = [
                'avg_temperature' => $sensorData->isNotEmpty() ? round($sensorData->avg('temperature'), 2) : 0,
                'avg_ph' => $sensorData->isNotEmpty() ? round($sensorData->avg('ph'), 2) : 0,
                'avg_oxygen' => $sensorData->isNotEmpty() ? round($sensorData->avg('oxygen'), 2) : 0,
                'max_temperature' => $sensorData->isNotEmpty() ? round($sensorData->max('temperature'), 2) : 0,
                'min_temperature' => $sensorData->isNotEmpty() ? round($sensorData->min('temperature'), 2) : 0,
                'max_ph' => $sensorData->isNotEmpty() ? round($sensorData->max('ph'), 2) : 0,
                'min_ph' => $sensorData->isNotEmpty() ? round($sensorData->min('ph'), 2) : 0,
                'max_oxygen' => $sensorData->isNotEmpty() ? round($sensorData->max('oxygen'), 2) : 0,
                'min_oxygen' => $sensorData->isNotEmpty() ? round($sensorData->min('oxygen'), 2) : 0,
            ];

            // Check status based on user settings
            $status = [
                'temperature' => 'normal',
                'ph' => 'normal',
                'oxygen' => 'normal',
            ];

            $alerts = [];

            if ($latestData && $settings) {
                if ($latestData->temperature < $settings->temp_min || $latestData->temperature > $settings->temp_max) {
                    $status['temperature'] = 'warning';
                    $alerts[] = [
                        'type' => 'temperature',
                        'message' => 'Temperature di luar batas normal: ' . $latestData->temperature . '°C',
                        'level' => 'warning'
                    ];
                }

                if ($latestData->ph < $settings->ph_min || $latestData->ph > $settings->ph_max) {
                    $status['ph'] = 'warning';
                    $alerts[] = [
                        'type' => 'ph',
                        'message' => 'pH di luar batas normal: ' . $latestData->ph,
                        'level' => 'warning'
                    ];
                }

                if ($latestData->oxygen < $settings->oxygen_min || $latestData->oxygen > $settings->oxygen_max) {
                    $status['oxygen'] = 'warning';
                    $alerts[] = [
                        'type' => 'oxygen',
                        'message' => 'Oksigen di luar batas normal: ' . $latestData->oxygen . ' mg/L',
                        'level' => 'warning'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'latest' => $latestData ? [
                        'temperature' => (float) $latestData->temperature,
                        'ph' => (float) $latestData->ph,
                        'oxygen' => (float) $latestData->oxygen,
                        'recorded_at' => $latestData->recorded_at,
                    ] : null,
                    'chart_data' => $sensorData,
                    'statistics' => $stats,
                    'status' => $status,
                    'alerts' => $alerts,
                    'time_range' => [
                        'start' => $startTime->format('Y-m-d H:i:s'),
                        'end' => $endTime->format('Y-m-d H:i:s'),
                        'label' => 'Jam Kerja (08:00 - 17:00)'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get history data with pagination (Mobile)
     */
    public function history(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            // Filters
            $query = SensorData::query();

            if ($request->has('date_from')) {
                $query->where('recorded_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('recorded_at', '<=', $request->date_to);
            }

            if ($request->has('temp_min')) {
                $query->where('temperature', '>=', $request->temp_min);
            }

            if ($request->has('temp_max')) {
                $query->where('temperature', '<=', $request->temp_max);
            }

            if ($request->has('ph_min')) {
                $query->where('ph', '>=', $request->ph_min);
            }

            if ($request->has('ph_max')) {
                $query->where('ph', '<=', $request->ph_max);
            }

            if ($request->has('oxygen_min')) {
                $query->where('oxygen', '>=', $request->oxygen_min);
            }

            if ($request->has('oxygen_max')) {
                $query->where('oxygen', '<=', $request->oxygen_max);
            }

            $data = $query->orderBy('recorded_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user settings (Mobile)
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();
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

        return response()->json([
            'success' => true,
            'data' => [
                'temp_min' => (float) $settings->temp_min,
                'temp_max' => (float) $settings->temp_max,
                'ph_min' => (float) $settings->ph_min,
                'ph_max' => (float) $settings->ph_max,
                'oxygen_min' => (float) $settings->oxygen_min,
                'oxygen_max' => (float) $settings->oxygen_max,
            ]
        ], 200);
    }

    /**
     * Update user settings (Mobile)
     */
    public function updateSettings(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'temp_min' => 'sometimes|numeric|min:0|max:50',
            'temp_max' => 'sometimes|numeric|min:0|max:50|gt:temp_min',
            'ph_min' => 'sometimes|numeric|min:0|max:14',
            'ph_max' => 'sometimes|numeric|min:0|max:14|gt:ph_min',
            'oxygen_min' => 'sometimes|numeric|min:0|max:20',
            'oxygen_max' => 'sometimes|numeric|min:0|max:20|gt:oxygen_min',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $settings = $user->settings()->firstOrCreate(['user_id' => $user->id]);

            if ($request->has('temp_min')) $settings->temp_min = $request->temp_min;
            if ($request->has('temp_max')) $settings->temp_max = $request->temp_max;
            if ($request->has('ph_min')) $settings->ph_min = $request->ph_min;
            if ($request->has('ph_max')) $settings->ph_max = $request->ph_max;
            if ($request->has('oxygen_min')) $settings->oxygen_min = $request->oxygen_min;
            if ($request->has('oxygen_max')) $settings->oxygen_max = $request->oxygen_max;

            $settings->save();

            return response()->json([
                'success' => true,
                'message' => 'Settings berhasil diupdate',
                'data' => [
                    'temp_min' => (float) $settings->temp_min,
                    'temp_max' => (float) $settings->temp_max,
                    'ph_min' => (float) $settings->ph_min,
                    'ph_max' => (float) $settings->ph_max,
                    'oxygen_min' => (float) $settings->oxygen_min,
                    'oxygen_max' => (float) $settings->oxygen_max,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update settings gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor reading (Mobile)
     */
    public function latestReading(Request $request)
    {
        $latestData = SensorData::latest('recorded_at')->first();

        if (!$latestData) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data sensor'
            ], 404);
        }

        $user = $request->user();
        $settings = $user->settings;

        $status = [
            'temperature' => 'normal',
            'ph' => 'normal',
            'oxygen' => 'normal',
        ];

        if ($settings) {
            if ($latestData->temperature < $settings->temp_min || $latestData->temperature > $settings->temp_max) {
                $status['temperature'] = 'warning';
            }
            if ($latestData->ph < $settings->ph_min || $latestData->ph > $settings->ph_max) {
                $status['ph'] = 'warning';
            }
            if ($latestData->oxygen < $settings->oxygen_min || $latestData->oxygen > $settings->oxygen_max) {
                $status['oxygen'] = 'warning';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'temperature' => (float) $latestData->temperature,
                'ph' => (float) $latestData->ph,
                'oxygen' => (float) $latestData->oxygen,
                'recorded_at' => $latestData->recorded_at,
                'status' => $status,
            ]
        ], 200);
    }
}

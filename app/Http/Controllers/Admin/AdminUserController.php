<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $totalSensorData = SensorData::count();
        
        // Statistics
        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'regular' => User::where('role', 'user')->count(),
            'active' => User::where('is_active', 1)->count(),
        ];

        return view('admin.users', compact('users', 'totalSensorData', 'stats'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => 'required|in:admin,user',
            'is_active' => 'required|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->is_active,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:8|confirmed',
            ]);
            
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return redirect()->route('admin.users')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri!'
            ], 403);
        }

        // Prevent deleting last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus admin terakhir!'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus!'
        ]);
    }

    /**
     * Verify user email
     */
    public function verifyEmail(User $user)
    {
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terverifikasi!'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi!'
        ]);
    }

    /**
     * Export users to CSV
     */
    public function export()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Status', 'Email Verified', 'Last Login', 'Created At']);
            
            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    ucfirst($user->role),
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get user details for modal/ajax
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * View user dashboard (admin can view any user's dashboard)
     */
    public function viewUserDashboard(User $user)
    {
        // Get sensor data for charts (Working Hours: 08:00-17:00 from latest date with data)
        $latestData = SensorData::orderBy('recorded_at', 'desc')->first();
        
        if ($latestData) {
            $latestDate = \Carbon\Carbon::parse($latestData->recorded_at)->startOfDay();
            $startTime = $latestDate->copy()->setHour(8)->setMinute(0)->setSecond(0);
            $endTime = $latestDate->copy()->setHour(17)->setMinute(0)->setSecond(0);
        } else {
            // Fallback to today if no data exists
            $startTime = \Carbon\Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
            $endTime = \Carbon\Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
        }
        
        // Get sensor data grouped by hour (08:00-17:00)
        $sensorData = SensorData::whereBetween('recorded_at', [$startTime, $endTime])
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->recorded_at)->format('Y-m-d H:00:00');
            })
            ->map(function($group) {
                return [
                    'temperature' => round((float)$group->avg('temperature'), 2),
                    'ph' => round((float)$group->avg('ph'), 2),
                    'oxygen' => round((float)$group->avg('oxygen'), 2),
                    'time' => \Carbon\Carbon::parse($group->first()->recorded_at)->format('H:00'),
                    'recorded_at' => $group->first()->recorded_at
                ];
            })
            ->values();

        // Get user settings for thresholds
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

        // Calculate statistics from filtered data (08:00-17:00)
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

        // Check alerts based on latest data
        $alerts = [];
        if ($latestData) {
            if ($latestData->temperature < $settings->temp_min || $latestData->temperature > $settings->temp_max) {
                $alerts[] = [
                    'type' => 'temperature',
                    'message' => 'Temperature di luar batas normal: ' . $latestData->temperature . '°C',
                    'level' => 'warning'
                ];
            }
            if ($latestData->ph < $settings->ph_min || $latestData->ph > $settings->ph_max) {
                $alerts[] = [
                    'type' => 'ph',
                    'message' => 'pH di luar batas normal: ' . $latestData->ph,
                    'level' => 'warning'
                ];
            }
            if ($latestData->oxygen < $settings->oxygen_min || $latestData->oxygen > $settings->oxygen_max) {
                $alerts[] = [
                    'type' => 'oxygen',
                    'message' => 'Oksigen di luar batas normal: ' . $latestData->oxygen . ' mg/L',
                    'level' => 'warning'
                ];
            }
        }

        return view('admin.user-dashboard', compact('user', 'sensorData', 'latestData', 'settings', 'stats', 'alerts'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Show user history page with Firebase data
     */
    public function history(Request $request)
    {
        try {
            // Get Firebase data
            $firebaseService = app(\App\Services\FirebaseService::class);
            $firebaseData = $firebaseService->getAllSensorData();
            
            // Get filter parameters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $parameterType = $request->input('type', 'all');
            
            $history = collect();
            
            if ($firebaseData && is_array($firebaseData)) {
                // Convert Firebase data to collection with proper structure
                $allData = collect($firebaseData)->map(function ($item, $key) {
                    return (object) [
                        'id' => $key,
                        'device_id' => 1,
                        'temperature' => isset($item['temperature']) ? floatval($item['temperature']) : null,
                        'ph' => isset($item['pH']) ? floatval($item['pH']) : (isset($item['ph']) ? floatval($item['ph']) : null),
                        'oxygen' => isset($item['oxygen']) ? floatval($item['oxygen']) : null,
                        'voltage' => isset($item['voltage']) ? floatval($item['voltage']) : null,
                        'timestamp' => isset($item['timestamp']) ? $item['timestamp'] : now()->toDateTimeString(),
                        'recorded_at' => isset($item['timestamp']) ? 
                            \Carbon\Carbon::parse($item['timestamp']) : 
                            now(),
                        'created_at' => isset($item['timestamp']) ? 
                            \Carbon\Carbon::parse($item['timestamp']) : 
                            now(),
                    ];
                })->sortByDesc('recorded_at');
                
                // Apply date range filter
                if ($startDate && $endDate) {
                    $start = \Carbon\Carbon::parse($startDate)->startOfDay();
                    $end = \Carbon\Carbon::parse($endDate)->endOfDay();
                    $allData = $allData->filter(function ($item) use ($start, $end) {
                        $itemDate = \Carbon\Carbon::parse($item->recorded_at);
                        return $itemDate->between($start, $end);
                    });
                }
                
                // Apply parameter type filter
                if ($parameterType && $parameterType !== 'all') {
                    $allData = $allData->filter(function ($item) use ($parameterType) {
                        switch ($parameterType) {
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
                
                // Implement pagination manually for Firebase data
                $perPage = 20;
                $currentPage = request()->input('page', 1);
                $items = $allData->forPage($currentPage, $perPage);
                
                $history = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $allData->count(),
                    $perPage,
                    $currentPage,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            } else {
                // If no Firebase data, create empty paginator
                $history = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(),
                    0,
                    20,
                    1,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            }
            
            // Get user settings for threshold comparison
            $settings = \Auth::user()->settings;

            return view('user.history', compact('history', 'settings'));
            
        } catch (\Exception $e) {
            \Log::error('Error in user history: ' . $e->getMessage());
            
            // Fallback to empty history
            $history = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                20,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            $settings = \Auth::user()->settings;
            
            session()->flash('error', 'Gagal memuat data history dari Firebase: ' . $e->getMessage());
            
            return view('user.history', compact('history', 'settings'));
        }
    }

    /**
     * Show user profile page
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return redirect()->route('user.profile')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $user = Auth::user();
        
        // Get or create user settings
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

        return view('user.settings', compact('settings'));
    }

    /**
     * Update user settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'temp_min' => 'required|numeric|min:0|max:50',
            'temp_max' => 'required|numeric|min:0|max:50|gte:temp_min',
            'ph_min' => 'required|numeric|min:0|max:14',
            'ph_max' => 'required|numeric|min:0|max:14|gte:ph_min',
            'oxygen_min' => 'required|numeric|min:0|max:20',
            'oxygen_max' => 'required|numeric|min:0|max:20|gte:oxygen_min',
        ]);

        $user = Auth::user();
        $settings = $user->settings()->firstOrNew(['user_id' => $user->id]);

        $settings->fill([
            'temp_min' => $request->temp_min,
            'temp_max' => $request->temp_max,
            'ph_min' => $request->ph_min,
            'ph_max' => $request->ph_max,
            'oxygen_min' => $request->oxygen_min,
            'oxygen_max' => $request->oxygen_max,
        ]);

        $settings->save();

        return redirect()->route('user.settings')->with('success', 'Pengaturan berhasil disimpan!');
    }

    /**
     * Export Firebase history to CSV
     */
    public function exportHistory(Request $request)
    {
        try {
            // Get Firebase data
            $firebaseService = app(\App\Services\FirebaseService::class);
            $firebaseData = $firebaseService->getAllSensorData();
            
            $data = collect();
            
            if ($firebaseData && is_array($firebaseData)) {
                // Convert Firebase data to collection
                $data = collect($firebaseData)->map(function ($item, $key) {
                    return (object) [
                        'id' => $key,
                        'temperature' => isset($item['temperature']) ? floatval($item['temperature']) : null,
                        'ph' => isset($item['pH']) ? floatval($item['pH']) : (isset($item['ph']) ? floatval($item['ph']) : null),
                        'oxygen' => isset($item['oxygen']) ? floatval($item['oxygen']) : null,
                        'voltage' => isset($item['voltage']) ? floatval($item['voltage']) : null,
                        'recorded_at' => isset($item['timestamp']) ? 
                            \Carbon\Carbon::parse($item['timestamp']) : 
                            now(),
                    ];
                })->sortByDesc('recorded_at');
                
                // Apply date filter if provided
                if ($request->has('start_date') && $request->has('end_date')) {
                    $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                    $end = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                    $data = $data->filter(function ($item) use ($start, $end) {
                        $itemDate = \Carbon\Carbon::parse($item->recorded_at);
                        return $itemDate->between($start, $end);
                    });
                }
            }

        $filename = 'sensor_data_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Waktu', 'Suhu (°C)', 'pH', 'Oksigen (mg/L)', 'Voltage (V)']);
            
            // Data
            foreach ($data as $row) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($row->recorded_at)->format('Y-m-d H:i:s'),
                    $row->temperature ?? 'N/A',
                    $row->ph ?? 'N/A',
                    $row->oxygen ?? 'N/A',
                    $row->voltage ?? 'N/A',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
        
        } catch (\Exception $e) {
            \Log::error('Error in export history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengexport data: ' . $e->getMessage());
        }
    }
}

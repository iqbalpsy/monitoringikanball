<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Device;
use App\Models\SensorData;

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

        return view('admin.dashboard', compact('user', 'devices', 'users', 'recentSensorData', 'totalSensorData'));
    }

    /**
     * Show user dashboard
     */
    public function userDashboard()
    {
        $user = Auth::user();
        $devices = Device::with('latestSensorData')->get();
        
        return view('dashboard.user', compact('devices'));
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
     * Show history page
     */
    public function history()
    {
        $devices = Device::all();
        $users = User::all();
        $sensorData = SensorData::with('device')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        $totalSensorData = SensorData::count();

        return view('admin.history', compact('devices', 'sensorData', 'users', 'totalSensorData'));
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
}

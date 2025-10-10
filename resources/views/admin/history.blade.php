@extends('layouts.app')

@section('title', 'History & Logs')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">History & Logs</h1>
            <p class="text-gray-600 mt-1">Riwayat aktivitas sistem dan data sensor</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-filter mr-2"></i>Filter Data
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>Export History
            </button>
        </div>
    </div>
</div>

<!-- Filter Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::whereDate('recorded_at', today())->count() }}</p>
                <p class="text-gray-600">Today's Records</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-calendar-week text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::where('recorded_at', '>=', now()->subWeek())->count() }}</p>
                <p class="text-gray-600">This Week</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::where('recorded_at', '>=', now()->subMonth())->count() }}</p>
                <p class="text-gray-600">This Month</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::whereHas('device', function($q) { $q->where('status', 'offline'); })->count() }}</p>
                <p class="text-gray-600">Error Records</p>
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="card-gradient rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recent Sensor Data</h3>
            <div class="flex space-x-2">
                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option>All Devices</option>
                    @foreach($devices as $device)
                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                    @endforeach
                </select>
                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option>Last 24 Hours</option>
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Timestamp</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Device</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">pH Level</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Temperature</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Oxygen</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\SensorData::with('device')->latest('recorded_at')->take(20)->get() as $sensorData)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-6 text-sm">
                        <div>
                            <p class="font-medium text-gray-900">{{ $sensorData->recorded_at->format('d/m/Y H:i:s') }}</p>
                            <p class="text-xs text-gray-500">{{ $sensorData->recorded_at->diffForHumans() }}</p>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-microchip text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $sensorData->device->name }}</p>
                                <p class="text-xs text-gray-500">{{ $sensorData->device->device_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isPhNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $sensorData->ph_level }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isTemperatureNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $sensorData->temperature }}°C
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isOxygenAdequate() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $sensorData->oxygen_level }} mg/L
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($sensorData->isPhNormal() && $sensorData->isTemperatureNormal() && $sensorData->isOxygenAdequate())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Normal
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Warning
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">Showing {{ $sensorData->count() }} of {{ number_format($sensorData->total()) }} records</p>
            <div class="flex space-x-2">
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100">Previous</button>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

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
                @php
                    $todayCount = (isset($allData) && is_object($allData)) ? $allData->filter(function($item) {
                        return \Carbon\Carbon::parse($item->created_at)->isToday();
                    })->count() : 0;
                @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $todayCount }}</p>
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
                @php
                    $weekCount = (isset($allData) && is_object($allData)) ? $allData->filter(function($item) {
                        return \Carbon\Carbon::parse($item->created_at)->isCurrentWeek();
                    })->count() : 0;
                @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $weekCount }}</p>
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
                @php
                    $monthCount = (isset($allData) && is_object($allData)) ? $allData->filter(function($item) {
                        return \Carbon\Carbon::parse($item->created_at)->isCurrentMonth();
                    })->count() : 0;
                @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $monthCount }}</p>
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
                @php
                    $abnormalCount = (isset($allData) && is_object($allData)) ? $allData->filter(function($item) {
                        return ($item->temperature && ($item->temperature < 24 || $item->temperature > 30)) ||
                               ($item->ph && ($item->ph < 6.5 || $item->ph > 8.5)) ||
                               ($item->oxygen && $item->oxygen < 5);
                    })->count() : 0;
                @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $abnormalCount }}</p>
                <p class="text-gray-600">Abnormal Records</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card-gradient rounded-lg shadow-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter History Data</h3>
    <form method="GET" action="{{ route('admin.history') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Filter</label>
            <select name="date_filter" id="date_filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="all" {{ request('date_filter') == 'all' ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
        </div>
        
        <div id="custom_dates" style="{{ request('date_filter') == 'custom' ? '' : 'display: none;' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        
        <div id="custom_dates_end" style="{{ request('date_filter') == 'custom' ? '' : 'display: none;' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parameter</label>
            <select name="parameter_filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="all" {{ request('parameter_filter') == 'all' ? 'selected' : '' }}>All Parameters</option>
                <option value="temperature" {{ request('parameter_filter') == 'temperature' ? 'selected' : '' }}>Temperature</option>
                <option value="ph" {{ request('parameter_filter') == 'ph' ? 'selected' : '' }}>pH Level</option>
                <option value="oxygen" {{ request('parameter_filter') == 'oxygen' ? 'selected' : '' }}>Oxygen</option>
                <option value="voltage" {{ request('parameter_filter') == 'voltage' ? 'selected' : '' }}>Voltage</option>
            </select>
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
            <a href="{{ route('admin.history') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-refresh mr-1"></i>Reset
            </a>
        </div>
    </form>
</div>

<!-- History Table -->
<div class="card-gradient rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Sensor Data History - Firebase</h3>
            <div class="flex space-x-2">
                <span class="text-sm text-gray-600">Total: {{ number_format($totalSensorData) }} records</span>
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
                @forelse($sensorData as $data)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-6 text-sm">
                        <div>
                            <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i:s') }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}</p>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-microchip text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $data->device->name ?? 'ESP32 Device #1' }}</p>
                                <p class="text-xs text-gray-500">Device ID: {{ $data->device_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($data->ph)
                            @php
                                $isPhNormal = $data->ph >= 6.5 && $data->ph <= 8.5;
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isPhNormal ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ number_format($data->ph, 1) }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($data->temperature)
                            @php
                                $isTempNormal = $data->temperature >= 25 && $data->temperature <= 30;
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isTempNormal ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ number_format($data->temperature, 1) }}°C
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($data->oxygen)
                            @php
                                $isOxygenNormal = $data->oxygen >= 5.0;
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isOxygenNormal ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ number_format($data->oxygen, 1) }} mg/L
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $phOk = !$data->ph || ($data->ph >= 6.5 && $data->ph <= 8.5);
                            $tempOk = !$data->temperature || ($data->temperature >= 25 && $data->temperature <= 30);
                            $oxygenOk = !$data->oxygen || ($data->oxygen >= 5.0);
                            $allNormal = $phOk && $tempOk && $oxygenOk;
                        @endphp
                        @if($allNormal)
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
                @empty
                <tr>
                    <td colspan="6" class="py-8 px-6 text-center">
                        <div class="text-gray-500">
                            <i class="fas fa-database text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">Tidak ada data history tersedia</p>
                            <p class="text-sm">Data sensor dari Firebase akan ditampilkan di sini</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                @if($sensorData instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    Showing {{ $sensorData->firstItem() ?? 0 }} to {{ $sensorData->lastItem() ?? 0 }} of {{ number_format($sensorData->total()) }} records
                @else
                    Showing {{ $sensorData->count() }} records
                @endif
            </p>
            <div>
                @if($sensorData instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $sensorData->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateFilter = document.getElementById('date_filter');
    const customDates = document.getElementById('custom_dates');
    const customDatesEnd = document.getElementById('custom_dates_end');
    
    function toggleCustomDates() {
        if (dateFilter.value === 'custom') {
            customDates.style.display = '';
            customDatesEnd.style.display = '';
        } else {
            customDates.style.display = 'none';
            customDatesEnd.style.display = 'none';
        }
    }
    
    dateFilter.addEventListener('change', toggleCustomDates);
    toggleCustomDates(); // Initialize on page load
});
</script>
@endsection

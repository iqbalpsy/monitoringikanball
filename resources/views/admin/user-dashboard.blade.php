@extends('layouts.app')

@section('title', 'User Dashboard - ' . $user->name)

@section('content')
<!-- Page Header with Back Button -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard User: {{ $user->name }}</h1>
                <p class="text-gray-600 mt-1">{{ $user->email }} - <span class="capitalize">{{ $user->role }}</span></p>
            </div>
        </div>
        <div>
            <span class="px-4 py-2 rounded-lg {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                <i class="fas fa-circle mr-1"></i>{{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
</div>

<!-- Alerts Section -->
@if(count($alerts) > 0)
<div class="mb-6">
    @foreach($alerts as $alert)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-3 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
            <div>
                <p class="font-semibold">{{ $alert['message'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Sensor Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Temperature Card -->
    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-orange-500">
        <div class="flex justify-between items-start mb-4">
            <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-4 rounded-xl">
                <i class="fas fa-thermometer-half text-white text-2xl"></i>
            </div>
            @if($latestData && $settings)
                @if($latestData->temperature >= $settings->temp_min && $latestData->temperature <= $settings->temp_max)
                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-check mr-1"></i> Normal
                    </span>
                @else
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-exclamation mr-1"></i> Warning
                    </span>
                @endif
            @endif
        </div>
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Suhu Air</h3>
        <div class="flex items-end justify-between">
            <div>
                <p class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->temperature, 1) : '--' }}</p>
                <p class="text-gray-500 text-sm mt-1">°C</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Range: {{ $settings->temp_min }}°C - {{ $settings->temp_max }}°C</p>
                <p class="mt-1">Avg: {{ number_format($stats['avg_temperature'], 1) }}°C</p>
            </div>
        </div>
    </div>

    <!-- pH Card -->
    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <div class="flex justify-between items-start mb-4">
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-4 rounded-xl">
                <i class="fas fa-flask text-white text-2xl"></i>
            </div>
            @if($latestData && $settings)
                @if($latestData->ph >= $settings->ph_min && $latestData->ph <= $settings->ph_max)
                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-check mr-1"></i> Normal
                    </span>
                @else
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-exclamation mr-1"></i> Warning
                    </span>
                @endif
            @endif
        </div>
        <h3 class="text-gray-600 text-sm font-semibold mb-2">pH Air</h3>
        <div class="flex items-end justify-between">
            <div>
                <p class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->ph, 2) : '--' }}</p>
                <p class="text-gray-500 text-sm mt-1">pH</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Range: {{ $settings->ph_min }} - {{ $settings->ph_max }}</p>
                <p class="mt-1">Avg: {{ number_format($stats['avg_ph'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Oxygen Card -->
    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500">
        <div class="flex justify-between items-start mb-4">
            <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-xl">
                <i class="fas fa-wind text-white text-2xl"></i>
            </div>
            @if($latestData && $settings)
                @if($latestData->oxygen >= $settings->oxygen_min && $latestData->oxygen <= $settings->oxygen_max)
                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-check mr-1"></i> Normal
                    </span>
                @else
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <i class="fas fa-exclamation mr-1"></i> Warning
                    </span>
                @endif
            @endif
        </div>
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Oksigen Terlarut</h3>
        <div class="flex items-end justify-between">
            <div>
                <p class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->oxygen, 2) : '--' }}</p>
                <p class="text-gray-500 text-sm mt-1">mg/L</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Range: {{ $settings->oxygen_min }} - {{ $settings->oxygen_max }} mg/L</p>
                <p class="mt-1">Avg: {{ number_format($stats['avg_oxygen'], 2) }} mg/L</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Temperature Chart -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-thermometer-half text-orange-500 mr-2"></i>
                Grafik Suhu Air (Jam Kerja 08:00 - 17:00)
            </h3>
        </div>
        <canvas id="temperatureChart"></canvas>
    </div>

    <!-- pH Chart -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-flask text-blue-500 mr-2"></i>
                Grafik pH Air (Jam Kerja 08:00 - 17:00)
            </h3>
        </div>
        <canvas id="phChart"></canvas>
    </div>
</div>

<!-- Oxygen Chart Full Width -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800">
            <i class="fas fa-wind text-green-500 mr-2"></i>
            Grafik Oksigen Terlarut (Jam Kerja 08:00 - 17:00)
        </h3>
    </div>
    <canvas id="oxygenChart"></canvas>
</div>

<!-- Statistics Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Temperature Stats -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-orange-500 mr-2"></i>Statistik Suhu
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Minimum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['min_temperature'], 1) }}°C</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Maximum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['max_temperature'], 1) }}°C</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rata-rata:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['avg_temperature'], 1) }}°C</span>
            </div>
        </div>
    </div>

    <!-- pH Stats -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-blue-500 mr-2"></i>Statistik pH
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Minimum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['min_ph'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Maximum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['max_ph'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rata-rata:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['avg_ph'], 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Oxygen Stats -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-green-500 mr-2"></i>Statistik Oksigen
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Minimum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['min_oxygen'], 2) }} mg/L</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Maximum:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['max_oxygen'], 2) }} mg/L</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rata-rata:</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['avg_oxygen'], 2) }} mg/L</span>
            </div>
        </div>
    </div>
</div>

<!-- User Settings Info -->
<div class="bg-white rounded-xl shadow-md p-6 mt-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">
        <i class="fas fa-cog text-purple-500 mr-2"></i>User Settings & Thresholds
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h4 class="font-semibold text-gray-700 mb-2">Temperature Range</h4>
            <p class="text-sm text-gray-600">Min: {{ $settings->temp_min }}°C</p>
            <p class="text-sm text-gray-600">Max: {{ $settings->temp_max }}°C</p>
        </div>
        <div>
            <h4 class="font-semibold text-gray-700 mb-2">pH Range</h4>
            <p class="text-sm text-gray-600">Min: {{ $settings->ph_min }}</p>
            <p class="text-sm text-gray-600">Max: {{ $settings->ph_max }}</p>
        </div>
        <div>
            <h4 class="font-semibold text-gray-700 mb-2">Oxygen Range</h4>
            <p class="text-sm text-gray-600">Min: {{ $settings->oxygen_min }} mg/L</p>
            <p class="text-sm text-gray-600">Max: {{ $settings->oxygen_max }} mg/L</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data for charts (Working Hours: 08:00-17:00)
const sensorData = @json($sensorData);
const labels = sensorData.map(item => item.time); // Use pre-formatted time (e.g., "08:00", "09:00")
const temperatures = sensorData.map(item => parseFloat(item.temperature));
const phLevels = sensorData.map(item => parseFloat(item.ph));
const oxygenLevels = sensorData.map(item => parseFloat(item.oxygen));

// Temperature Chart
const tempCtx = document.getElementById('temperatureChart').getContext('2d');
new Chart(tempCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Temperature (°C)',
            data: temperatures,
            borderColor: 'rgb(251, 146, 60)',
            backgroundColor: 'rgba(251, 146, 60, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: false,
                title: { display: true, text: 'Temperature (°C)' }
            },
            x: {
                title: { display: true, text: 'Time (Hour)' }
            }
        }
    }
});

// pH Chart
const phCtx = document.getElementById('phChart').getContext('2d');
new Chart(phCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'pH Level',
            data: phLevels,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: false,
                title: { display: true, text: 'pH Level' }
            },
            x: {
                title: { display: true, text: 'Time (Hour)' }
            }
        }
    }
});

// Oxygen Chart
const oxygenCtx = document.getElementById('oxygenChart').getContext('2d');
new Chart(oxygenCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Oxygen (mg/L)',
            data: oxygenLevels,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: false,
                title: { display: true, text: 'Oxygen (mg/L)' }
            },
            x: {
                title: { display: true, text: 'Time (Hour)' }
            }
        }
    }
});
</script>
@endsection

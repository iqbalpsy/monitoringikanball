@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
            <p class="text-gray-600 mt-1">Monitor semua device dan sistem IoT fish monitoring</p>
        </div>  
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $users->count() }}</p>
                <p class="text-gray-600">Total Users</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-microchip text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $devices->count() }}</p>
                <p class="text-gray-600">Total Devices</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-wifi text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $devices->where('status', 'online')->count() }}</p>
                <p class="text-gray-600">Online Devices</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                <i class="fas fa-database text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($recentSensorData->count()) }}</p>
                <p class="text-gray-600">Sensor Records</p>
            </div>
        </div>
    </div>
</div>

<!-- Kondisi Terkini - Gauge Charts -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Kondisi Terkini</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Suhu Air -->
        <div class="card-gradient rounded-lg shadow-lg p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-thermometer-half text-orange-500 text-xl mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Suhu Air</h3>
            </div>
            <div class="relative w-48 h-48 mx-auto mb-4">
                <canvas id="temperatureGauge" width="192" height="192"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div id="tempValue" class="text-3xl font-bold text-gray-900">{{ $latestData ? number_format($latestData->temperature, 1) : '0.0' }}</div>
                        <div class="text-sm text-gray-600">°C</div>
                    </div>
                </div>
            </div>
            <div id="tempStatus" class="px-4 py-2 rounded-full text-sm font-medium {{ $latestData && $latestData->isTemperatureNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $latestData && $latestData->isTemperatureNormal() ? 'Normal' : 'Warning' }}
            </div>
        </div>

        <!-- Oksigen -->
        <div class="card-gradient rounded-lg shadow-lg p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-wind text-blue-500 text-xl mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Oksigen</h3>
            </div>
            <div class="relative w-48 h-48 mx-auto mb-4">
                <canvas id="oxygenGauge" width="192" height="192"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div id="oxygenValue" class="text-3xl font-bold text-gray-900">{{ $latestData ? number_format($latestData->oxygen, 1) : '0.0' }}</div>
                        <div class="text-sm text-gray-600">mg/L</div>
                    </div>
                </div>
            </div>
            <div id="oxygenStatus" class="px-4 py-2 rounded-full text-sm font-medium {{ $latestData && $latestData->isOxygenAdequate() ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                {{ $latestData && $latestData->isOxygenAdequate() ? 'Baik' : 'Warning' }}
            </div>
        </div>

        <!-- pH -->
        <div class="card-gradient rounded-lg shadow-lg p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-vial text-green-500 text-xl mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">pH</h3>
            </div>
            <div class="relative w-48 h-48 mx-auto mb-4">
                <canvas id="phGauge" width="192" height="192"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div id="phValue" class="text-3xl font-bold text-gray-900">{{ $latestData ? number_format($latestData->ph, 1) : '0.0' }}</div>
                        <div class="text-sm text-gray-600">pH</div>
                    </div>
                </div>
            </div>
            <div id="phStatus" class="px-4 py-2 rounded-full text-sm font-medium {{ $latestData && $latestData->isPhNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $latestData && $latestData->isPhNormal() ? 'Normal' : 'Warning' }}
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Quick Actions</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button onclick="navigateTo('dashboard')" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg transition-all transform hover:scale-105 shadow-lg">
            <div class="flex flex-col items-center">
                <i class="fas fa-th-large text-2xl mb-2"></i>
                <span class="font-medium">Dashboard</span>
            </div>
        </button>
        
        <button onclick="navigateTo('history')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-4 rounded-lg transition-all transform hover:scale-105 shadow-lg">
            <div class="flex flex-col items-center">
                <i class="fas fa-history text-2xl mb-2"></i>
                <span class="font-medium">History</span>
            </div>
        </button>
        
        <button onclick="navigateTo('users')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-4 rounded-lg transition-all transform hover:scale-105 shadow-lg">
            <div class="flex flex-col items-center">
                <i class="fas fa-users text-2xl mb-2"></i>
                <span class="font-medium">Users</span>
            </div>
        </button>
        
        <button onclick="navigateTo('reports')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-4 rounded-lg transition-all transform hover:scale-105 shadow-lg">
            <div class="flex flex-col items-center">
                <i class="fas fa-chart-bar text-2xl mb-2"></i>
                <span class="font-medium">Reports</span>
            </div>
        </button>
    </div>
</div>



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gauge charts
    initializeGaugeCharts();
    
    // Update data every 5 seconds
    setInterval(updateGaugeData, 5000);
});

function initializeGaugeCharts() {
    // Get initial values from blade template
    const initialTemp = parseFloat(document.getElementById('tempValue').textContent) || 27.0;
    const initialOxygen = parseFloat(document.getElementById('oxygenValue').textContent) || 6.5;
    const initialPh = parseFloat(document.getElementById('phValue').textContent) || 7.0;
    
    // Temperature Gauge
    const tempCtx = document.getElementById('temperatureGauge').getContext('2d');
    window.temperatureChart = new Chart(tempCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [initialTemp, 60 - initialTemp], // Current value and remaining to max (60)
                backgroundColor: [
                    createGradient(tempCtx, ['#ef4444', '#f97316', '#eab308', '#22c55e']),
                    '#e5e7eb'
                ],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

    // Oxygen Gauge
    const oxygenCtx = document.getElementById('oxygenGauge').getContext('2d');
    window.oxygenChart = new Chart(oxygenCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [initialOxygen, 10 - initialOxygen], // Current value and remaining to max (10)
                backgroundColor: [
                    createGradient(oxygenCtx, ['#ef4444', '#f97316', '#3b82f6', '#06b6d4']),
                    '#e5e7eb'
                ],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

    // pH Gauge
    const phCtx = document.getElementById('phGauge').getContext('2d');
    window.phChart = new Chart(phCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [initialPh, 14 - initialPh], // Current value and remaining to max (14)
                backgroundColor: [
                    createGradient(phCtx, ['#ef4444', '#22c55e', '#3b82f6']),
                    '#e5e7eb'
                ],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}

function createGradient(ctx, colors) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    colors.forEach((color, index) => {
        gradient.addColorStop(index / (colors.length - 1), color);
    });
    return gradient;
}

function updateGaugeData() {
    // Fetch real sensor data from API
    fetch('{{ route("api.sensor-data") }}?hours=1')
        .then(response => response.json())
        .then(result => {
            if (result.success && result.latest) {
                const newTemp = parseFloat(result.latest.temperature).toFixed(1);
                const newOxygen = parseFloat(result.latest.oxygen).toFixed(1);
                const newPh = parseFloat(result.latest.ph).toFixed(1);

                // Update Temperature
                document.getElementById('tempValue').textContent = newTemp;
                updateStatus('temp', newTemp, 24, 30);
                window.temperatureChart.data.datasets[0].data = [parseFloat(newTemp), 60 - parseFloat(newTemp)];
                window.temperatureChart.update('none');

                // Update Oxygen
                document.getElementById('oxygenValue').textContent = newOxygen;
                updateStatus('oxygen', newOxygen, 5, 10);
                window.oxygenChart.data.datasets[0].data = [parseFloat(newOxygen), 10 - parseFloat(newOxygen)];
                window.oxygenChart.update('none');

                // Update pH
                document.getElementById('phValue').textContent = newPh;
                updateStatus('ph', newPh, 6.5, 8.5);
                window.phChart.data.datasets[0].data = [parseFloat(newPh), 14 - parseFloat(newPh)];
                window.phChart.update('none');
            }
        })
        .catch(error => {
            console.error('Error fetching sensor data:', error);
        });
}

function updateStatus(type, value, min, max) {
    const statusElement = document.getElementById(type + 'Status');
    value = parseFloat(value);
    
    if (value >= min && value <= max) {
        if (type === 'temp') {
            statusElement.textContent = 'Normal';
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800';
        } else if (type === 'oxygen') {
            statusElement.textContent = 'Baik';
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800';
        } else if (type === 'ph') {
            statusElement.textContent = 'Normal';
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800';
        }
    } else {
        statusElement.textContent = 'Warning';
        statusElement.className = 'px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800';
    }
}

// Auto-refresh gauge data every 30 seconds (same as user dashboard)
setInterval(updateGaugeData, 30000);

// Quick Actions functionality
function navigateTo(page) {
    switch(page) {
        case 'dashboard':
            window.location.href = '{{ route("admin.dashboard") }}';
            break;
        case 'history':
            window.location.href = '{{ route("admin.history") }}';
            break;
        case 'users':
            window.location.href = '{{ route("admin.users") }}';
            break;
        case 'reports':
            window.location.href = '{{ route("admin.reports") }}';
            break;
    }
}

console.log('Dashboard with gauge charts loaded');
</script>
@endpush
@endsection
